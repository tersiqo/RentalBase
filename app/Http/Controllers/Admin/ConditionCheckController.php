<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConditionCheckRequest;
use App\Models\ConditionCheck;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConditionCheckController extends Controller
{
    /**
     * Menampilkan halaman daftar rekaman kondisi + form tambah baru.
     */
    public function index(Request $request)
    {
        // === POLA BAKU PROJECT: Ambil user dan client ===
        $user = Auth::user();
        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun tidak terhubung ke toko.');
        }

        // Ambil parameter konteks navigasi jika diarahkan dari tabel pengembalian atau order show
        $selectedOrderId = $request->query('order_id');
        $selectedUnitId  = $request->query('unit_id');
        $selectedType    = $request->query('type');
        $returnTo        = $request->query('return_to');

        // Ambil daftar rekaman kondisi milik toko ini
        // Eager load: order & customernya, equipmentUnit & produknya, dan checker (petugas)
        $conditionChecks = ConditionCheck::with([
            'equipmentUnit.product',
            'equipmentUnit.orderItemUnits.orderItem.order.user',
            'checker',
        ])
        ->whereHas('equipmentUnit', function ($query) use ($client) {
            $query->where('client_id', $client->id);
        })
        ->orderBy('created_at', 'desc')
        ->get();

        // Ambil daftar order untuk dropdown — hanya yang relevan untuk pemeriksaan fisik
        $orders = Order::with(['orderItems.product', 'user', 'orderItems.orderItemUnits.equipmentUnit.product'])
            ->where('client_id', $client->id)
            ->whereIn('status', ['siap_kirim', 'dikonfirmasi', 'dikirim', 'sedang_disewa', 'dikembalikan', 'menunggu_pembayaran_denda', 'selesai'])
            ->orderBy('created_at', 'desc')
            ->get();

        $selectedOrder = null;
        $defaultCheckType = 'setelah_kembali';

        if ($selectedOrderId) {
            $selectedOrder = Order::with(['orderItems.product', 'user', 'orderItems.orderItemUnits.equipmentUnit.product'])
                ->where('client_id', $client->id)
                ->find($selectedOrderId);

            if ($selectedOrder) {
                // Pastikan order terpilih selalu ada di daftar dropdown
                if (! $orders->contains('id', $selectedOrder->id)) {
                    $orders->prepend($selectedOrder);
                }

                // Tentukan fase pemeriksaan otomatis
                if (in_array($selectedType, ['sebelum_serah', 'setelah_kembali'])) {
                    $defaultCheckType = $selectedType;
                } elseif (in_array($selectedOrder->status, ['siap_kirim', 'dikonfirmasi'])) {
                    $defaultCheckType = 'sebelum_serah';
                } else {
                    $defaultCheckType = 'setelah_kembali';
                }
            }
        }

        return view('admin.condition-checks.index', compact(
            'conditionChecks',
            'orders',
            'user',
            'selectedOrderId',
            'selectedUnitId',
            'selectedOrder',
            'defaultCheckType',
            'returnTo'
        ));
    }

    /**
     * Mengambil daftar unit fisik untuk order tertentu (dipanggil via fetch/AJAX).
     * URL: GET /admin/condition-checks/units?order_id=123
     */
    public function getUnits(Request $request)
    {
        // Ambil user dan client seperti biasa
        $user = Auth::user();
        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        if (! $client) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $orderId = $request->integer('order_id');

        // Validasi: order harus milik client ini
        $order = Order::where('id', $orderId)
            ->where('client_id', $client->id)
            ->first();

        if (! $order) {
            return response()->json([]);
        }

        // Ambil unit fisik dari order ini
        $order->load('orderItems.orderItemUnits.equipmentUnit.product');

        $units = $order->orderItems->flatMap(function ($item) {
            return $item->orderItemUnits->map(function ($oiu) use ($item) {
                $unit = $oiu->equipmentUnit;
                return [
                    'id'           => $unit->id,
                    'label'        => '[' . $unit->unit_code . '] ' . ($item->product->name ?? '-'),
                    'unit_code'    => $unit->unit_code,
                    'product_name' => $item->product->name ?? '-',
                    'condition'    => $unit->condition,
                ];
            });
        })->values();

        return response()->json($units);
    }

    /**
     * Menyimpan rekaman kondisi baru.
     */
    public function store(ConditionCheckRequest $request)
    {
        // === POLA BAKU PROJECT: Ambil user dan client ===
        $user = Auth::user();
        if (! $user) {
            $user = User::whereNotNull('client_id')->first();
        }
        $client = $user?->client;

        if (! $client) {
            abort(403, 'Akses ditolak: Akun tidak terhubung ke toko.');
        }

        // Validasi: pastikan unit yang dipilih memang milik client ini
        $equipmentUnit = EquipmentUnit::where('id', $request->equipment_unit_id)
            ->where('client_id', $client->id)
            ->first();

        if (! $equipmentUnit) {
            return back()->with('error', 'Unit fisik tidak valid atau bukan milik toko Anda.');
        }

        // Simpan foto (mendukung single file atau multiple files)
        $disk = \App\Services\StorageHelper::disk();
        $imagePath = null;
        if ($request->hasFile('images')) {
            $paths = [];
            foreach ($request->file('images') as $file) {
                if ($file && $file->isValid()) {
                    $paths[] = $file->store('condition-checks', $disk);
                }
            }
            if (! empty($paths)) {
                $imagePath = count($paths) === 1 ? $paths[0] : json_encode($paths);
            }
        } elseif ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('condition-checks', $disk);
        }

        // Simpan rekaman kondisi ke database
        ConditionCheck::create([
            'equipment_unit_id' => $request->equipment_unit_id,
            'check_type'        => $request->check_type,
            'condition'         => $request->condition,
            'notes'             => $request->notes,
            'image'             => $imagePath,
            'checked_by'        => $user->id,
        ]);

        // Sinkronkan status unit fisik bila hasil inspeksi kembali mencatat kerusakan
        if ($request->check_type === 'setelah_kembali' && in_array($request->condition, ['rusak_ringan', 'rusak_berat'], true)) {
            $equipmentUnit->update([
                'condition' => $request->condition,
                'status'    => $request->condition === 'rusak_berat' ? 'rusak' : 'maintenance',
            ]);
        }

        // Catat jejak audit aktivitas inspeksi
        \App\Models\ActivityLog::create([
            'client_id'   => $client->id,
            'user_id'     => $user->id,
            'action'      => 'condition_check_recorded',
            'module'      => 'inspections',
            'description' => sprintf(
                'Inspeksi fisik unit [%s] (%s) fase %s dicatat dengan kondisi: %s oleh %s.',
                $equipmentUnit->unit_code,
                $equipmentUnit->product?->name ?? 'Produk',
                $request->check_type === 'sebelum_serah' ? 'Sebelum Kirim' : 'Saat Kembali',
                ucfirst(str_replace('_', ' ', $request->condition)),
                $user->name ?? 'Admin Toko'
            ),
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        $returnTo = $request->input('return_to');
        $orderId = $request->input('order_id');
        $isReturnDamage = $request->check_type === 'setelah_kembali' && in_array($request->condition, ['rusak_ringan', 'rusak_berat'], true);
        $damageSuffix = $isReturnDamage
            ? sprintf(
                ' Status unit otomatis diubah menjadi %s. Buat laporan di menu Kasus Kerusakan jika perlu menagih kompensasi ke penyewa.',
                $request->condition === 'rusak_berat' ? 'Rusak' : 'Maintenance'
            )
            : '';

        if ($returnTo === 'returns') {
            return redirect()->route('admin.returns.index')
                ->with('success', sprintf(
                    'Pemeriksaan fisik unit [%s] berhasil dicatat (%s).%s',
                    $equipmentUnit->unit_code,
                    ucfirst(str_replace('_', ' ', $request->condition)),
                    $isReturnDamage ? $damageSuffix : ' Anda dapat melanjutkan proses pengembalian pesanan.'
                ));
        }

        if ($returnTo === 'order' && $orderId) {
            return redirect()->route('admin.orders.show', $orderId)
                ->with('success', sprintf(
                    'Pemeriksaan fisik unit [%s] berhasil dicatat (%s).%s',
                    $equipmentUnit->unit_code,
                    ucfirst(str_replace('_', ' ', $request->condition)),
                    $damageSuffix
                ));
        }

        return redirect()->route('admin.condition-checks.index')
            ->with('success', 'Rekaman kondisi fisik berhasil disimpan.' . $damageSuffix);
    }
}