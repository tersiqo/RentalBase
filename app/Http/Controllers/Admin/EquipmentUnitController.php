<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\EquipmentUnit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EquipmentUnitController extends Controller
{
    /**
     * Menampilkan daftar unit fisik peralatan rental toko.
     */
    public function index(Request $request)
    {
        $client = $this->resolveClient();
        $subscription = $client->activeSubscription()->first();

        $search = trim((string) $request->query('search', ''));
        $productId = $request->query('product_id');
        $status = $request->query('status');
        $condition = $request->query('condition');

        // Query dasar unit fisik milik toko aktif
        $baseQuery = EquipmentUnit::where('client_id', $client->id);

        // Hitung metrik status unit untuk summary cards
        $metrics = [
            'total'          => (clone $baseQuery)->count(),
            'tersedia'       => (clone $baseQuery)->whereIn('status', ['tersedia', 'available'])->count(),
            'disewa'         => (clone $baseQuery)->whereIn('status', ['disewa', 'rented'])->count(),
            'maintenance'    => (clone $baseQuery)->where('status', 'maintenance')->count(),
            'tidak_tersedia' => (clone $baseQuery)->whereIn('status', ['tidak_tersedia', 'rusak', 'lost', 'inactive'])->count(),
        ];

        // Kuota unit dari paket langganan
        $limit = $this->planUnitLimit($subscription?->plan_name);
        $used = $metrics['total'];

        // Query tabel dengan filter
        $query = (clone $baseQuery)->with([
            'product:id,client_id,category_id,name,rental_price_per_day,main_image,status',
            'orderItemUnits' => function ($q) {
                $q->latest()->with('orderItem.order:id,order_number,status,rental_start_date,rental_end_date');
            },
        ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('unit_code', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($productId) {
            $query->where('product_id', $productId);
        }

        if ($status) {
            if ($status === 'tersedia') {
                $query->whereIn('status', ['tersedia', 'available']);
            } elseif ($status === 'disewa') {
                $query->whereIn('status', ['disewa', 'rented']);
            } else {
                $query->where('status', $status);
            }
        }

        if ($condition) {
            $query->where('condition', $condition);
        }

        $units = $query->orderBy('created_at', 'desc')->get();

        // Daftar produk milik toko untuk dropdown filter & modal
        $products = Product::where('client_id', $client->id)
            ->orderBy('name')
            ->get(['id', 'name', 'rental_price_per_day', 'main_image', 'status']);

        return view('admin.units.index', compact(
            'client',
            'subscription',
            'units',
            'products',
            'limit',
            'used',
            'metrics',
            'search',
            'productId',
            'status',
            'condition'
        ));
    }

    /**
     * Menyimpan unit fisik baru ke inventaris toko.
     */
    public function store(Request $request)
    {
        $client = $this->resolveClient();
        $subscription = $client->activeSubscription()->first();

        // 1. Validasi batas kuota unit paket langganan
        $limit = $this->planUnitLimit($subscription?->plan_name);
        $used = EquipmentUnit::where('client_id', $client->id)->count();

        if ($limit !== null && $used >= $limit) {
            throw ValidationException::withMessages([
                'unit_code' => "Batas kuota unit fisik paket tercapai ({$limit} unit). Hapus unit yang tidak aktif atau upgrade paket langganan Anda.",
            ]);
        }

        // 2. Validasi input
        $validated = $this->validateUnit($request, $client->id);

        // Pastikan produk yang dipilih benar milik toko ini
        $product = Product::where('id', $validated['product_id'])
            ->where('client_id', $client->id)
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => 'Produk induk yang dipilih tidak valid atau bukan milik toko Anda.',
            ]);
        }

        // 3. Simpan unit fisik
        $unit = EquipmentUnit::create([
            'client_id'     => $client->id,
            'product_id'    => $product->id,
            'unit_code'     => strtoupper(trim($validated['unit_code'])),
            'serial_number' => ! empty($validated['serial_number']) ? trim($validated['serial_number']) : null,
            'condition'     => $validated['condition'],
            'status'        => $validated['status'],
        ]);

        return redirect()
            ->route('admin.units.index')
            ->with('success', "Unit fisik \"{$unit->unit_code}\" untuk produk \"{$product->name}\" berhasil ditambahkan.");
    }

    /**
     * Memperbarui informasi unit fisik.
     */
    public function update(Request $request, EquipmentUnit $unit)
    {
        $client = $this->resolveClient();
        $this->authorizeUnit($client, $unit);

        $validated = $this->validateUnit($request, $client->id, $unit->id);

        $product = Product::where('id', $validated['product_id'])
            ->where('client_id', $client->id)
            ->first();

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => 'Produk induk yang dipilih tidak valid atau bukan milik toko Anda.',
            ]);
        }

        // Proteksi integritas transaksi sewa:
        // Jika unit sedang disewa, tidak boleh sembarangan diubah statusnya menjadi tersedia secara tidak sah
        $isCurrentlyRented = in_array(strtolower((string) $unit->status), ['disewa', 'rented']) || $unit->isActivelyRented();

        if ($isCurrentlyRented && in_array($validated['status'], ['tersedia', 'available']) && ! in_array(strtolower((string) $unit->status), ['tersedia', 'available'])) {
            throw ValidationException::withMessages([
                'status' => "Unit fisik \"{$unit->unit_code}\" saat ini masih terikat transaksi sewa aktif oleh customer. Status tidak dapat diubah menjadi Tersedia sebelum pesanan selesai atau dibatalkan.",
            ]);
        }

        $unit->update([
            'product_id'    => $product->id,
            'unit_code'     => strtoupper(trim($validated['unit_code'])),
            'serial_number' => ! empty($validated['serial_number']) ? trim($validated['serial_number']) : null,
            'condition'     => $validated['condition'],
            'status'        => $validated['status'],
        ]);

        return redirect()
            ->route('admin.units.index')
            ->with('success', "Data unit fisik \"{$unit->unit_code}\" berhasil diperbarui.");
    }

    /**
     * Menghapus unit fisik dari sistem.
     */
    public function destroy(EquipmentUnit $unit)
    {
        $client = $this->resolveClient();
        $this->authorizeUnit($client, $unit);

        // 1. Proteksi jika status unit masih 'disewa'
        if (in_array(strtolower((string) $unit->status), ['disewa', 'rented'])) {
            return redirect()
                ->route('admin.units.index')
                ->with('error', "Unit \"{$unit->unit_code}\" tidak dapat dihapus karena saat ini sedang disewa oleh customer.");
        }

        // 2. Proteksi jika unit sedang dialokasikan pada pesanan aktif
        if ($unit->isActivelyRented()) {
            return redirect()
                ->route('admin.units.index')
                ->with('error', "Unit \"{$unit->unit_code}\" tidak dapat dihapus karena terikat pada pesanan sewa yang sedang aktif.");
        }

        $code = $unit->unit_code;
        $unit->delete();

        return redirect()
            ->route('admin.units.index')
            ->with('success', "Unit fisik \"{$code}\" telah berhasil dihapus dari inventaris.");
    }

    /**
     * Validasi data input unit fisik.
     */
    private function validateUnit(Request $request, int $clientId, ?int $ignoreId = null): array
    {
        return $request->validate([
            'product_id'    => ['required', 'integer'],
            'unit_code'     => [
                'required',
                'string',
                'max:50',
                Rule::unique('equipment_units', 'unit_code')
                    ->where('client_id', $clientId)
                    ->ignore($ignoreId),
            ],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'condition'     => ['required', 'string', Rule::in(['baik', 'rusak_ringan', 'rusak_berat'])],
            'status'        => ['required', 'string', Rule::in(['tersedia', 'disewa', 'maintenance', 'tidak_tersedia'])],
        ], [
            'product_id.required'    => 'Silakan pilih produk induk untuk unit fisik ini.',
            'unit_code.required'     => 'Kode unit / kode aset fisik wajib diisi.',
            'unit_code.max'          => 'Kode unit fisik maksimal 50 karakter.',
            'unit_code.unique'       => 'Kode unit ini sudah digunakan oleh unit lain di toko Anda.',
            'serial_number.max'      => 'Nomor seri pabrik maksimal 100 karakter.',
            'condition.required'     => 'Kondisi fisik unit wajib dipilih.',
            'condition.in'           => 'Kondisi fisik harus salah satu dari: Baik, Rusak Ringan, atau Rusak Berat.',
            'status.required'        => 'Status operasional unit wajib dipilih.',
            'status.in'              => 'Status unit harus salah satu dari: Tersedia, Disewa, Maintenance, atau Tidak Tersedia.',
        ]);
    }

    /**
     * Hitung limit kuota unit per paket langganan tenant.
     */
    private function planUnitLimit(?string $planName): ?int
    {
        $plan = mb_strtolower((string) $planName);

        if ($plan === '') {
            return config('packages.starter.limits.units', 50);
        }

        if (str_contains($plan, 'starter')) {
            return config('packages.starter.limits.units', 50);
        }

        if (str_contains($plan, 'business')) {
            return config('packages.business.limits.units', 100);
        }

        if (str_contains($plan, 'professional') || str_contains($plan, 'pro') || str_contains($plan, 'enterprise')) {
            return config('packages.professional.limits.units', null);
        }

        return config('packages.starter.limits.units', 50);
    }

    /**
     * Ambil instance Client tenant dari pengguna yang login.
     */
    private function resolveClient(): Client
    {
        $user = Auth::user();

        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }

        $client = $user?->client;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun tidak terhubung ke toko rental manapun.');
        }

        return $client;
    }

    /**
     * Otorisasi kepemilikan unit fisik pada tenant aktif.
     */
    private function authorizeUnit(Client $client, EquipmentUnit $unit): void
    {
        if ($unit->client_id !== $client->id) {
            abort(403, 'Akses ditolak: Anda tidak memiliki hak akses ke data unit fisik toko ini.');
        }
    }
}
