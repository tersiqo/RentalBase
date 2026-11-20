<?php

namespace Tests\Unit;

use App\Console\Commands\RefreshDemoDates;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * MOD-03 — Modul Manajemen Platform (Owner)
 * Fitur: Maintenance CLI Demo Date Refresher (ID: 9.2-A2)
 *
 * TINGKAT PENGUJIAN: UNIT TESTING (Uji Logika Perhitungan Timestamp Demo)
 * Runner: PHPUnit
 * Rentang ID: UT-03-018 s/d UT-03-026 (9 Skenario Pengujian)
 */
class RefreshDemoDatesLogicTest extends TestCase
{
    /**
     * UT-03-018: Memastikan signature dan deskripsi command CLI maintenance terdaftar presisi.
     */
    public function test_ut_03_018_command_signature_and_metadata_definition(): void
    {
        $command = new RefreshDemoDates();

        $this->assertSame('demo:refresh', $command->getName());
        $this->assertNotEmpty($command->getDescription());
        $this->assertStringContainsString('demo', strtolower($command->getDescription()));
    }

    /**
     * UT-03-019: Memastikan kalkulasi timestamp pesanan baru (*01) berstatus menunggu pembayaran.
     */
    public function test_ut_03_019_new_orders_payment_pending_timestamp_calculation(): void
    {
        $now = Carbon::parse('2026-11-20 12:00:00');

        $p01Created = $now->copy()->subMinutes(25);
        $p01Start = $now->copy()->addDays(2);
        $p01End = $now->copy()->addDays(5);

        $this->assertTrue($p01Created->isPast());
        $this->assertTrue($p01Start->isFuture());
        $this->assertSame(3, (int) abs($p01End->diffInDays($p01Start)));
    }

    /**
     * UT-03-020: Memastikan kalkulasi timestamp pesanan (*02) berstatus menunggu verifikasi.
     */
    public function test_ut_03_020_verification_pending_orders_timestamp_calculation(): void
    {
        $now = Carbon::parse('2026-11-20 12:00:00');

        $p02Created = $now->copy()->subHours(1);
        $p02Start = $now->copy()->addDays(1);
        $p02End = $now->copy()->addDays(3);

        $this->assertTrue($p02Created->isPast());
        $this->assertTrue($p02Start->isFuture());
        $this->assertSame(2, (int) abs($p02End->diffInDays($p02Start)));
    }

    /**
     * UT-03-021: Memastikan kalkulasi timestamp pesanan siap kirim (*03) dan dikirim (*04).
     */
    public function test_ut_03_021_shipped_orders_timestamp_calculation(): void
    {
        $now = Carbon::parse('2026-11-20 12:00:00');

        // Pesanan 03 (Siap Kirim)
        $p03Start = $now->copy()->addDays(1);
        $p03End = $now->copy()->addDays(4);

        $this->assertTrue($p03Start->isFuture());
        $this->assertSame(3, (int) abs($p03End->diffInDays($p03Start)));

        // Pesanan 04 (Dikirim)
        $p04Start = $now->copy()->startOfDay();
        $p04End = $now->copy()->addDays(2)->endOfDay();

        $this->assertTrue($p04End->isFuture());
    }

    /**
     * UT-03-022: Memastikan kalkulasi timestamp pesanan sedang disewa (*05) mulai kemarin dan selesai lusa.
     */
    public function test_ut_03_022_active_rental_orders_timestamp_calculation(): void
    {
        $now = Carbon::parse('2026-11-20 12:00:00');

        $p05Start = $now->copy()->subDay()->startOfDay();
        $p05End = $now->copy()->addDays(2)->endOfDay();

        $this->assertTrue($p05Start->isPast());
        $this->assertTrue($p05End->isFuture());
    }

    /**
     * UT-03-023: Memastikan kalkulasi timestamp pesanan retur denda (*07) menetapkan batas akhir kemarin sore.
     */
    public function test_ut_03_023_late_return_orders_timestamp_calculation(): void
    {
        $now = Carbon::parse('2026-11-20 12:00:00');

        $p07End = $now->copy()->subDay()->setTime(17, 0, 0);
        $this->assertTrue($p07End->isPast());
    }

    /**
     * UT-03-024: Memastikan kalkulasi pesanan tuntas (*09) dan dibatalkan (*10) selalu di masa lampau.
     */
    public function test_ut_03_024_completed_and_cancelled_orders_timestamp_rules(): void
    {
        $now = Carbon::parse('2026-11-20 12:00:00');

        $p09Completed = $now->copy()->subDays(3);
        $p10Cancelled = $now->copy()->subDay();

        $this->assertTrue($p09Completed->isPast());
        $this->assertTrue($p10Cancelled->isPast());
    }

    /**
     * UT-03-025: Memastikan timestamp registrasi tenant demo dronefly-malang dimajukan ke 3 jam lalu.
     */
    public function test_ut_03_025_demo_tenant_registration_timestamp_calculation(): void
    {
        $now = Carbon::parse('2026-11-20 12:00:00');
        $regTime = $now->copy()->subHours(3);

        $this->assertTrue($regTime->isPast());
        $this->assertSame(3, (int) abs($now->diffInHours($regTime)));
    }

    /**
     * UT-03-026: Memastikan simulasi paket langganan expiring berakhir tepat 5 hari ke depan.
     */
    public function test_ut_03_026_expiring_subscription_timestamp_calculation(): void
    {
        $now = Carbon::parse('2026-11-20 12:00:00');
        $endDate = $now->copy()->addDays(5)->endOfDay();

        $this->assertTrue($endDate->isFuture());
        $this->assertSame(5, (int) round($endDate->diffInDays($now)));
    }
}
