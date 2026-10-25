<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\TenantProfileRequest;
use App\Models\Client;
use App\Models\TenantProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantProfileController extends Controller
{
    /**
     * Menampilkan form edit profil & branding toko.
     * Query selalu scoped ke client_id milik admin yang sedang login.
     */
    public function edit(Request $request): View
    {
        $client = $this->resolveCurrentClient();

        // Query TenantProfile selalu scoped ke client_id milik admin yang login
        $profile = TenantProfile::firstOrCreate(
            ['client_id' => $client->id],
            [
                'name'        => $client->business_name ?? 'RentalBase Store',
                'subdomain'   => $client->subdomain,
                'description' => $client->description,
                'theme_color' => $client->theme_color ?? '#F97316',
            ]
        );

        $subscription = $client->activeSubscription;
        $plan = $client->plan ?? 'starter';
        $allowsCustomTheme = config("packages.{$plan}.features.custom_theme", false);

        $viewName = view()->exists('admin.profile.edit')
            ? 'admin.profile.edit'
            : (view()->exists('admin.theme.index') ? 'admin.theme.index' : 'admin.profile.edit');

        return view($viewName, compact('profile', 'client', 'subscription', 'allowsCustomTheme'));
    }

    /**
     * Memperbarui profil toko, upload logo, dan warna tema.
     * Query selalu scoped ke client_id milik admin yang sedang login.
     */
    public function update(TenantProfileRequest $request): RedirectResponse
    {
        $client = $this->resolveCurrentClient();

        // Pastikan hanya profile milik client_id ini yang dapat diakses/diupdate
        $profile = TenantProfile::where('client_id', $client->id)->first();

        if (! $profile) {
            $profile = TenantProfile::create([
                'client_id' => $client->id,
                'name'      => $client->business_name ?? 'RentalBase Store',
                'subdomain' => $client->subdomain,
            ]);
        }

        $validated = $request->validated();

        // Proteksi keamanan ganda: tolak/hapus theme_color jika paket tenant adalah Starter
        $plan = $client->plan ?? 'starter';
        $allowsCustomTheme = config("packages.{$plan}.features.custom_theme", false);
        if (! $allowsCustomTheme || $request->isStarterPlan()) {
            unset($validated['theme_color']);
        }

        // Handle upload file logo ke storage (disk 'supabase' / 's3')
        if ($request->hasFile('logo')) {
            $diskName = $this->resolveStorageDisk();
            $disk = Storage::disk($diskName);

            // Hapus logo lama dari storage jika sebelumnya sudah ada
            if (! empty($profile->logo_path)) {
                $this->deleteOldLogo($profile->logo_path);
            }

            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $filename = 'tenant-' . $client->id . '-' . time() . '.' . $extension;

            // Simpan file ke direktori tenant-logos pada disk storage
            $storedPath = $disk->putFileAs('tenant-logos', $file, $filename, 'public');

            $validated['logo_path'] = $storedPath;
        }

        // Hapus input temporary 'logo' dari data update model
        unset($validated['logo']);

        // Update data TenantProfile yang ter-scoped
        $profile->update($validated);

        // Sinkronisasi perubahan penting ke tabel clients untuk kompatibilitas modul legacy
        $this->syncClientAttributes($client, $profile);

        return redirect()
            ->back()
            ->with('success', 'Profil dan branding toko berhasil diperbarui.');
    }

    /**
     * Mendapatkan Client (Tenant) dari admin yang sedang login.
     * Mengamankan seluruh query agar selalu scoped ke tenant yang tepat.
     */
    protected function resolveCurrentClient(): Client
    {
        $user = Auth::user();

        $client = $user?->client ?? $user?->tenant;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun Anda tidak terhubung ke tenant/toko rental manapun.');
        }

        return $client;
    }

    /**
     * Menentukan disk storage yang tersedia (prioritas: supabase -> s3 -> default).
     */
    protected function resolveStorageDisk(): string
    {
        if (config('filesystems.disks.supabase')) {
            return 'supabase';
        }

        if (config('filesystems.disks.s3')) {
            return 's3';
        }

        return config('filesystems.default', 'public');
    }

    /**
     * Menghapus file logo lama dari storage dengan aman.
     */
    protected function deleteOldLogo(?string $path): void
    {
        if (empty($path)) {
            return;
        }

        $disksToCheck = array_unique([
            $this->resolveStorageDisk(),
            'supabase',
            's3',
            'public',
            config('filesystems.default', 'public'),
        ]);

        foreach ($disksToCheck as $diskName) {
            if (config("filesystems.disks.{$diskName}")) {
                try {
                    if (Storage::disk($diskName)->exists($path)) {
                        Storage::disk($diskName)->delete($path);
                        break;
                    }
                } catch (\Throwable $e) {
                    // Abaikan exception connection storage agar tidak menghambat flow update
                }
            }
        }
    }

    /**
     * Sinkronisasi data profile ke record clients.
     */
    protected function syncClientAttributes(Client $client, TenantProfile $profile): void
    {
        $updates = [];

        if ($profile->name && $client->business_name !== $profile->name) {
            $updates['business_name'] = $profile->name;
        }

        if ($profile->description && $client->description !== $profile->description) {
            $updates['description'] = $profile->description;
        }

        if ($profile->logo_path && $client->logo !== $profile->logo_path) {
            $updates['logo'] = $profile->logo_path;
        }

        if ($profile->theme_color && $client->theme_color !== $profile->theme_color) {
            $updates['theme_color'] = $profile->theme_color;
        }

        if (! empty($updates)) {
            $client->update($updates);
        }
    }
}
