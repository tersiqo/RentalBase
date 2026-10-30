<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Helper to resolve active client for layout branding.
     */
    protected function resolveClient(?string $subdomain, Request $request): Client
    {
        $client = null;
        if ($subdomain) {
            $client = Client::where('subdomain', $subdomain)->first();
        }
        if (! $client && $request->filled('client')) {
            $client = Client::where('subdomain', $request->query('client'))->first();
        }
        if (! $client && session()->has('active_client_id')) {
            $client = Client::find(session('active_client_id'));
        }
        if (! $client && session()->has('active_client_subdomain')) {
            $client = Client::where('subdomain', session('active_client_subdomain'))->first();
        }
        if (! $client) {
            $client = Client::where('status', 'aktif')->first() ?? Client::first();
        }

        if (! $client) {
            abort(404, 'Tenant tidak ditemukan.');
        }

        return $client;
    }

    /**
     * Halaman profil customer.
     */
    public function show(Request $request, ?string $subdomain = null)
    {
        $user = Auth::user();
        $client = $this->resolveClient($subdomain, $request);

        // Riwayat dan statistik pesanan customer
        $totalOrders = Order::where('user_id', $user->id)->count();
        $activeOrders = Order::where('user_id', $user->id)
            ->whereIn('status', ['menunggu_konfirmasi', 'menunggu_pembayaran', 'menunggu_verifikasi', 'siap_kirim', 'dikirim', 'sedang_disewa'])
            ->count();
        $completedOrders = Order::where('user_id', $user->id)
            ->where('status', 'selesai')
            ->count();

        $recentOrders = Order::where('user_id', $user->id)
            ->with(['orderItems.product', 'client'])
            ->latest()
            ->take(3)
            ->get();

        return view('customer.profile', compact(
            'user',
            'client',
            'totalOrders',
            'activeOrders',
            'completedOrders',
            'recentOrders'
        ));
    }

    /**
     * Redirect dari route global /profile ke route scoped /client/{subdomain}/profile.
     */
    public function showGlobal(Request $request)
    {
        $client = $this->resolveClient(null, $request);

        return redirect()->route('customer.profile', ['subdomain' => $client->subdomain]);
    }

    /**
     * Update data profil customer (nama, nomor telepon, alamat, avatar).
     */
    public function updateProfile(Request $request, ?string $subdomain = null)
    {
        $user = Auth::user();
        $client = $this->resolveClient($subdomain, $request);

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'avatar'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.max'      => 'Nama lengkap maksimal 255 karakter.',
            'phone.max'     => 'Nomor WhatsApp / telepon maksimal 20 digit.',
            'avatar.image'  => 'File foto profil harus berupa gambar.',
            'avatar.mimes'  => 'Format foto profil yang didukung: JPG, PNG, WEBP.',
            'avatar.max'    => 'Ukuran foto profil maksimal 2MB.',
        ]);

        $user->name = $validated['name'];
        $user->phone = $validated['phone'] ?? null;
        $user->address = $validated['address'] ?? null;

        if ($request->hasFile('avatar')) {
            $disk = config('filesystems.default', 'public');
            if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
                Storage::disk($disk)->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', $disk);
            $user->avatar = $path;
        }

        $user->save();

        return redirect()->route('customer.profile', ['subdomain' => $client->subdomain])
            ->with('success', 'Data profil Anda berhasil diperbarui!');
    }

    /**
     * Update password customer.
     */
    public function updatePassword(Request $request, ?string $subdomain = null)
    {
        $user = Auth::user();
        $client = $this->resolveClient($subdomain, $request);

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required'         => 'Password saat ini wajib diisi.',
            'current_password.current_password' => 'Password saat ini yang kamu masukkan salah.',
            'password.required'                 => 'Password baru wajib diisi.',
            'password.min'                      => 'Password baru minimal 8 karakter.',
            'password.confirmed'                => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('customer.profile', ['subdomain' => $client->subdomain])
            ->with('password_success', 'Password akun Anda berhasil diperbarui!');
    }
}
