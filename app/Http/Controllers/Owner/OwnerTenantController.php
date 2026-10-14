<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OwnerTenantController extends Controller
{
    /**
     * Menampilkan daftar client / tenant rental.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'aktif');
        $plan = $request->query('plan');
        $search = $request->query('search');

        Subscription::where('status', 'aktif')
            ->where('end_date', '<', Carbon::now())
            ->update(['status' => 'kadaluarsa']);

        $baseQuery = Client::where('status', '!=', 'menunggu_verifikasi');

        $query = (clone $baseQuery)->with([
            'adminUser',
            'latestSubscription',
            'activeSubscription',
        ])->withCount(['products', 'equipmentUnits']);

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($plan) {
            $query->whereHas('latestSubscription', function ($q) use ($plan) {
                $q->where('plan_name', $plan);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('adminUser', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $tenants = $query->orderByDesc('created_at')->paginate(10)->withQueryString();

        $now = Carbon::now();
        $counts = [
            'total'         => (clone $baseQuery)->count(),
            'aktif'         => (clone $baseQuery)->where('status', 'aktif')->count(),
            'suspended'     => (clone $baseQuery)->where('status', 'suspended')->count(),
            'nonaktif'      => (clone $baseQuery)->where('status', 'nonaktif')->count(),
            'expiring_soon' => Subscription::where('status', 'aktif')
                                  ->whereHas('client', function ($q) {
                                      $q->where('status', '!=', 'menunggu_verifikasi');
                                  })
                                  ->where('end_date', '<=', $now->copy()->addDays(7))
                                  ->where('end_date', '>=', $now)
                                  ->count(),
        ];

        $pendaftaranMenunggu = ClientRegistration::where('status', 'menunggu_verifikasi')->count();
        $packages = config('packages', []);

        return view('owner.tenants.index', compact(
            'tenants',
            'status',
            'plan',
            'search',
            'counts',
            'pendaftaranMenunggu',
            'packages'
        ));
    }

    /**
     * Memperbarui detail profil usaha & identitas client.
     */
    public function update(Request $request, Client $client)
    {
        $admin = $client->adminUser;

        $request->validate([
            'business_name' => 'required|string|max:255',
            'subdomain'     => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('clients', 'subdomain')->ignore($client->id),
            ],
            'description'   => 'nullable|string',
            'theme_color'   => 'nullable|string|regex:/^#[0-9a-fA-F]{6}$/i',
            'logo'          => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'admin_name'    => 'required|string|max:255',
            'admin_email'   => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($admin?->id),
            ],
            'admin_phone'   => 'required|string|max:25',
            'admin_address' => 'nullable|string',
        ], [
            'business_name.required' => 'Nama usaha wajib diisi.',
            'subdomain.required'     => 'Subdomain wajib diisi.',
            'subdomain.unique'       => 'Subdomain sudah digunakan oleh tenant lain.',
            'subdomain.alpha_dash'   => 'Subdomain hanya boleh berupa huruf, angka, dan strip (-).',
            'admin_name.required'    => 'Nama PIC / Pengelola wajib diisi.',
            'admin_email.required'   => 'Email PIC wajib diisi.',
            'admin_email.unique'     => 'Email ini sudah terdaftar pada user lain.',
            'admin_phone.required'   => 'Nomor telepon/WhatsApp wajib diisi.',
            'logo.max'               => 'Ukuran logo maksimal 2MB.',
        ]);

        DB::beginTransaction();

        try {
            $clientData = [
                'business_name' => $request->business_name,
                'subdomain'     => strtolower($request->subdomain),
                'description'   => $request->description,
                'theme_color'   => $request->theme_color ?: '#F97316',
            ];

            if ($request->hasFile('logo')) {
                // Hapus logo lama jika ada
                if ($client->logo && Storage::disk('public')->exists($client->logo)) {
                    Storage::disk('public')->delete($client->logo);
                }
                $clientData['logo'] = $request->file('logo')->store('clients/logos', 'public');
            }

            $client->update($clientData);

            if ($admin) {
                $admin->update([
                    'name'    => $request->admin_name,
                    'email'   => $request->admin_email,
                    'phone'   => $request->admin_phone,
                    'address' => $request->admin_address,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', "Informasi tenant '{$client->business_name}' berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data tenant: ' . $e->getMessage());
        }
    }

    /**
     * Mengubah status akun tenant (aktif, suspended, nonaktif).
     */
    public function updateStatus(Request $request, Client $client)
    {
        $request->validate([
            'status' => 'required|string|in:aktif,suspended,nonaktif',
        ]);

        $newStatus = $request->status;

        DB::beginTransaction();

        try {
            $client->update([
                'status' => $newStatus,
            ]);

            // Sinkronisasi status user admin rental milik tenant ini
            User::where('client_id', $client->id)
                ->where('role', 'admin_rental')
                ->update([
                    'status' => $newStatus === 'nonaktif' ? 'suspended' : $newStatus,
                ]);

            DB::commit();

            $statusLabels = [
                'aktif'     => 'Diaktifkan',
                'suspended' => 'Ditangguhkan (Suspended)',
                'nonaktif'  => 'Dinonaktifkan',
            ];

            $label = $statusLabels[$newStatus] ?? $newStatus;

            return redirect()->back()->with('success', "Status akun tenant '{$client->business_name}' berhasil diubah menjadi: {$label}.");
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui status tenant.');
        }
    }

    /**
     * Mengelola paket dan masa berlaku subscription tenant.
     */
    public function updateSubscription(Request $request, Client $client)
    {
        $availablePlans = array_keys(config('packages', [
            'starter' => [], 'business' => [], 'professional' => []
        ]));

        $request->validate([
            'plan_name'     => ['required', 'string', Rule::in($availablePlans)],
            'end_date'      => 'required|date',
            'status'        => 'nullable|string|in:aktif,menunggu_verifikasi,kadaluarsa,nonaktif',
            'is_disabled'   => 'nullable|boolean',
            'billing_cycle' => 'nullable|string|in:monthly,yearly',
        ], [
            'plan_name.required' => 'Pilihan paket langganan wajib diisi.',
            'end_date.required'  => 'Tanggal berakhir langganan wajib diisi.',
            'end_date.date'      => 'Format tanggal tidak valid.',
        ]);

        DB::beginTransaction();

        try {
            $plan = $request->plan_name;
            $subscription = $client->latestSubscription;
            $endDate = Carbon::parse($request->end_date)->endOfDay();

            // Pemicu Status Otomatis:
            // 1. Nonaktif: Owner menonaktifkan subscription secara manual (alasan administratif/kebijakan) atau akun client nonaktif
            // 2. Kadaluwarsa: Tanggal saat ini melewati tanggal berakhir (End Date)
            // 3. Menunggu Verifikasi: Status akun tenant masih menunggu verifikasi (pendaftaran tenant belum diverifikasi)
            // 4. Aktif: Status akun tenant sudah aktif (pendaftaran tenant sudah diverifikasi) dan tanggal masih dalam masa berlangganan
            $isManualDisabled = $request->boolean('is_disabled') || $request->status === 'nonaktif';

            if ($isManualDisabled || $client->status === 'nonaktif') {
                $status = 'nonaktif';
            } elseif ($endDate->isPast()) {
                $status = 'kadaluarsa';
            } elseif ($client->status === 'menunggu_verifikasi') {
                $status = 'menunggu_verifikasi';
            } else {
                $status = 'aktif';
            }

            $subscriptionData = [
                'plan_name'     => $plan,
                'max_products'  => $this->getMaxProducts($plan),
                'max_users'     => $this->getMaxUsers($plan),
                'price'         => $this->getPrice($plan),
                'billing_cycle' => $request->billing_cycle ?: 'monthly',
                'end_date'      => $endDate,
                'status'        => $status,
            ];

            if ($status === 'aktif') {
                $subscriptionData['payment_status'] = 'diverifikasi';
            }

            if ($subscription) {
                $subscription->update($subscriptionData);
            } else {
                $subscriptionData['client_id'] = $client->id;
                $subscriptionData['start_date'] = Carbon::now();
                $subscriptionData['payment_status'] = $status === 'aktif' ? 'diverifikasi' : 'menunggu';
                Subscription::create($subscriptionData);
            }

            DB::commit();

            return redirect()->back()->with('success', "Subscription tenant '{$client->business_name}' berhasil diperbarui ke paket " . ucfirst($plan) . " (Status: " . ucfirst($status) . ").");
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return redirect()->back()->with('error', 'Gagal memperbarui data langganan tenant: ' . $e->getMessage());
        }
    }

    private function getMaxProducts(string $plan): ?int
    {
        return config("packages.{$plan}.limits.products", match ($plan) {
            'starter'      => 10,
            'business'     => 50,
            'professional' => null,
            default        => 10,
        });
    }

    private function getMaxUsers(string $plan): ?int
    {
        return config("packages.{$plan}.limits.admins", match ($plan) {
            'starter'      => 1,
            'business'     => 3,
            'professional' => 10,
            default        => 1,
        });
    }

    private function getPrice(string $plan): float
    {
        return (float) config("packages.{$plan}.price", match ($plan) {
            'starter'      => 0,
            'business'     => 99000,
            'professional' => 249000,
            default        => 0,
        });
    }
}

