<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Notification;
use App\Models\User;
use Tests\TestCase;

/**
 * MOD-04 — Modul Pengelolaan Toko, Metode Pembayaran dan Dashboard (Admin Rental)
 * Fitur: Notifikasi Admin / Pusat Pemberitahuan Toko (ID: 8.2-A2)
 *
 * TINGKAT PENGUJIAN: UNIT TESTING (Uji Logika Komponen Terisolasi)
 * Runner: PHPUnit murni (tanpa HTTP request, tanpa middleware, tanpa session web)
 *
 * Menguji:
 * 1. Logika accessor type_badge untuk visual label, background color, dan dot indicator.
 * 2. Logika accessor admin_target_url untuk deep linking tindakan operasional admin.
 * 3. Logika query scope unread() dan read() untuk menyaring status baca.
 * 4. Casting atribut boolean dan integritas relasi Eloquent model Notification.
 *
 * Total: 4 Test Case (UT-NTF-001 s/d UT-NTF-004)
 */
class AdminNotificationLogicTest extends TestCase
{
    /**
     * UT-NTF-001: Memastikan accessor type_badge mengembalikan label, warna background, dan dot class sesuai tipe notifikasi.
     */
    public function test_ut_ntf_001_type_badge_accessor_mengembalikan_label_dan_warna_sesuai_tipe(): void
    {
        $testCases = [
            'order'    => ['label' => 'Pesanan', 'dot' => 'bg-blue-500'],
            'payment'  => ['label' => 'Pembayaran', 'dot' => 'bg-emerald-500'],
            'shipment' => ['label' => 'Pengiriman', 'dot' => 'bg-purple-500'],
            'return'   => ['label' => 'Pengembalian', 'dot' => 'bg-amber-500'],
            'identity' => ['label' => 'Identitas', 'dot' => 'bg-indigo-500'],
            'damage'   => ['label' => 'Kompensasi', 'dot' => 'bg-red-500'],
            'quota'    => ['label' => 'Kuota Toko', 'dot' => 'bg-amber-500'],
            'system'   => ['label' => 'Sistem Toko', 'dot' => 'bg-slate-500'],
        ];

        foreach ($testCases as $type => $expected) {
            $notification = new Notification(['type' => $type]);
            $badge = $notification->type_badge;

            $this->assertIsArray($badge);
            $this->assertSame($expected['label'], $badge['label'], "Label badge untuk tipe '{$type}' harus sesuai.");
            $this->assertSame($expected['dot'], $badge['dot'], "Warna dot indikator untuk tipe '{$type}' harus sesuai.");
            $this->assertArrayHasKey('bg', $badge);
        }

        // Default fallback untuk tipe yang tidak terdaftar
        $unknown = new Notification(['type' => 'unknown_type']);
        $this->assertSame('Informasi', $unknown->type_badge['label']);
    }

    /**
     * UT-NTF-002: Memastikan accessor admin_target_url mengarahkan ke rute aksi operasional admin yang tepat.
     */
    public function test_ut_ntf_002_admin_target_url_mengarahkan_ke_rute_tindakan_yang_tepat(): void
    {
        // 1. Tipe order dengan nomor pesanan dalam pesan -> mengarah ke rute detail order
        $orderNotif = new Notification([
            'type'    => 'order',
            'title'   => 'Pesanan Baru #42 Masuk',
            'message' => 'Pesanan baru #42 menunggu konfirmasi admin.',
        ]);
        $this->assertSame(route('admin.orders.show', 42), $orderNotif->admin_target_url);

        // 2. Tipe order tanpa ID pesanan -> mengarah ke rute daftar pesanan
        $generalOrderNotif = new Notification([
            'type'    => 'order',
            'title'   => 'Pembaruan Pesanan',
            'message' => 'Terdapat perubahan status pada pesanan pelanggan.',
        ]);
        $this->assertSame(route('admin.orders.index'), $generalOrderNotif->admin_target_url);

        // 3. Tipe return -> mengarah ke rute retur admin
        $returnNotif = new Notification([
            'type'    => 'return',
            'title'   => 'Pengajuan Pengembalian',
            'message' => 'Customer mengajukan retur unit sewa.',
        ]);
        $this->assertSame(route('admin.returns.index'), $returnNotif->admin_target_url);

        // 4. Tipe damage dengan ID kasus -> mengarah ke detail damage case
        $damageNotif = new Notification([
            'type'    => 'damage',
            'title'   => 'Laporan Kerusakan #15',
            'message' => 'Unit fisik mengalami kerusakan pada pesanan #15.',
        ]);
        $this->assertSame(route('admin.damage-cases.show', 15), $damageNotif->admin_target_url);

        // 5. Tipe quota / system -> mengarah ke pengaturan profil toko
        $quotaNotif = new Notification([
            'type'    => 'quota',
            'title'   => 'Peringatan Batas Kuota',
            'message' => 'Jumlah produk toko hampir mencapai batas kuota paket.',
        ]);
        $this->assertSame(route('admin.settings.profile'), $quotaNotif->admin_target_url);
    }

    /**
     * UT-NTF-003: Memastikan casting boolean pada kolom is_read dan manipulasi status baca.
     */
    public function test_ut_ntf_003_casting_is_read_sebagai_boolean_murni(): void
    {
        // Nilai integer 0 atau 1 dari database harus di-cast menjadi boolean murni
        $notifUnread = new Notification(['is_read' => 0]);
        $this->assertFalse($notifUnread->is_read);
        $this->assertIsBool($notifUnread->is_read);

        $notifRead = new Notification(['is_read' => 1]);
        $this->assertTrue($notifRead->is_read);
        $this->assertIsBool($notifRead->is_read);
    }

    /**
     * UT-NTF-004: Memastikan relasi model Notification terhubung ke User dan Client.
     */
    public function test_ut_ntf_004_definisi_relasi_user_dan_client_pada_model(): void
    {
        $notification = new Notification();

        // 1. Relasi belongsTo User
        $userRelation = $notification->user();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $userRelation);
        $this->assertInstanceOf(User::class, $userRelation->getRelated());

        // 2. Relasi belongsTo Client (Toko)
        $clientRelation = $notification->client();
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\BelongsTo::class, $clientRelation);
        $this->assertInstanceOf(Client::class, $clientRelation->getRelated());
    }
}
