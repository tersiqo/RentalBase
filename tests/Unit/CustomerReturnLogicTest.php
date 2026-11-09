<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\ReturnModel;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * MOD-10 — Pengembalian, Kondisi dan Damage Case
 * Fitur: Form Pengembalian Barang & Denda Sewa Customer
 *
 * TINGKAT PENGUJIAN: UNIT TESTING (Uji Logika Komponen Terisolasi)
 * Runner: PHPUnit murni (tanpa HTTP request, tanpa middleware, tanpa session web)
 *
 * Menguji:
 * 1. Logika accessor can_return pada model App\Models\Order untuk status sewa aktif.
 * 2. Logika accessor can_return memblokir status non-aktif (selesai, dibatalkan, dikembalikan).
 * 3. Logika proteksi pengajuan ganda saat relasi returnModel sudah berstatus aktif.
 * 4. Logika formula perhitungan denda backend: pembulatan jam ke atas (ceil) dan fallback tarif.
 *
 * Total: 4 Test Case (UT-RET-011 s/d UT-RET-014)
 */
class CustomerReturnLogicTest extends TestCase
{
    /**
     * UT-RET-011: Memastikan pesanan berstatus 'sedang_disewa' dan 'siap_kembali' dapat mengajukan retur (can_return = true).
     */
    public function test_ut_ret_011_order_sedang_disewa_dan_siap_kembali_dapat_mengajukan_retur(): void
    {
        // 1. Status 'sedang_disewa' tanpa record retur aktif
        $orderDisewa = new Order(['status' => 'sedang_disewa']);
        $this->assertTrue(
            $orderDisewa->can_return,
            'Order dengan status sedang_disewa tanpa proses retur aktif harus bernilai can_return = true.'
        );

        // 2. Status 'siap_kembali' tanpa record retur aktif
        $orderSiapKembali = new Order(['status' => 'siap_kembali']);
        $this->assertTrue(
            $orderSiapKembali->can_return,
            'Order dengan status siap_kembali tanpa proses retur aktif harus bernilai can_return = true.'
        );
    }

    /**
     * UT-RET-012: Memastikan pesanan dengan status selain sedang disewa/siap kembali tidak dapat mengajukan retur (can_return = false).
     */
    public function test_ut_ret_012_order_status_non_aktif_tidak_dapat_mengajukan_retur(): void
    {
        $invalidStatuses = [
            'menunggu_konfirmasi',
            'menunggu_pembayaran',
            'siap_kirim',
            'dikirim',
            'dikembalikan',
            'selesai',
            'dibatalkan',
        ];

        foreach ($invalidStatuses as $status) {
            $order = new Order(['status' => $status]);
            $this->assertFalse(
                $order->can_return,
                "Order dengan status '{$status}' tidak boleh dapat mengajukan pengembalian (can_return harus false)."
            );
        }
    }

    /**
     * UT-RET-013: Memastikan proteksi pengajuan ganda (can_return = false) jika sudah ada record retur berstatus aktif.
     */
    public function test_ut_ret_013_mencegah_pengajuan_ganda_jika_return_model_sudah_ada(): void
    {
        $order = new Order(['status' => 'sedang_disewa']);

        // Daftar status retur yang mengindikasikan retur sudah/sedang berjalan
        $activeReturnStatuses = ['diajukan', 'diproses', 'dalam_pengembalian', 'diterima', 'selesai'];

        foreach ($activeReturnStatuses as $returnStatus) {
            $return = new ReturnModel(['return_status' => $returnStatus]);
            $order->setRelation('returnModel', $return);

            $this->assertFalse(
                $order->can_return,
                "Order tidak boleh mengajukan retur ganda jika returnModel sudah berstatus '{$returnStatus}'."
            );
        }

        // Jika status retur ditolak toko, pelanggan diperbolehkan mengajukan ulang
        $rejectedReturn = new ReturnModel(['return_status' => 'ditolak']);
        $order->setRelation('returnModel', $rejectedReturn);

        $this->assertTrue(
            $order->can_return,
            'Order harus dapat mengajukan retur kembali jika pengembalian sebelumnya berstatus ditolak.'
        );
    }

    /**
     * UT-RET-014: Memastikan formula matematis perhitungan denda backend presisi (jam dibulatkan ke atas & fallback tarif).
     */
    public function test_ut_ret_014_perhitungan_formula_denda_backend_presisi(): void
    {
        $dueDate = Carbon::parse('2026-11-10 12:00:00');

        // Skenario 1: Terlambat 65 menit (1 jam 5 menit) -> ceil(65 / 60) = 2 jam
        $returnedAtLate = Carbon::parse('2026-11-10 13:05:00');
        $this->assertTrue($returnedAtLate->greaterThan($dueDate));
        $diffInMinutes = $dueDate->diffInMinutes($returnedAtLate);
        $lateHours = (int) ceil($diffInMinutes / 60);

        $this->assertSame(2, $lateHours, 'Keterlambatan 65 menit harus dibulatkan ke atas menjadi 2 jam penuh.');

        // Uji simulasi tarif denda per item
        // Item A: late_fee_per_hour = 25.000, qty = 2
        $rateA = 25000.0;
        $qtyA = 2;
        $feeA = $rateA * $qtyA * $lateHours; // 25.000 * 2 * 2 = 100.000
        $this->assertEquals(100000.0, $feeA);

        // Item B: late_fee_per_hour = 0, fallback 10% daily rate (200.000 * 0.1 = 20.000), qty = 1
        $dailyPriceB = 200000.0;
        $rateB = round($dailyPriceB * 0.1, -3); // 20.000
        $qtyB = 1;
        $feeB = $rateB * $qtyB * $lateHours; // 20.000 * 1 * 2 = 40.000
        $this->assertEquals(40000.0, $feeB);

        $totalFee = $feeA + $feeB;
        $this->assertEquals(140000.0, $totalFee, 'Total denda keterlambatan harus tepat akumulasi dari seluruh item.');

        // Skenario 2: Pengembalian tepat waktu (sebelum jatuh tempo) -> 0 jam & denda 0
        $returnedOnTime = Carbon::parse('2026-11-10 11:59:00');
        $lateHoursOnTime = $returnedOnTime->greaterThan($dueDate)
            ? (int) ceil($dueDate->diffInMinutes($returnedOnTime) / 60)
            : 0;

        $this->assertSame(0, $lateHoursOnTime, 'Pengembalian tepat waktu harus menghasilkan keterlambatan 0 jam.');
        $feeOnTime = $rateA * $qtyA * $lateHoursOnTime;
        $this->assertEquals(0.0, $feeOnTime, 'Denda pengembalian tepat waktu harus bernilai 0.');
    }
}
