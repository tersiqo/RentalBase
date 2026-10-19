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
    public function index()
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

        // Ambil daftar rekaman kondisi milik toko ini
        // Jalur: condition_check → equipment_unit → client
        $conditionChecks = ConditionCheck::with([
                'equipmentUnit.product',
                'checker',
            ])
            ->whereHas('equipmentUnit', function ($query) use ($client) {
                $query->where('client_id', $client->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Ambil daftar order untuk dropdown — hanya yang sudah konfirmasi atau sedang disewa
        $orders = Order::with(['orderItems.product', 'user'])
            ->where('client_id', $client->id)
            ->whereIn('status', ['siap_kirim', 'dikonfirmasi', 'dikirim', 'sedang_disewa'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.condition-checks.index', compact('conditionChecks', 'orders', 'user'));
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

        // Simpan foto jika ada
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('condition-checks', 'public');
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

        return redirect()->route('admin.condition-checks.index')
            ->with('success', 'Rekaman kondisi fisik berhasil disimpan.');
    }
}