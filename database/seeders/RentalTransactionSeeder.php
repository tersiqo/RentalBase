<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\ConditionCheck;
use App\Models\DamageCase;
use App\Models\DamageReport;
use App\Models\IdentityGuarantee;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Database\Seeder;

class RentalTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // =============================================================
        // AMBIL CLIENT
        // =============================================================
        $clientJaya = Client::where('subdomain', 'jaya')->firstOrFail();

        $clientOutdoor = Client::where('subdomain', 'outdoor')->firstOrFail();

        // =============================================================
        // AMBIL USER
        // =============================================================
        $customer = User::where(
            'email',
            'customer@rentalbase.test'
        )->firstOrFail();

        $adminJaya = User::where(
            'email',
            'admin@jaya.test'
        )->firstOrFail();

        $adminOutdoor = User::where(
            'email',
            'admin@outdoor.test'
        )->firstOrFail();

        // =============================================================
        // AMBIL PRODUCT
        // =============================================================
        $tendaJaya = Product::where('client_id', $clientJaya->id)
            ->where('nama', 'Tenda Dome Eiger 4-Person Waterproof')
            ->firstOrFail();

        $tendaOutdoor = Product::where('client_id', $clientOutdoor->id)
            ->where('nama', 'Tenda Consina Magnum 4 Person')
            ->firstOrFail();

        // =============================================================
        // ORDER 1
        // CLIENT: JAYA EQUIPMENT
        // STATUS: selesai
        // =============================================================
        $orderJaya = Order::updateOrCreate(
            [
                'kode_order' => 'ORD-JAYA-0001',
            ],
            [
                'client_id' => $clientJaya->id,
                'customer_id' => $customer->id,
                'tanggal_mulai' => '2026-10-01',
                'tanggal_selesai' => '2026-10-03',
                'alamat_pengiriman' => 'Jl. Veteran No. 10, Malang',
                'total_harga' => 100000,
                'status' => 'selesai',
            ]
        );

        // =============================================================
        // ORDER ITEM JAYA
        // =============================================================
        OrderItem::updateOrCreate(
            [
                'order_id' => $orderJaya->id,
                'product_id' => $tendaJaya->id,
            ],
            [
                'jumlah' => 2,
                'harga_satuan' => $tendaJaya->harga_sewa,
                'subtotal' => $tendaJaya->harga_sewa * 2,
            ]
        );

        // =============================================================
        // IDENTITY GUARANTEE JAYA
        // =============================================================
        IdentityGuarantee::updateOrCreate(
            [
                'order_id' => $orderJaya->id,
            ],
            [
                'customer_id' => $customer->id,
                'nama_lengkap' => 'Customer Demo',
                'nomor_identitas' => 'DEMO-KTP-0001',
                'foto_identitas' => 'identity/demo-ktp.jpg',
                'foto_wajah' => 'identity/demo-face.jpg',
                'alamat' => 'Jl. Contoh No. 1, Malang',
                'status' => 'diverifikasi',
            ]
        );

        // =============================================================
        // PAYMENT JAYA
        // =============================================================
        Payment::updateOrCreate(
            [
                'order_id' => $orderJaya->id,
            ],
            [
                'metode' => 'bank_transfer',
                'bukti_pembayaran' => 'payments/demo-payment-jaya.jpg',
                'tanggal_bayar' => '2026-09-30 10:00:00',
                'status' => 'diverifikasi',
                'diverifikasi_oleh' => $adminJaya->id,
                'catatan' => 'Pembayaran sesuai dengan total transaksi.',
            ]
        );

        // =============================================================
        // SHIPMENT JAYA
        // =============================================================
        Shipment::updateOrCreate(
            [
                'order_id' => $orderJaya->id,
            ],
            [
                'metode_pengiriman' => 'kurir',
                'nama_kurir' => 'JNE',
                'nomor_resi' => 'JAYA-DEMO-0001',
                'tanggal_kirim' => '2026-10-01',
                'tanggal_diterima' => '2026-10-01',
                'status_pengiriman' => 'diterima',
                'catatan' => 'Barang diterima customer.',
            ]
        );

        // =============================================================
        // RETURN JAYA
        // =============================================================
        ReturnModel::updateOrCreate(
            [
                'order_id' => $orderJaya->id,
            ],
            [
                'metode_pengembalian' => 'langsung',
                'nama_kurir' => null,
                'nomor_resi' => null,
                'tanggal_pengembalian' => '2026-10-03',
                'status_pengembalian' => 'selesai',
                'catatan' => 'Barang dikembalikan langsung ke lokasi rental.',
            ]
        );

        // =============================================================
        // CONDITION CHECK SEBELUM JAYA
        // =============================================================
        ConditionCheck::updateOrCreate(
            [
                'order_id' => $orderJaya->id,
                'tipe' => 'sebelum',
            ],
            [
                'catatan' => 'Tenda dalam kondisi baik sebelum digunakan.',
                'foto' => 'conditions/jaya-before.jpg',
                'diperiksa_oleh' => $adminJaya->id,
            ]
        );

        // =============================================================
        // CONDITION CHECK SESUDAH JAYA
        // =============================================================
        ConditionCheck::updateOrCreate(
            [
                'order_id' => $orderJaya->id,
                'tipe' => 'sesudah',
            ],
            [
                'catatan' => 'Tenda dikembalikan dalam kondisi baik.',
                'foto' => 'conditions/jaya-after.jpg',
                'diperiksa_oleh' => $adminJaya->id,
            ]
        );

        // =============================================================
        // DAMAGE REPORT JAYA
        // =============================================================
        $damageReportJaya = DamageReport::updateOrCreate(
            [
                'order_id' => $orderJaya->id,
                'deskripsi' => 'Terdapat noda kecil pada bagian luar tenda.',
            ],
            [
                'dilaporkan_oleh' => $adminJaya->id,
                'foto' => 'damage/jaya-damage.jpg',
                'status' => 'selesai',
            ]
        );

        // =============================================================
        // DAMAGE CASE JAYA
        // =============================================================
        DamageCase::updateOrCreate(
            [
                'damage_report_id' => $damageReportJaya->id,
            ],
            [
                'status' => 'selesai',
                'tanggapan_customer' => 'Saya menerima laporan kondisi tersebut.',
                'catatan_admin' => 'Kerusakan ringan dan dicatat sesuai kebijakan rental.',
                'hasil_penyelesaian' => 'Kasus selesai dan dicatat sebagai kerusakan ringan.',
            ]
        );

        // =============================================================
        // ACTIVITY LOG JAYA
        // =============================================================
        ActivityLog::updateOrCreate(
            [
                'client_id' => $clientJaya->id,
                'user_id' => $adminJaya->id,
                'aktivitas' => 'verifikasi_pembayaran',
                'deskripsi' => 'Admin memverifikasi pembayaran order ORD-JAYA-0001.',
            ],
            []
        );

        // =============================================================
        // ORDER 2
        // CLIENT: MALANG OUTDOOR
        // STATUS: menunggu_pembayaran
        // =============================================================
        $orderOutdoor = Order::updateOrCreate(
            [
                'kode_order' => 'ORD-OUTDOOR-0001',
            ],
            [
                'client_id' => $clientOutdoor->id,
                'customer_id' => $customer->id,
                'tanggal_mulai' => '2026-10-10',
                'tanggal_selesai' => '2026-10-12',
                'alamat_pengiriman' => 'Jl. Soekarno Hatta No. 20, Malang',
                'total_harga' => 70000,
                'status' => 'menunggu_pembayaran',
            ]
        );

        // =============================================================
        // ORDER ITEM OUTDOOR
        // =============================================================
        OrderItem::updateOrCreate(
            [
                'order_id' => $orderOutdoor->id,
                'product_id' => $tendaOutdoor->id,
            ],
            [
                'jumlah' => 2,
                'harga_satuan' => $tendaOutdoor->harga_sewa,
                'subtotal' => $tendaOutdoor->harga_sewa * 2,
            ]
        );

        // =============================================================
        // IDENTITY GUARANTEE OUTDOOR
        // =============================================================
        IdentityGuarantee::updateOrCreate(
            [
                'order_id' => $orderOutdoor->id,
            ],
            [
                'customer_id' => $customer->id,
                'nama_lengkap' => 'Customer Demo',
                'nomor_identitas' => 'DEMO-KTP-0001',
                'foto_identitas' => 'identity/demo-ktp.jpg',
                'foto_wajah' => 'identity/demo-face.jpg',
                'alamat' => 'Jl. Contoh No. 1, Malang',
                'status' => 'diverifikasi',
            ]
        );

        // =============================================================
        // PAYMENT OUTDOOR
        // =============================================================
        Payment::updateOrCreate(
            [
                'order_id' => $orderOutdoor->id,
            ],
            [
                'metode' => 'bank_transfer',
                'bukti_pembayaran' => null,
                'tanggal_bayar' => null,
                'status' => 'menunggu',
                'diverifikasi_oleh' => null,
                'catatan' => null,
            ]
        );

        // =============================================================
        // ACTIVITY LOG OUTDOOR
        // =============================================================
        ActivityLog::updateOrCreate(
            [
                'client_id' => $clientOutdoor->id,
                'user_id' => $adminOutdoor->id,
                'aktivitas' => 'booking_baru',
                'deskripsi' => 'Terdapat booking baru ORD-OUTDOOR-0001.',
            ],
            []
        );
    }
}