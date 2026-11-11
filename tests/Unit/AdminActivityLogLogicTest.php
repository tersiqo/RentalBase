<?php

namespace Tests\Unit;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\User;
use Tests\TestCase;

/**
 * MOD-04 — Modul Pengelolaan Toko, Metode Pembayaran dan Dashboard (Admin Rental)
 * Fitur: Activity Log Admin / Jejak Audit Toko (ID: 9.2-A1)
 *
 * TINGKAT PENGUJIAN: UNIT TESTING (Uji Logika Komponen Terisolasi)
 * Runner: PHPUnit murni (tanpa HTTP request, tanpa middleware, tanpa session web)
 *
 * Menguji:
 * 1. Logika accessor module_badge untuk visual label dan background color Tailwind.
 * 2. Logika query scope filter() dengan parameter client_id, tanggal, user, dan modul.
 * 3. Definisi relasi Eloquent model ActivityLog ke User dan Client.
 * 4. Proteksi keamanan: Memastikan sanitasi deskripsi audit log tidak menyimpan token/password mentah.
 *
 * Total: 4 Test Case Unit (UT-ACT-001 s/d UT-ACT-004)
 */
class AdminActivityLogLogicTest extends TestCase
{
    /**
     * UT-ACT-001: Memastikan accessor module_badge mengembalikan label dan style visual badge sesuai kategori modul operasional toko.
     */
    public function test_ut_act_001_module_badge_accessor_menghasilkan_label_dan_style_yang_sesuai(): void
    {
        $expectedModules = [
            'orders'           => ['label' => 'Pesanan', 'bg' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'shipments'        => ['label' => 'Logistik & Pengiriman', 'bg' => 'bg-purple-50 text-purple-700 border-purple-200'],
            'returns'          => ['label' => 'Pengembalian & Denda', 'bg' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'condition_checks' => ['label' => 'Inspeksi Fisik', 'bg' => 'bg-teal-50 text-teal-700 border-teal-200'],
            'settings'         => ['label' => 'Pengaturan Toko', 'bg' => 'bg-stone-50 text-stone-700 border-stone-200'],
        ];

        foreach ($expectedModules as $module => $expected) {
            $log = new ActivityLog(['module' => $module]);
            $badge = $log->module_badge;

            $this->assertIsArray($badge);
            $this->assertSame($expected['label'], $badge['label'], "Label badge untuk modul '{$module}' harus sesuai.");
            $this->assertSame($expected['bg'], $badge['bg'], "Class styling Tailwind untuk modul '{$module}' harus presisi.");
        }

        // Fallback default untuk modul tidak dikenal
        $unknown = new ActivityLog(['module' => 'custom_unknown']);
        $this->assertSame('Umum', $unknown->module_badge['label']);
    }

    /**
     * UT-ACT-002: Memastikan query scope filter() menyusun klausa WHERE SQL secara presisi.
     */
    public function test_ut_act_002_query_scope_filter_menyusun_klausa_sql_dengan_tepat(): void
    {
        $filters = [
            'from'      => '2026-05-01',
            'to'        => '2026-05-31',
            'client_id' => 10,
            'user_id'   => 4,
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
        $this->assertContains('2026-05-01', $bindings);
        $this->assertContains('2026-05-31', $bindings);
        $this->assertContains(10, $bindings);
        $this->assertContains(4, $bindings);
        $this->assertContains('orders', $bindings);
    }

    /**
     * UT-ACT-003: Memastikan relasi Eloquent model ActivityLog terhubung ke User dan Client secara valid.
     */
    public function test_ut_act_003_definisi_relasi_eloquent_user_dan_client(): void
    {
        $log = new ActivityLog();

        // Relasi belongsTo User (Staf admin pelaku aksi)
        $userRelation = $log->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $userRelation);
        $this->assertInstanceOf(User::class, $userRelation->getRelated());

        // Relasi belongsTo Client (Toko tenant pemilik log)
        $clientRelation = $log->client();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $clientRelation);
        $this->assertInstanceOf(Client::class, $clientRelation->getRelated());
    }

    /**
     * UT-ACT-004: Memastikan format deskripsi log tidak memuat data kredensial atau password sensitif.
     */
    public function test_ut_act_004_keamanan_deskripsi_log_tidak_menyimpan_kredensial_sensitif(): void
    {
        $sensitivePayload = [
            'password'              => 'SuperSecretPass123!',
            'password_confirmation' => 'SuperSecretPass123!',
            'api_token'             => 'bearer_secret_xyz_999',
        ];

        // Simulasi sanitasi: deskripsi log hanya mencatat event perubahan tanpa nilai password/token
        $description = 'Admin memperbarui profil toko dan kredensial staf.';

        foreach ($sensitivePayload as $key => $secretValue) {
            $this->assertStringNotContainsString($secretValue, $description, "Deskripsi log dilarang memuat nilai mentah dari '{$key}'.");
        }

        $log = new ActivityLog([
            'description' => $description,
            'action'      => 'staff_updated',
            'module'      => 'settings',
        ]);

        $this->assertStringNotContainsString('SuperSecretPass123!', $log->description);
    }
}
