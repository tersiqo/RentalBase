<?php

namespace Tests\Unit;

use App\Models\EquipmentUnit;
use App\Models\Order;
use Tests\TestCase;

/**
 * MOD-07 & MOD-08 — Master Pesanan & Detail/Verifikasi Order Admin
 *
 * TINGKAT PENGUJIAN: UNIT TESTING (Uji Logika Komponen Terisolasi)
 * Runner: PHPUnit murni (tanpa HTTP request, tanpa middleware, tanpa session web)
 *
 * Menguji:
 * 1. Logika ketersediaan jadwal unit fisik pada model EquipmentUnit (isAvailableForPeriod)
 * 2. Logika accessor status dan izin pembatalan pada model Order (status_label, can_cancel)
 *
 * Total: 4 Test Case (UT-ORD-001 s/d UT-ORD-004)
 */
class OrderVerificationLogicTest extends TestCase
{
    /**
     * UT-ORD-001: Memastikan method EquipmentUnit::isAvailableForPeriod() mengembalikan true jika tidak ada bentrok.
     */
    public function test_ut_ord_001_unit_tersedia_jika_tidak_ada_bentrok_jadwal(): void
    {
        $unit = new EquipmentUnit([
            'status' => 'tersedia',
        ]);

        $isAvailable = $unit->isAvailableForPeriod('2026-11-10 10:00:00', '2026-11-13 10:00:00');

        $this->assertTrue(
            $isAvailable,
            'Unit fisik dengan status tersedia dan tanpa bentrok jadwal harus bernilai true.'
        );
    }

    /**
     * UT-ORD-002: Memastikan EquipmentUnit::isAvailableForPeriod() mengembalikan false jika status rusak/maintenance atau tanggal invalid.
     */
    public function test_ut_ord_002_unit_tidak_tersedia_jika_status_rusak_atau_maintenance(): void
    {
        // 1. Unit berstatus rusak
        $unitRusak = new EquipmentUnit(['status' => 'rusak']);
        $this->assertFalse(
            $unitRusak->isAvailableForPeriod('2026-11-10 10:00:00', '2026-11-13 10:00:00'),
            'Unit berstatus rusak harus ditolak (false).'
        );

        // 2. Unit berstatus maintenance
        $unitMaintenance = new EquipmentUnit(['status' => 'maintenance']);
        $this->assertFalse(
            $unitMaintenance->isAvailableForPeriod('2026-11-10 10:00:00', '2026-11-13 10:00:00'),
            'Unit berstatus maintenance harus ditolak (false).'
        );

        // 3. Tanggal sewa tidak valid (end date mendahului start date)
        $unitTersedia = new EquipmentUnit(['status' => 'tersedia']);
        $this->assertFalse(
            $unitTersedia->isAvailableForPeriod('2026-11-15 10:00:00', '2026-11-10 10:00:00'),
            'Periode tanggal terbalik (end < start) harus ditolak (false).'
        );
    }

    /**
     * UT-ORD-003: Memastikan accessor Order::status_label mengembalikan label bahasa Indonesia yang benar.
     */
    public function test_ut_ord_003_status_label_mengembalikan_label_bahasa_indonesia(): void
    {
        $cases = [
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
            'menunggu_pembayaran' => 'Menunggu Pembayaran',
            'menunggu_verifikasi' => 'Menunggu Verifikasi',
            'siap_kirim'          => 'Siap Kirim',
            'sedang_disewa'       => 'Sedang Disewa',
            'selesai'             => 'Selesai',
            'dibatalkan'          => 'Batal',
        ];

        foreach ($cases as $statusKey => $expectedLabel) {
            $order = new Order(['status' => $statusKey]);
            $this->assertSame(
                $expectedLabel,
                $order->status_label,
                "Status {$statusKey} seharusnya menghasilkan label '{$expectedLabel}'."
            );
        }
    }

    /**
     * UT-ORD-004: Memastikan accessor Order::can_cancel bernilai true saat belum bayar dan false jika sudah selesai.
     */
    public function test_ut_ord_004_can_cancel_true_jika_belum_dibayar_false_jika_selesai(): void
    {
        // 1. Pesanan menunggu pembayaran -> boleh dibatalkan
        $orderMenungguBayar = new Order(['status' => 'menunggu_pembayaran']);
        $this->assertTrue($orderMenungguBayar->can_cancel);

        // 2. Pesanan belum dibayar -> boleh dibatalkan
        $orderBelumBayar = new Order(['status' => 'belum_dibayar']);
        $this->assertTrue($orderBelumBayar->can_cancel);

        // 3. Pesanan selesai -> tidak boleh dibatalkan
        $orderSelesai = new Order(['status' => 'selesai']);
        $this->assertFalse($orderSelesai->can_cancel);

        // 4. Pesanan sedang dikirim -> tidak boleh dibatalkan
        $orderDikirim = new Order(['status' => 'dikirim']);
        $this->assertFalse($orderDikirim->can_cancel);
    }
}
