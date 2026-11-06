<?php

namespace Tests\Unit;

use App\Models\Order;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * MOD-09 — Pengiriman dan Status Pesanan
 *
 * TINGKAT PENGUJIAN: UNIT TESTING (Uji Logika Komponen Terisolasi)
 *
 * File ini menguji method logika jadwal & metode penyerahan pada model App\Models\Order.
 * - Tidak menggunakan HTTP Request.
 * - Tidak menyentuh database (hanya objek PHP di memori).
 * - Berjalan sangat cepat (< 0.1 detik).
 *
 * Total: 4 Test Case (UT-SHP-001 s/d UT-SHP-004)
 */
class OrderHandoverLogicTest extends TestCase
{
    /**
     * Helper membuat objek Order di memori.
     */
    private function makeOrder(string $shippingMethod, string $rentalStart = '2026-11-12 09:00:00', string $address = 'Jl. Merdeka No. 10, Jakarta'): Order
    {
        return new Order([
            'shipping_method'   => $shippingMethod,
            'shipping_address'  => $address,
            'rental_start_date' => $rentalStart,
            'rental_end_date'   => Carbon::parse($rentalStart)->addDays(2),
            'duration_days'     => 3,
        ]);
    }

    /**
     * UT-SHP-001: Memastikan metode 'ambil_toko' dikenali sebagai Ambil Sendiri.
     */
    public function test_ut_shp_001_metode_ambil_toko_dikenali_sebagai_ambil_sendiri(): void
    {
        $order = $this->makeOrder('ambil_toko');

        $this->assertTrue(
            $order->isPickup(),
            'Order dengan shipping_method ambil_toko harus menghasilkan nilai true pada isPickup().'
        );
    }

    /**
     * UT-SHP-002: Memastikan pesanan kurir biasa BUKAN Ambil Sendiri.
     */
    public function test_ut_shp_002_pesanan_kurir_biasa_bukan_ambil_sendiri(): void
    {
        $order = $this->makeOrder('kurir');

        $this->assertFalse(
            $order->isPickup(),
            'Order dengan shipping_method kurir dan alamat biasa harus menghasilkan nilai false pada isPickup().'
        );
    }

    /**
     * UT-SHP-003: Memastikan batas jadwal kirim Kurir dibuka mulai H-1 pukul 00:00.
     */
    public function test_ut_shp_003_jadwal_kirim_kurir_dibuka_mulai_h_minus_1(): void
    {
        // Rencana mulai sewa: 12 November 2026 pukul 09:00
        $order = $this->makeOrder('kurir', '2026-11-12 09:00:00');

        // 1. Batas tanggal paling awal harus H-1 (11 November pukul 00:00:00)
        $this->assertSame('2026-11-11 00:00:00', $order->earliestHandoverDate()->format('Y-m-d H:i:s'));

        // 2. Tepat H-1 pukul 00:00:00 -> sudah boleh diproses
        $this->assertTrue($order->canProcessHandover(Carbon::parse('2026-11-11 00:00:00')));

        // 3. Pada H-2 pukul 23:59:59 (1 detik sebelum H-1) -> belum boleh diproses
        $this->assertFalse($order->canProcessHandover(Carbon::parse('2026-11-10 23:59:59')));
    }

    /**
     * UT-SHP-004: Memastikan batas jadwal penyerahan Ambil Sendiri dibuka mulai Hari-H pukul 00:00.
     */
    public function test_ut_shp_004_jadwal_ambil_sendiri_dibuka_mulai_hari_h(): void
    {
        // Rencana mulai sewa: 12 November 2026 pukul 09:00
        $order = $this->makeOrder('ambil_toko', '2026-11-12 09:00:00');

        // 1. Batas tanggal paling awal harus Hari-H (12 November pukul 00:00:00)
        $this->assertSame('2026-11-12 00:00:00', $order->earliestHandoverDate()->format('Y-m-d H:i:s'));

        // 2. Tepat Hari-H pukul 00:00:00 -> sudah boleh diproses
        $this->assertTrue($order->canProcessHandover(Carbon::parse('2026-11-12 00:00:00')));

        // 3. Pada H-1 pukul 23:59:59 (1 detik sebelum Hari-H) -> belum boleh diproses
        $this->assertFalse($order->canProcessHandover(Carbon::parse('2026-11-11 23:59:59')));
    }
}
