<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\DamageCase;
use App\Models\DamageReport;
use App\Models\EquipmentUnit;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DamageCaseController extends Controller
{
    /**
     * Menampilkan daftar kasus kerusakan peralatan rental toko.
     */
    public function index(Request $request)
    {
        $client = $this->resolveClient();
        $user = Auth::user() ?? User::where('client_id', $client->id)->first();

        $search = trim((string) $request->query('search', ''));
        $selectedCaseStatus = $request->query('case_status') ?? $request->query('status');
        $selectedPaymentStatus = $request->query('payment_status');

        // Query dasar kasus kerusakan milik tenant aktif
        $baseQuery = DamageCase::whereHas('damageReport', function ($q) use ($client) {
            $q->whereHas('order', function ($oq) use ($client) {
                $oq->where('client_id', $client->id);
            });
        });

        // Hitung metrik status untuk kartu ringkasan
        $metrics = [
            'total' => (clone $baseQuery)->count(),
            'terbuka' => (clone $baseQuery)->whereIn('case_status', ['terbuka', 'menunggu_respons', 'menunggu_tanggapan'])->count(),
            'dalam_tinjauan' => (clone $baseQuery)->whereIn('case_status', ['dalam_tinjauan', 'negosiasi'])->count(),
            'menunggu_verifikasi' => (clone $baseQuery)->where(function ($q) {
                $q->where('payment_status', 'menunggu_verifikasi')
                  ->orWhere('case_status', 'kompensasi_dibayar');
            })->count(),
            'selesai' => (clone $baseQuery)->whereIn('case_status', ['selesai', 'lunas'])->count(),
        ];

        // Query tabel dengan eager loading
        $query = (clone $baseQuery)->with([
            'damageReport.order.user',
            'damageReport.equipmentUnit.product',
            'damageReport.reporter',
        ]);

        // Filter pencarian teks
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('damageReport.order', function ($oq) use ($search) {
                      $oq->where('order_number', 'like', "%{$search}%")
                         ->orWhereHas('user', function ($uq) use ($search) {
                             $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                         });
                  })
                  ->orWhereHas('damageReport.equipmentUnit', function ($uq) use ($search) {
                      $uq->where('unit_code', 'like', "%{$search}%")
                         ->orWhere('serial_number', 'like', "%{$search}%")
                         ->orWhereHas('product', function ($pq) use ($search) {
                             $pq->where('name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Filter status kasus
        if ($selectedCaseStatus) {
            if ($selectedCaseStatus === 'terbuka') {
                $query->whereIn('case_status', ['terbuka', 'menunggu_respons', 'menunggu_tanggapan']);
            } elseif ($selectedCaseStatus === 'dalam_tinjauan') {
                $query->whereIn('case_status', ['dalam_tinjauan', 'negosiasi']);
            } elseif ($selectedCaseStatus === 'kompensasi_dibayar') {
                $query->whereIn('case_status', ['kompensasi_dibayar', 'menunggu_verifikasi']);
            } elseif ($selectedCaseStatus === 'selesai') {
                $query->whereIn('case_status', ['selesai', 'lunas']);
            } else {
                $query->where('case_status', $selectedCaseStatus);
            }
        }

        // Filter status pembayaran
        if ($selectedPaymentStatus) {
            $query->where('payment_status', $selectedPaymentStatus);
        }

        $damageCases = $query->latest()->get();

        return view('admin.damage-cases.index', compact(
            'damageCases',
            'metrics',
            'client',
            'user',
            'search',
            'selectedCaseStatus',
            'selectedPaymentStatus'
        ));
    }

    /**
     * Menampilkan form buat tiket kasus kerusakan baru.
     */
    public function create(Request $request)
    {
        $client = $this->resolveClient();
        $user = Auth::user() ?? User::where('client_id', $client->id)->first();

        $selectedOrderId = $request->query('order_id');

        // Ambil daftar order milik toko
        $orders = Order::with(['user', 'orderItems.product', 'orderItems.orderItemUnits.equipmentUnit.product'])
            ->where('client_id', $client->id)
            ->orderByDesc('id')
            ->get();

        // Ambil seluruh unit toko untuk fallback jika order belum dialokasikan spesifik
        $allUnits = EquipmentUnit::with('product')
            ->where('client_id', $client->id)
            ->orderBy('unit_code')
            ->get();

        return view('admin.damage-cases.create', compact('client', 'user', 'orders', 'allUnits', 'selectedOrderId'));
    }

    /**
     * Endpoint API JSON untuk mengambil unit fisik dari pesanan yang dipilih.
     */
    public function getUnits(Request $request)
    {
        $client = $this->resolveClient();
        $orderId = $request->integer('order_id');

        $order = Order::where('id', $orderId)
            ->where('client_id', $client->id)
            ->with(['orderItems.product', 'orderItems.orderItemUnits.equipmentUnit.product'])
            ->first();

        if (! $order) {
            return response()->json([]);
        }

        // Ambil unit yang dialokasikan di pesanan
        $allocatedUnits = $order->orderItems->flatMap(function ($item) {
            return $item->orderItemUnits->map(function ($oiu) use ($item) {
                $u = $oiu->equipmentUnit;
                if (! $u) {
                    return null;
                }
                return [
                    'id' => $u->id,
                    'unit_code' => $u->unit_code,
                    'serial_number' => $u->serial_number ?? '-',
                    'product_name' => $item->product->name ?? ($u->product->name ?? '-'),
                    'status' => $u->status,
                    'condition' => $u->condition,
                    'is_allocated' => true,
                ];
            });
        })->filter()->values();

        if ($allocatedUnits->isNotEmpty()) {
            return response()->json($allocatedUnits);
        }

        // Jika belum ada alokasi unit spesifik, tampilkan unit milik client yang sesuai dengan produk order
        $productIds = $order->orderItems->pluck('product_id')->toArray();
        $matchingUnits = EquipmentUnit::where('client_id', $client->id)
            ->whereIn('product_id', $productIds)
            ->with('product')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'unit_code' => $u->unit_code,
                    'serial_number' => $u->serial_number ?? '-',
                    'product_name' => $u->product->name ?? '-',
                    'status' => $u->status,
                    'condition' => $u->condition,
                    'is_allocated' => false,
                ];
            });

        return response()->json($matchingUnits);
    }

    /**
     * Simpan laporan dan kasus kerusakan baru ke database.
     */
    public function store(Request $request)
    {
        $client = $this->resolveClient();
        $user = Auth::user() ?? User::where('client_id', $client->id)->first();

        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'equipment_unit_id' => 'required|exists:equipment_units,id',
            'description' => 'required|string|min:10|max:2000',
            'repair_cost' => 'required|numeric|min:0',
            'fine_amount' => 'nullable|numeric|min:0',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:3072',
            'unit_status' => 'nullable|string|in:rusak,damaged,maintenance',
        ], [
            'order_id.required' => 'Silakan pilih pesanan yang bersangkutan.',
            'equipment_unit_id.required' => 'Silakan pilih unit fisik perlengkapan yang mengalami kerusakan.',
            'description.required' => 'Deskripsi kerusakan wajib diisi secara detail.',
            'description.min' => 'Deskripsi kerusakan minimal 10 karakter.',
            'repair_cost.required' => 'Nominal biaya perbaikan wajib diisi.',
            'photos.max' => 'Maksimal 5 foto bukti kerusakan yang dapat diunggah.',
            'photos.*.image' => 'File bukti harus berupa gambar (JPEG, PNG, JPG, WEBP).',
            'photos.*.max' => 'Ukuran setiap file foto tidak boleh melebihi 3 MB.',
        ]);

        $order = Order::where('id', $request->order_id)
            ->where('client_id', $client->id)
            ->firstOrFail();

        $unit = EquipmentUnit::where('id', $request->equipment_unit_id)
            ->where('client_id', $client->id)
            ->firstOrFail();

        // Unggah foto-foto bukti jika ada
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photoFile) {
                $path = $photoFile->store('damages', 'public');
                $photoPaths[] = $path;
            }
        }

        // Simpan rekaman DamageReport
        $damageReport = DamageReport::create([
            'order_id' => $order->id,
            'equipment_unit_id' => $unit->id,
            'description' => trim($request->description),
            'image' => count($photoPaths) > 0 ? json_encode($photoPaths) : null,
            'reported_by' => $user->id,
        ]);

        // Format catatan resolusi awal
        $adminName = $user->name ?? 'Admin Toko';
        $timestamp = now()->translatedFormat('d M Y, H:i');
        $repairFormatted = 'Rp ' . number_format((float) $request->repair_cost, 0, ',', '.');
        $fineFormatted = 'Rp ' . number_format((float) ($request->fine_amount ?? 0), 0, ',', '.');

        $initialNote = "[Tiket Dibuat oleh {$adminName} - {$timestamp} WIB]\n" .
            trim($request->description) . "\n" .
            "Klaim Biaya Perbaikan: {$repairFormatted} | Denda Kelalaian: {$fineFormatted}";

        // Simpan rekaman DamageCase
        $damageCase = DamageCase::create([
            'damage_report_id' => $damageReport->id,
            'repair_cost' => $request->repair_cost,
            'fine_amount' => $request->fine_amount ?? 0,
            'payment_status' => 'belum_dibayar',
            'case_status' => 'terbuka',
            'resolution_notes' => $initialNote,
        ]);

        // Perbarui status unit fisik menjadi rusak / maintenance
        $unitStatus = $request->input('unit_status', 'rusak');
        $unit->update([
            'status' => $unitStatus,
            'condition' => 'rusak',
        ]);

        if (class_exists(\App\Events\DamageCaseCreated::class)) {
            event(new \App\Events\DamageCaseCreated($damageCase));
        }

        return redirect()->route('admin.damage-cases.show', $damageCase->id)
            ->with('success', "Kasus kerusakan untuk unit [{$unit->unit_code}] berhasil diterbitkan dengan nomor tiket #{$damageCase->id}.");
    }

    /**
     * Menampilkan detail lengkap dan penanganan tiket kasus kerusakan.
     */
    public function show(DamageCase $damageCase)
    {
        $client = $this->resolveClient();
        $user = Auth::user() ?? User::where('client_id', $client->id)->first();

        $damageCase->loadMissing([
            'damageReport.order.client',
            'damageReport.order.user',
            'damageReport.equipmentUnit.product.category',
            'damageReport.reporter',
        ]);

        $this->authorizeDamageCase($client, $damageCase);

        $order = $damageCase->damageReport?->order;

        // Ambil riwayat pembayaran kompensasi dari tabel payments jika ada
        $compensationPayment = null;
        if ($order) {
            $compensationPayment = Payment::where('order_id', $order->id)
                ->where(function ($q) use ($damageCase) {
                    $q->where('notes', 'LIKE', '%#' . $damageCase->id . '%')
                      ->orWhere('notes', 'LIKE', '%Kompensasi%');
                })
                ->latest()
                ->first();
        }

        return view('admin.damage-cases.show', compact('damageCase', 'client', 'user', 'order', 'compensationPayment'));
    }

    /**
     * Kirim tanggapan / klarifikasi dari pihak toko ke customer.
     */
    public function respond(Request $request, DamageCase $damageCase)
    {
        $client = $this->resolveClient();
        $user = Auth::user() ?? User::where('client_id', $client->id)->first();

        $this->authorizeDamageCase($client, $damageCase);

        $request->validate([
            'notes' => 'required|string|min:5|max:2000',
            'case_status' => 'nullable|string|in:terbuka,menunggu_respons,dalam_tinjauan,negosiasi,menunggu_pembayaran',
        ], [
            'notes.required' => 'Catatan tanggapan tidak boleh kosong.',
            'notes.min' => 'Catatan tanggapan minimal 5 karakter.',
        ]);

        if (in_array(strtolower($damageCase->case_status), ['selesai', 'dibatalkan'])) {
            return back()->with('error', 'Kasus kerusakan ini telah ditutup / selesai dan tidak dapat ditanggapi lagi.');
        }

        $adminName = $user->name ?? 'Admin Toko';
        $timestamp = now()->translatedFormat('d M Y, H:i');
        $newNote = "[Tanggapan Toko ({$adminName}) - {$timestamp} WIB]\n" . trim($request->notes);

        $existingNotes = $damageCase->resolution_notes ? trim($damageCase->resolution_notes) . "\n\n" : '';
        $updatedNotes = $existingNotes . $newNote;

        $targetStatus = $request->input('case_status', 'menunggu_respons');

        $damageCase->update([
            'resolution_notes' => $updatedNotes,
            'case_status' => $targetStatus,
        ]);

        return back()->with('success', 'Tanggapan pihak toko berhasil dicatat dan status kasus diperbarui.');
    }

    /**
     * Verifikasi bukti pembayaran kompensasi kerusakan menjadi lunas.
     */
    public function verifyPayment(Request $request, DamageCase $damageCase)
    {
        $client = $this->resolveClient();
        $user = Auth::user() ?? User::where('client_id', $client->id)->first();

        $this->authorizeDamageCase($client, $damageCase);

        if (strtolower($damageCase->payment_status) === 'lunas') {
            return back()->with('error', 'Pembayaran untuk tiket kerusakan ini sudah berstatus lunas.');
        }

        $order = $damageCase->damageReport?->order;

        // Cari atau buat record Payment
        if ($order) {
            $payment = Payment::where('order_id', $order->id)
                ->where(function ($q) use ($damageCase) {
                    $q->where('notes', 'LIKE', '%#' . $damageCase->id . '%')
                      ->orWhere('notes', 'LIKE', '%Kompensasi%');
                })
                ->latest()
                ->first();

            if ($payment) {
                $payment->update([
                    'status' => 'diterima',
                    'notes' => ($payment->notes ? $payment->notes . ' | ' : '') . 'Diverifikasi Lunas oleh Toko',
                ]);
            } else {
                Payment::create([
                    'order_id' => $order->id,
                    'amount' => $damageCase->total_amount,
                    'status' => 'diterima',
                    'notes' => 'Pembayaran Kompensasi Kerusakan Tiket #' . $damageCase->id . ' (Diverifikasi Manual)',
                    'paid_at' => now(),
                ]);
            }
        }

        $adminName = $user->name ?? 'Admin Toko';
        $timestamp = now()->translatedFormat('d M Y, H:i');
        $notePayment = "[Pembayaran Kompensasi Terverifikasi ({$adminName}) - {$timestamp} WIB]\n" .
            "Pembayaran ganti rugi sebesar {$damageCase->formatted_total} telah diverifikasi dan diterima secara sah.";

        $existingNotes = $damageCase->resolution_notes ? trim($damageCase->resolution_notes) . "\n\n" : '';
        $updatedNotes = $existingNotes . $notePayment;

        $damageCase->update([
            'payment_status' => 'lunas',
            'case_status' => 'lunas',
            'resolution_notes' => $updatedNotes,
        ]);

        return back()->with('success', "Pembayaran kompensasi sebesar {$damageCase->formatted_total} berhasil diverifikasi dan ditandai lunas.");
    }

    /**
     * Konfirmasi penyelesaian dan penutupan kasus kerusakan serta pembaruan status unit fisik.
     */
    public function close(Request $request, DamageCase $damageCase)
    {
        $client = $this->resolveClient();
        $user = Auth::user() ?? User::where('client_id', $client->id)->first();

        $this->authorizeDamageCase($client, $damageCase);

        $request->validate([
            'closing_notes' => 'nullable|string|max:1000',
            'unit_status_after' => 'required|string|in:tersedia,available,maintenance,rusak,damaged',
        ], [
            'unit_status_after.required' => 'Silakan tentukan status akhir unit fisik perlengkapan.',
        ]);

        $unit = $damageCase->damageReport?->equipmentUnit;
        $newUnitStatus = $request->input('unit_status_after');

        if ($unit) {
            $unitCondition = in_array($newUnitStatus, ['tersedia', 'available']) ? 'baik' : 'rusak';
            $unit->update([
                'status' => $newUnitStatus,
                'condition' => $unitCondition,
            ]);
        }

        $adminName = $user->name ?? 'Admin Toko';
        $timestamp = now()->translatedFormat('d M Y, H:i');
        $closingNote = "[Kasus Ditutup & Selesai ({$adminName}) - {$timestamp} WIB]\n" .
            "Status unit fisik diubah menjadi: " . ucfirst($newUnitStatus) .
            ($request->closing_notes ? "\nCatatan Penutupan: " . trim($request->closing_notes) : '');

        $existingNotes = $damageCase->resolution_notes ? trim($damageCase->resolution_notes) . "\n\n" : '';
        $updatedNotes = $existingNotes . $closingNote;

        $damageCase->update([
            'case_status' => 'selesai',
            'resolution_notes' => $updatedNotes,
        ]);

        if (class_exists(\App\Events\DamageCaseResolved::class)) {
            event(new \App\Events\DamageCaseResolved($damageCase));
        }

        return back()->with('success', "Kasus kerusakan tiket #{$damageCase->id} telah resmi ditutup. Status unit fisik diperbarui.");
    }

    /**
     * Dapatkan instance client tenant yang sedang aktif.
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
     * Otorisasi kepemilikan kasus kerusakan pada client yang sedang login.
     */
    private function authorizeDamageCase(Client $client, DamageCase $damageCase): void
    {
        $orderClientId = $damageCase->damageReport?->order?->client_id;
        $unitClientId = $damageCase->damageReport?->equipmentUnit?->client_id;

        if ($orderClientId !== $client->id && $unitClientId !== $client->id) {
            abort(403, 'Akses ditolak: Kasus kerusakan ini bukan milik toko Anda.');
        }
    }
}
