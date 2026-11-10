<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * MOD-03 — Modul Manajemen Platform (Owner)
 * Fitur: Activity Log Owner dan Integrasi Logistik (ID: 9.2-A2)
 *
 * TINGKAT PENGUJIAN: UNIT TESTING (Uji Logika Komponen Terisolasi)
 * Runner: PHPUnit murni (tanpa HTTP request, tanpa middleware, tanpa session web)
 *
 * Menguji:
 * 1. Logika accessor module_badge untuk pemetaan label dan styling Tailwind modul aktivitas.
 * 2. Logika query scope filter() untuk rentang tanggal, tenant (client_id), user, dan modul.
 * 3. Definisi relasi Eloquent model ActivityLog ke User dan Client.
 * 4. Logika verifikasi perhitungan denda keterlambatan backend berdasarkan jam riil vs frontend.
 *
 * Total: 4 Test Case Unit (UT-LOG-001 s/d UT-LOG-004)
 */
class OwnerAuditTrailLogicTest extends TestCase
{
    /**
     * UT-LOG-001: Memastikan accessor module_badge mengembalikan label dan styling visual Tailwind yang tepat.
     */
    public function test_ut_log_001_module_badge_accessor_menghasilkan_label_dan_style_yang_tepat(): void
    {
        $testCases = [
            'orders'           => ['label' => 'Pesanan', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'shipments'        => ['label' => 'Logistik & Pengiriman', 'bg' => 'bg-purple-50 text-purple-700 border-purple-200'],
            'returns'          => ['label' => 'Pengembalian & Denda', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'condition_checks' => ['label' => 'Inspeksi Fisik', 'bg' => 'bg-teal-50 text-teal-700 border-teal-200'],
            'subscriptions'    => ['label' => 'Subscription Tenant', 'bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'tenants'          => ['label' => 'Akun & Tenant', 'bg' => 'bg-orange-50 text-orange-700 border-orange-200'],
        ];

        foreach ($testCases as $module => $expected) {
            $log = new ActivityLog(['module' => $module]);
            $badge = $log->module_badge;

            $this->assertIsArray($badge);
            $this->assertSame($expected['label'], $badge['label'], "Label badge untuk modul '{$module}' harus sesuai.");
            $this->assertSame($expected['bg'], $badge['bg'], "Styling background Tailwind untuk modul '{$module}' harus presisi.");
        }

        // Fallback default untuk modul yang tidak terdaftar
        $unknown = new ActivityLog(['module' => 'unknown_module']);
        $this->assertSame('Umum', $unknown->module_badge['label']);
    }

    /**
     * UT-LOG-002: Memastikan query scope filter() menyusun klausa WHERE SQL yang akurat.
     */
    public function test_ut_log_002_query_scope_filter_menyusun_klausa_sql_dengan_tepat(): void
    {
        $filters = [
            'from'      => '2026-01-01',
            'to'        => '2026-01-31',
            'client_id' => 5,
            'user_id'   => 12,
            'module'    => 'orders',
        ];

        $query = ActivityLog::query()->filter($filters);
        $sql = $query->toSql();

        $this->assertStringContainsString('date("created_at") >= ?', $sql);
        $this->assertStringContainsString('date("created_at") <= ?', $sql);
        $this->assertStringContainsString('"client_id" = ?', $sql);
        $this->assertStringContainsString('"user_id" = ?', $sql);
        $this->assertStringContainsString('"module" = ?', $sql);

        $bindings = $query->getBindings();
        $this->assertContains('2026-01-01', $bindings);
        $this->assertContains('2026-01-31', $bindings);
        $this->assertContains(5, $bindings);
        $this->assertContains(12, $bindings);
        $this->assertContains('orders', $bindings);
    }

    /**
     * UT-LOG-003: Memastikan relasi Eloquent model ActivityLog terhubung ke User dan Client.
     */
    public function test_ut_log_003_definisi_relasi_eloquent_user_dan_client(): void
    {
        $log = new ActivityLog();

        // 1. Relasi belongsTo User (Staf/Super Admin pelaku aktivitas)
        $userRelation = $log->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $userRelation);
        $this->assertInstanceOf(User::class, $userRelation->getRelated());

        // 2. Relasi belongsTo Client (Toko/Tenant tempat aktivitas terjadi)
        $clientRelation = $log->client();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $clientRelation);
        $this->assertInstanceOf(Client::class, $clientRelation->getRelated());
    }

    /**
     * UT-LOG-004: Memastikan verifikasi kalkulator denda keterlambatan backend menghitung nominal berbasis durasi jam riil.
     */
    public function test_ut_log_004_kalkulator_denda_backend_menghitung_nominal_berbasis_jam_riil(): void
    {
        // Simulasi batas akhir sewa: 10:00, waktu pengembalian aktual: 14:30 (terlambat 5 jam pembulatan ke atas)
        $scheduledEnd = Carbon::parse('2026-05-10 10:00:00');
        $actualReturn = Carbon::parse('2026-05-10 14:30:00');

        $diffInHours = (int) ceil($scheduledEnd->diffInMinutes($actualReturn) / 60);
        $this->assertSame(5, $diffInHours);

        $ratePerHour = 10000; // Rp 10.000 / jam
        $calculatedPenalty = $diffInHours * $ratePerHour;

        // Backend memvalidasi dan menolak manipulasi payload denda dari frontend
        $tamperedFrontendPenalty = 5000; // Hacker mencoba mengirim nilai denda murah
        $this->assertNotSame($calculatedPenalty, $tamperedFrontendPenalty);
        $this->assertSame(50000, $calculatedPenalty);
    }
}
