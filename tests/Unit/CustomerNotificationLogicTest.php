<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use Tests\TestCase;

/**
 * MOD-09 — Pengiriman dan Status Pesanan (Pelacakan Customer)
 * Fitur: Notifikasi Customer / Pusat Pemberitahuan Customer (ID: 8.2-A1 / 9.1-A1)
 *
 * TINGKAT PENGUJIAN: UNIT TESTING (Uji Logika Komponen Terisolasi)
 * Runner: PHPUnit murni (tanpa HTTP request, tanpa middleware, tanpa session web)
 *
 * Menguji:
 * 1. Logika accessor type_badge untuk visual label, badge background, dan dot indicator feed customer.
 * 2. Logika query scope unread() dan read() untuk menyaring status baca notifikasi.
 * 3. Logika casting atribut boolean kolom is_read.
 * 4. Integritas relasi Eloquent model Notification ke User dan Client.
 *
 * Total: 4 Test Case Unit (UT-CNT-001 s/d UT-CNT-004)
 */
class CustomerNotificationLogicTest extends TestCase
{
    /**
     * UT-CNT-001: Memastikan accessor type_badge mengembalikan label, style visual, dan dot indicator yang tepat untuk kategori notifikasi customer.
     */
    public function test_ut_cnt_001_type_badge_accessor_menghasilkan_label_dan_style_visual_sesuai_kategori(): void
    {
        $expectedTypes = [
            'order'    => ['label' => 'Pesanan', 'dot' => 'bg-blue-500'],
            'payment'  => ['label' => 'Pembayaran', 'dot' => 'bg-emerald-500'],
            'shipment' => ['label' => 'Pengiriman', 'dot' => 'bg-purple-500'],
            'return'   => ['label' => 'Pengembalian', 'dot' => 'bg-amber-500'],
            'identity' => ['label' => 'Identitas', 'dot' => 'bg-indigo-500'],
            'damage'   => ['label' => 'Kompensasi', 'dot' => 'bg-red-500'],
        ];

        foreach ($expectedTypes as $type => $expected) {
            $notification = new Notification(['type' => $type]);
            $badge = $notification->type_badge;

            $this->assertIsArray($badge);
            $this->assertSame($expected['label'], $badge['label'], "Label badge untuk tipe '{$type}' harus sesuai.");
            $this->assertSame($expected['dot'], $badge['dot'], "Warna dot indikator untuk tipe '{$type}' harus sesuai.");
            $this->assertArrayHasKey('bg', $badge, "Key 'bg' styling Tailwind harus tersedia.");
        }

        // Fallback default untuk tipe yang tidak dikenal
        $unknown = new Notification(['type' => 'custom_promo']);
        $this->assertSame('Informasi', $unknown->type_badge['label']);
    }

    /**
     * UT-CNT-002: Memastikan query scope unread() dan read() memfilter notifikasi berdasarkan kolom is_read.
     */
    public function test_ut_cnt_002_query_scope_unread_dan_read_memfilter_notifikasi_dengan_benar(): void
    {
        // Scope unread
        $unreadQuery = Notification::query()->unread();
        $this->assertStringContainsString('"is_read" = ?', $unreadQuery->toSql());
        $this->assertContains(false, $unreadQuery->getBindings());

        // Scope read
        $readQuery = Notification::query()->read();
        $this->assertStringContainsString('"is_read" = ?', $readQuery->toSql());
        $this->assertContains(true, $readQuery->getBindings());
    }

    /**
     * UT-CNT-003: Memastikan casting boolean pada kolom is_read mengubah integer 0/1 menjadi boolean murni.
     */
    public function test_ut_cnt_003_casting_is_read_menjadi_boolean_murni(): void
    {
        $notifUnread = new Notification(['is_read' => 0]);
        $this->assertFalse($notifUnread->is_read);
        $this->assertIsBool($notifUnread->is_read);

        $notifRead = new Notification(['is_read' => 1]);
        $this->assertTrue($notifRead->is_read);
        $this->assertIsBool($notifRead->is_read);
    }

    /**
     * UT-CNT-004: Memastikan relasi Eloquent model Notification terhubung ke User dan Client secara valid.
     */
    public function test_ut_cnt_004_definisi_relasi_eloquent_user_dan_client(): void
    {
        $notification = new Notification();

        // Relasi belongsTo User (Customer pemilik notifikasi)
        $userRelation = $notification->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $userRelation);
        $this->assertInstanceOf(User::class, $userRelation->getRelated());

        // Relasi belongsTo Client (Toko rental terkait)
        $clientRelation = $notification->client();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $clientRelation);
        $this->assertInstanceOf(Client::class, $clientRelation->getRelated());
    }
}
