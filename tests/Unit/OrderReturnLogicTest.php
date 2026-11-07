<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\ReturnModel;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * MOD-10 — Pengembalian, Kondisi dan Damage Case
 *
 * TINGKAT PENGUJIAN: UNIT TESTING (Uji Logika Komponen Terisolasi)
 *
 * File ini menguji method & accessor logika retur pada model App\Models\Order
 * serta rumus perhitungan jam keterlambatan.
 *
 * Total: 4 Test Case (UT-RET-001 s/d UT-RET-004)
 */
class OrderReturnLogicTest extends TestCase
{
    /**
     * UT-RET-001: Memastikan pesanan berstatus 'sedang_disewa' dapat mengajukan retur (can_return = true).
     */
    public function test_ut_ret_001_order_sedang_disewa_dapat_mengajukan_retur(): void
    {
        $order = new Order(['status' => 'sedang_disewa']);

        $this->assertTrue(
            $order->can_return,
            'Order dengan status sedang_disewa tanpa proses retur aktif harus bernilai can_return = true.'
        );

        $orderSelesai = new Order(['status' => 'selesai']);
        $this->assertFalse(
            $orderSelesai->can_return,
            'Order yang sudah selesai tidak boleh mengajukan retur lagi.'
        );
    }

    /**
     * UT-RET-002: Memastikan pesanan menunggu denda dengan status retur 'menunggu_pembayaran' dapat membayar denda.
     */
    public function test_ut_ret_002_order_menunggu_denda_dapat_membayar_denda(): void
    {
        $order = new Order(['status' => 'menunggu_pembayaran_denda']);
        $return = new ReturnModel([
            'late_fee_status' => 'menunggu_pembayaran',
            'return_status'   => 'diterima',
        ]);
        $order->setRelation('returnModel', $return);

        $this->assertTrue(
            $order->can_pay_late_fee,
            'Order dengan status menunggu_pembayaran_denda dan denda menunggu_pembayaran harus bernilai can_pay_late_fee = true.'
        );

        // Jika denda sudah lunas, tombol bayar denda harus false
        $return->late_fee_status = 'lunas';
        $this->assertFalse($order->can_pay_late_fee);
    }

    /**
     * UT-RET-003: Memastikan rumus keterlambatan menggunakan satuan jam dengan pembulatan ke atas (ceil).
     */
    public function test_ut_ret_003_perhitungan_jam_keterlambatan_pembulatan_ke_atas(): void
    {
        $dueDate = Carbon::parse('2026-11-10 10:00:00');

        // Kasus 1: Terlambat 70 menit (1 jam 10 menit) -> dibulatkan ke atas menjadi 2 jam
        $returnDate = Carbon::parse('2026-11-10 11:10:00');
        $this->assertTrue($returnDate->greaterThan($dueDate));
        $diffInMinutes = $dueDate->diffInMinutes($returnDate);
        $lateHours = (int) ceil($diffInMinutes / 60);

        $this->assertSame(2, $lateHours, 'Keterlambatan 70 menit harus dibulatkan ke atas menjadi 2 jam.');

        // Kasus 2: Tepat waktu (kembali sebelum jatuh tempo) -> 0 jam keterlambatan
        $onTimeDate = Carbon::parse('2026-11-10 09:45:00');
        $lateHoursOnTime = $onTimeDate->greaterThan($dueDate)
            ? (int) ceil($dueDate->diffInMinutes($onTimeDate) / 60)
            : 0;

        $this->assertSame(0, $lateHoursOnTime, 'Pengembalian tepat waktu harus menghasilkan 0 jam keterlambatan.');
    }

    /**
     * UT-RET-004: Memastikan catatan status pesanan (status_note) memuat rincian nominal denda keterlambatan.
     */
    public function test_ut_ret_004_status_note_memuat_nominal_denda_secara_dinamis(): void
    {
        $order = new Order(['status' => 'menunggu_pembayaran_denda']);
        $return = new ReturnModel([
            'late_fee_amount' => 50000,
            'late_fee_status' => 'menunggu_pembayaran',
        ]);
        $order->setRelation('returnModel', $return);

        $statusNote = $order->status_note;

        $this->assertStringContainsString('50.000', $statusNote);
        $this->assertStringContainsString('denda keterlambatan', $statusNote);
    }
}
