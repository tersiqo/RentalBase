<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Client;
use App\Models\ClientRegistration;
use App\Models\ConditionCheck;
use App\Models\DamageCase;
use App\Models\DamageReport;
use App\Models\EquipmentUnit;
use App\Models\IdentityGuarantee;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderItemUnit;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ReturnModel;
use App\Models\Shipment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComprehensiveTestScenarioSeeder extends Seeder
{
    public function run(): void
    {
        // -------------------------------------------------------------------------
        // 1. Akun Pengguna Inti
        // -------------------------------------------------------------------------
        $customer = User::where('email', 'iqouser@gmail.com')->first();
        if (! $customer) {
            $customer = User::create([
                'name' => 'iqouser',
                'email' => 'iqouser@gmail.com',
                'password' => bcrypt('password'),
                'role' => 'customer',
                'status' => 'aktif',
            ]);
        }

        $admin = User::where('email', 'budi@babyrental.com')->first() 
            ?? User::where('role', 'admin_rental')->first()
            ?? $customer;

        $client = Client::find(1) ?? Client::first();
        if (! $client) {
            return;
        }

        // Sinkronisasi auto-increment sequences PostgreSQL agar terhindar dari duplicate key pkey
        if (DB::connection()->getDriverName() === 'pgsql') {
            $tables = [
                'client_registrations',
                'clients',
                'subscriptions',
                'users',
                'categories',
                'products',
                'equipment_units',
                'condition_checks',
                'orders',
                'order_items',
                'order_item_units',
                'identity_guarantees',
                'payments',
                'shipments',
                'returns',
                'damage_reports',
                'damage_cases',
                'carts',
                'cart_items',
                'notifications',
            ];
            foreach ($tables as $table) {
                try {
                    DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1))");
                } catch (\Throwable $t) {
                    // Abaikan jika sequence tidak ditemukan
                }
            }
        }

        $products = Product::where('client_id', $client->id)->get();
        if ($products->isEmpty()) {
            return;
        }

        $p1 = $products->get(0) ?? $products->first(); // Stroller YOYO2 (150rb)
        $p2 = $products->get(1) ?? $p1;               // Car Seat Joie (125rb)
        $p3 = $products->get(2) ?? $p1;               // Box Bayi Nuna (100rb)
        $p4 = $products->get(3) ?? $p1;               // Haenim UV (80rb)
        $p5 = $products->get(4) ?? $p1;               // Cocolatte Pockit (90rb)
        $p6 = $products->get(5) ?? $p1;               // Spectra Pump (110rb)

        $units = EquipmentUnit::where('client_id', $client->id)->get();
        $u1 = $units->first();

        $defaultAddress = '[DATA CONTOH UJI COBA] Jl. Soekarno Hatta No. 9, Lowokwaru, Kota Malang (Alamat Simulasi)';
        $dummyKtp = 'identity_guarantees/ktp-contoh.jpg';
        $dummySelfie = 'identity_guarantees/selfie-contoh.jpg';
        $dummyPayProof = 'payments/bukti-transfer-contoh.jpg';
        $dummyRefundProof = 'refunds/bukti-refund-contoh.jpg';

        DB::beginTransaction();
        try {
            // =========================================================================
            // MODUL A: SISI OWNER PLATFORM (Pendaftaran Tenant, Langganan, & Status Toko)
            // =========================================================================

            // A1. Pendaftaran Baru Menunggu Verifikasi
            ClientRegistration::updateOrCreate(
                ['subdomain' => 'contoh-kamera-malang'],
                [
                    'business_name' => '[DATA CONTOH] Rental Kamera Malang (Menunggu Verifikasi)',
                    'description' => '[CONTOH PENGUJIAN]: Usaha rental kamera dan lensa di Kota Malang.',
                    'plan_name' => 'Starter',
                    'admin_name' => '[CONTOH] Ahmad Fotografer',
                    'admin_email' => 'contoh.ahmad@kamera.test',
                    'admin_phone' => '081234567891',
                    'admin_password' => bcrypt('password'),
                    'status' => 'menunggu_verifikasi',
                    'payment_status' => 'menunggu',
                    'payment_proof' => 'subscriptions/proofs/tf-sub-001.jpg',
                ]
            );

            // A2. Pendaftaran Tenant Ditolak dengan Alasan
            ClientRegistration::updateOrCreate(
                ['subdomain' => 'contoh-drone-surabaya'],
                [
                    'business_name' => '[DATA CONTOH] Drone Rent Surabaya (Ditolak)',
                    'description' => '[CONTOH PENGUJIAN]: Rental drone komersial.',
                    'plan_name' => 'Basic',
                    'admin_name' => '[CONTOH] Budi Pilot',
                    'admin_email' => 'contoh.budi@drone.test',
                    'admin_phone' => '081234567892',
                    'admin_password' => bcrypt('password'),
                    'status' => 'ditolak',
                    'payment_status' => 'ditolak',
                    'payment_proof' => 'subscriptions/proofs/tf-sub-001.jpg',
                    'rejection_reason' => '[CONTOH PENGUJIAN]: Bukti transfer buram dan nominal tidak sesuai paket langganan.',
                ]
            );

            // A3. Pendaftaran Tenant Disetujui
            ClientRegistration::updateOrCreate(
                ['subdomain' => 'contoh-camping-batu'],
                [
                    'business_name' => '[DATA CONTOH] Camping Gear Batu (Disetujui)',
                    'description' => '[CONTOH PENGUJIAN]: Rental alat outdoor dan mendaki gunung.',
                    'plan_name' => 'Pro Enterprise',
                    'admin_name' => '[CONTOH] Citra Outdoor',
                    'admin_email' => 'contoh.citra@camping.test',
                    'admin_phone' => '081234567893',
                    'admin_password' => bcrypt('password'),
                    'status' => 'disetujui',
                    'payment_status' => 'diverifikasi',
                    'payment_proof' => 'subscriptions/proofs/tf-sub-001.jpg',
                ]
            );

            // A4. Tenant Aktif dengan Langganan Hampir Habis (Expiring Soon - sisa <= 7 hari)
            $tenantSoon = Client::updateOrCreate(
                ['subdomain' => 'contoh-mainan-edukasi'],
                [
                    'business_name' => '[DATA CONTOH] Rental Mainan Edukasi (Akan Berakhir)',
                    'description' => '[CONTOH PENGUJIAN]: Toko persewaan mainan anak & montessori.',
                    'status' => 'aktif',
                ]
            );
            Subscription::updateOrCreate(
                ['client_id' => $tenantSoon->id, 'plan_name' => 'Basic (Simulasi Expiring)'],
                [
                    'max_products' => 30,
                    'max_users' => 2,
                    'price' => 149000.00,
                    'billing_cycle' => 'bulanan',
                    'start_date' => Carbon::now()->subDays(25),
                    'end_date' => Carbon::now()->addDays(4), // sisa 4 hari! Masuk tab "Akan Berakhir"
                    'status' => 'aktif',
                    'payment_status' => 'diverifikasi',
                ]
            );

            // A5. Tenant Langganan Kadaluarsa (Expired)
            $tenantExp = Client::updateOrCreate(
                ['subdomain' => 'contoh-tenda-pesta'],
                [
                    'business_name' => '[DATA CONTOH] Tenda Pesta Malang (Langganan Kadaluarsa)',
                    'description' => '[CONTOH PENGUJIAN]: Toko sewa tenda dan perlengkapan hajatan.',
                    'status' => 'aktif',
                ]
            );
            Subscription::updateOrCreate(
                ['client_id' => $tenantExp->id, 'plan_name' => 'Starter (Simulasi Expired)'],
                [
                    'max_products' => 15,
                    'max_users' => 1,
                    'price' => 99000.00,
                    'billing_cycle' => 'bulanan',
                    'start_date' => Carbon::now()->subMonths(2),
                    'end_date' => Carbon::now()->subDays(2), // Sudah lewat tanggal!
                    'status' => 'kadaluarsa',
                    'payment_status' => 'diverifikasi',
                ]
            );

            // A6. Tenant Ditangguhkan (Suspended)
            Client::updateOrCreate(
                ['subdomain' => 'contoh-sepeda-listrik'],
                [
                    'business_name' => '[DATA CONTOH] Rental Sepeda Listrik (Ditangguhkan)',
                    'description' => '[CONTOH PENGUJIAN]: Toko dibekukan sementara oleh Owner karena pelanggaran operasional.',
                    'status' => 'suspended',
                ]
            );

            // A7. Tenant Nonaktif
            Client::updateOrCreate(
                ['subdomain' => 'contoh-sound-system'],
                [
                    'business_name' => '[DATA CONTOH] Sound System Malang (Nonaktif)',
                    'description' => '[CONTOH PENGUJIAN]: Toko rental audio visual yang sudah dinonaktifkan.',
                    'status' => 'nonaktif',
                ]
            );


            // =========================================================================
            // MODUL B: SISI ADMIN TOKO (Staf Admin, Kategori, Produk, & Unit Fisik)
            // =========================================================================

            // B1. Staf Admin Biasa (Aktif)
            User::updateOrCreate(
                ['email' => 'staf.contoh@babyrental.com'],
                [
                    'name' => '[DATA CONTOH] Dedi Staf Operasional',
                    'password' => bcrypt('password'),
                    'role' => 'admin_rental',
                    'client_id' => $client->id,
                    'status' => 'aktif',
                ]
            );

            // B2. Staf Admin Nonaktif (Uji Kuota Admin)
            User::updateOrCreate(
                ['email' => 'staf.nonaktif@babyrental.com'],
                [
                    'name' => '[DATA CONTOH] Eko Staf Nonaktif',
                    'password' => bcrypt('password'),
                    'role' => 'admin_rental',
                    'client_id' => $client->id,
                    'status' => 'nonaktif',
                ]
            );

            // B3. Kategori Kosong (Siap Dihapus oleh Admin)
            $cat = Category::where('client_id', $client->id)->first();
            Category::updateOrCreate(
                [
                    'client_id' => $client->id,
                    'slug' => 'contoh-kategori-aksesoris-kosong',
                ],
                [
                    'name' => '[CONTOH] Kategori Aksesoris Kosong (Siap Dihapus)',
                    'description' => '[CONTOH PENGUJIAN]: Kategori tanpa produk untuk menguji fitur hapus kategori.',
                ]
            );

            // B4. Produk Draft / Nonaktif
            Product::updateOrCreate(
                [
                    'client_id' => $client->id,
                    'slug' => 'contoh-baby-walker-draft',
                ],
                [
                    'category_id' => $cat?->id ?? 1,
                    'name' => '[DATA CONTOH] Baby Walker Ergonomis (Draft/Nonaktif)',
                    'description' => '[CONTOH PENGUJIAN]: Produk berstatus nonaktif (disembunyikan dari katalog customer).',
                    'rental_price_per_day' => 60000.00,
                    'deposit_fee' => 0,
                    'late_fee_per_hour' => 10000.00,
                    'status' => 'nonaktif',
                ]
            );

            // B5. Produk Stok Habis (Semua Unit Sedang Disewa)
            $prodOut = Product::updateOrCreate(
                [
                    'client_id' => $client->id,
                    'slug' => 'contoh-baby-bouncer-habis',
                ],
                [
                    'category_id' => $cat?->id ?? 1,
                    'name' => '[DATA CONTOH] Baby Bouncer Otomatis (Stok 0 / Disewa)',
                    'description' => '[CONTOH PENGUJIAN]: Produk yang seluruh unit fisiknya sedang disewa penyewa.',
                    'rental_price_per_day' => 70000.00,
                    'deposit_fee' => 0,
                    'late_fee_per_hour' => 10000.00,
                    'status' => 'tersedia',
                ]
            );
            EquipmentUnit::updateOrCreate(
                ['unit_code' => 'UNIT-CONTOH-BOUNCER-DISEWA'],
                [
                    'client_id' => $client->id,
                    'product_id' => $prodOut->id,
                    'serial_number' => 'SN-BOUNCER-9988',
                    'condition' => 'baik',
                    'status' => 'disewa',
                ]
            );

            // B6. Unit Fisik dengan Status & Kondisi Beragam
            EquipmentUnit::updateOrCreate(
                ['unit_code' => 'UNIT-CONTOH-MAINTENANCE'],
                [
                    'client_id' => $client->id,
                    'product_id' => $p1->id,
                    'serial_number' => 'SN-YOYO-MAINT-01',
                    'condition' => 'rusak_ringan',
                    'status' => 'maintenance',
                ]
            );
            EquipmentUnit::updateOrCreate(
                ['unit_code' => 'UNIT-CONTOH-RUSAK-BERAT'],
                [
                    'client_id' => $client->id,
                    'product_id' => $p2->id,
                    'serial_number' => 'SN-JOIE-RUSAK-02',
                    'condition' => 'rusak_berat',
                    'status' => 'tidak_tersedia',
                ]
            );
            $unitGood = EquipmentUnit::updateOrCreate(
                ['unit_code' => 'UNIT-CONTOH-TERSEDIA-BAIK'],
                [
                    'client_id' => $client->id,
                    'product_id' => $p3->id,
                    'serial_number' => 'SN-NUNA-BAIK-03',
                    'condition' => 'baik',
                    'status' => 'tersedia',
                ]
            );


            // =========================================================================
            // MODUL E: REKAMAN KONDISI FISIK UNIT (Condition Checks)
            // =========================================================================
            ConditionCheck::updateOrCreate(
                [
                    'equipment_unit_id' => $unitGood->id,
                    'check_type' => 'sebelum_kirim',
                ],
                [
                    'condition' => 'baik',
                    'notes' => '[CONTOH PENGUJIAN]: Kondisi box bayi mulus 100%, engsel kokoh, busa matras bersih dan steril.',
                    'image' => null,
                    'checked_by' => $admin->id,
                ]
            );

            ConditionCheck::updateOrCreate(
                [
                    'equipment_unit_id' => $u1?->id ?? 1,
                    'check_type' => 'saat_kembali',
                ],
                [
                    'condition' => 'rusak_ringan',
                    'notes' => '[CONTOH PENGUJIAN]: Ditemukan retak rambut pada engsel kanopi lipat saat barang dikembalikan penyewa.',
                    'image' => 'damages/kanopi-retak.jpg',
                    'checked_by' => $admin->id,
                ]
            );


            // =========================================================================
            // MODUL I: NOTIFIKASI PENGGUNA (Notifications)
            // =========================================================================
            Notification::updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'title' => '[DATA CONTOH] Pengingat Batas Waktu Sewa',
                ],
                [
                    'client_id' => $client->id,
                    'message' => 'Masa sewa pesanan ORD-CONTOH-07 akan berakhir dalam 2 hari. Mohon siapkan unit untuk pengembalian.',
                    'type' => 'warning',
                    'is_read' => false,
                ]
            );

            Notification::updateOrCreate(
                [
                    'user_id' => $customer->id,
                    'title' => '[DATA CONTOH] Pembayaran Berhasil Diverifikasi',
                ],
                [
                    'client_id' => $client->id,
                    'message' => 'Pembayaran pesanan ORD-CONTOH-05 telah terverifikasi oleh admin toko. Barang sedang dipersiapkan.',
                    'type' => 'success',
                    'is_read' => true,
                ]
            );

            Notification::updateOrCreate(
                [
                    'user_id' => $admin->id,
                    'title' => '[DATA CONTOH] Bukti Pembayaran Baru Masuk',
                ],
                [
                    'client_id' => $client->id,
                    'message' => 'Penyewa telah mengunggah bukti pembayaran untuk pesanan ORD-CONTOH-04. Mohon segera diperiksa.',
                    'type' => 'info',
                    'is_read' => false,
                ]
            );


            // =========================================================================
            // MODUL C, D, F, G, H: 16 SKENARIO PESANAN LENGKAP CUSTOMER
            // =========================================================================

            // SKENARIO 1: Menunggu Verifikasi Identitas (KTP Baru Diunggah)
            $ord1 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-01-KTP-DITINJAU'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->addDays(2),
                    'rental_end_date' => Carbon::now()->addDays(4),
                    'duration_days' => 2,
                    'total_amount' => 300000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'menunggu_konfirmasi',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord1, $p1, 1, 150000.00, 300000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord1->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi KTP Ditinjau)',
                    'identity_number' => '3573010101900001',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'menunggu',
                    'reviewed_by' => null,
                ]
            );

            // SKENARIO 2: Jaminan Identitas Ditolak (Form Upload Ulang Terbuka)
            $ord2 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-02-KTP-DITOLAK'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->addDays(3),
                    'rental_end_date' => Carbon::now()->addDays(5),
                    'duration_days' => 2,
                    'total_amount' => 250000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'menunggu_konfirmasi',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord2, $p2, 1, 125000.00, 250000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord2->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi KTP Ditolak)',
                    'identity_number' => '3573010101900002',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'ditolak',
                    'reviewed_by' => $admin->id,
                ]
            );

            // SKENARIO 3: Identitas Disetujui — Menunggu Pembayaran Transfer/QRIS
            $ord3 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-03-BELUM-BAYAR'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->addDays(1),
                    'rental_end_date' => Carbon::now()->addDays(4),
                    'duration_days' => 3,
                    'total_amount' => 300000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'menunggu_pembayaran',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord3, $p3, 1, 100000.00, 300000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord3->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Belum Bayar)',
                    'identity_number' => '3573010101900003',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );

            // SKENARIO 4: Pembayaran Diunggah — Menunggu Verifikasi Admin
            $ord4 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-04-VERIFIKASI-BAYAR'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->addDays(2),
                    'rental_end_date' => Carbon::now()->addDays(4),
                    'duration_days' => 2,
                    'total_amount' => 160000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'menunggu_verifikasi',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord4, $p4, 1, 80000.00, 160000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord4->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Verifikasi Bayar)',
                    'identity_number' => '3573010101900004',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            Payment::updateOrCreate(
                ['order_id' => $ord4->id],
                [
                    'client_payment_method_id' => 1,
                    'amount' => 160000.00,
                    'payment_proof' => $dummyPayProof,
                    'status' => 'menunggu_konfirmasi',
                    'paid_at' => Carbon::now()->subHours(2),
                ]
            );

            // SKENARIO 5: Pesanan Siap Kirim (Toko Menyiapkan Barang)
            $ord5 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-05-SIAP-KIRIM'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->addDays(1),
                    'rental_end_date' => Carbon::now()->addDays(3),
                    'duration_days' => 2,
                    'total_amount' => 180000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'siap_kirim',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord5, $p5, 1, 90000.00, 180000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord5->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Siap Kirim)',
                    'identity_number' => '3573010101900005',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            Payment::updateOrCreate(
                ['order_id' => $ord5->id],
                [
                    'client_payment_method_id' => 1,
                    'amount' => 180000.00,
                    'payment_proof' => $dummyPayProof,
                    'status' => 'terverifikasi',
                    'verified_by' => $admin->id,
                    'paid_at' => Carbon::now()->subHours(6),
                    'verified_at' => Carbon::now()->subHours(4),
                ]
            );

            // SKENARIO 6: Sedang Dikirim via JNE (Resi Aktif & Tombol Terima Barang)
            $ord6 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-06-DIKIRIM-JNE'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now(),
                    'rental_end_date' => Carbon::now()->addDays(3),
                    'duration_days' => 3,
                    'total_amount' => 450000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'dikirim',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord6, $p1, 1, 150000.00, 450000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord6->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Dikirim JNE)',
                    'identity_number' => '3573010101900006',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            Payment::updateOrCreate(
                ['order_id' => $ord6->id],
                [
                    'client_payment_method_id' => 1,
                    'amount' => 450000.00,
                    'payment_proof' => $dummyPayProof,
                    'status' => 'terverifikasi',
                    'verified_by' => $admin->id,
                    'paid_at' => Carbon::now()->subDays(1),
                    'verified_at' => Carbon::now()->subHours(18),
                ]
            );
            Shipment::updateOrCreate(
                ['order_id' => $ord6->id],
                [
                    'shipping_method' => 'kurir',
                    'courier_name' => 'JNE Reguler (Simulasi)',
                    'tracking_number' => 'JNE-CONTOH-88990011',
                    'delivery_address' => $defaultAddress,
                    'status' => 'dikirim',
                    'shipped_at' => Carbon::now()->subHours(5),
                ]
            );

            // SKENARIO 7: Sedang Disewa (Aktif Berjalan + Tombol Ajukan Pengembalian)
            $ord7 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-07-SEDANG-DISEWA'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->subDays(1),
                    'rental_end_date' => Carbon::now()->addDays(3),
                    'duration_days' => 4,
                    'total_amount' => 440000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'sedang_disewa',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord7, $p6, 1, 110000.00, 440000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord7->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Sedang Disewa)',
                    'identity_number' => '3573010101900007',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            Payment::updateOrCreate(
                ['order_id' => $ord7->id],
                [
                    'client_payment_method_id' => 1,
                    'amount' => 440000.00,
                    'payment_proof' => $dummyPayProof,
                    'status' => 'terverifikasi',
                    'verified_by' => $admin->id,
                    'paid_at' => Carbon::now()->subDays(2),
                    'verified_at' => Carbon::now()->subDays(2),
                ]
            );
            Shipment::updateOrCreate(
                ['order_id' => $ord7->id],
                [
                    'shipping_method' => 'kurir',
                    'courier_name' => 'Kurir Toko (Simulasi)',
                    'tracking_number' => null,
                    'delivery_address' => $defaultAddress,
                    'status' => 'diterima',
                    'shipped_at' => Carbon::now()->subDays(2),
                    'delivered_at' => Carbon::now()->subDays(1),
                ]
            );

            // SKENARIO 8: Sedang Disewa — Overdue / Telat Kembalikan (Uji Denda Admin)
            $ord8 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-08-SEWA-TELAT-OVERDUE'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->subDays(5),
                    'rental_end_date' => Carbon::now()->subDays(2), // Lewat 48 jam!
                    'duration_days' => 3,
                    'total_amount' => 375000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'sedang_disewa',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord8, $p2, 1, 125000.00, 375000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord8->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Sewa Overdue)',
                    'identity_number' => '3573010101900008',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            Payment::updateOrCreate(
                ['order_id' => $ord8->id],
                [
                    'client_payment_method_id' => 1,
                    'amount' => 375000.00,
                    'payment_proof' => $dummyPayProof,
                    'status' => 'terverifikasi',
                    'verified_by' => $admin->id,
                    'paid_at' => Carbon::now()->subDays(5),
                    'verified_at' => Carbon::now()->subDays(5),
                ]
            );

            // SKENARIO 9: Pengembalian Diajukan (Menunggu Konfirmasi Toko)
            $ord9 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-09-PENGEMBALIAN-DIAJUKAN'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->subDays(4),
                    'rental_end_date' => Carbon::now()->subDays(1),
                    'duration_days' => 3,
                    'total_amount' => 270000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'dikembalikan',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord9, $p5, 1, 90000.00, 270000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord9->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Pengembalian Diajukan)',
                    'identity_number' => '3573010101900009',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            Payment::updateOrCreate(
                ['order_id' => $ord9->id],
                [
                    'client_payment_method_id' => 1,
                    'amount' => 270000.00,
                    'payment_proof' => $dummyPayProof,
                    'status' => 'terverifikasi',
                    'verified_by' => $admin->id,
                    'paid_at' => Carbon::now()->subDays(4),
                    'verified_at' => Carbon::now()->subDays(4),
                ]
            );
            ReturnModel::updateOrCreate(
                ['order_id' => $ord9->id],
                [
                    'return_method' => 'kurir',
                    'courier_name' => 'GoSend Instant (Simulasi)',
                    'tracking_number' => 'GS-SIMULASI-9988',
                    'return_date' => Carbon::now()->subHours(3),
                    'late_hours' => 0,
                    'calculated_late_fee' => 0,
                    'late_fee_amount' => 0,
                    'late_fee_status' => 'tidak_ada',
                    'return_status' => 'diajukan',
                    'notes' => '[CONTOH PENGUJIAN]: Paket stroller dikembalikan via kurir instant GoSend nomor order GS-SIMULASI-9988.',
                ]
            );

            // SKENARIO 10: Pesanan Selesai Normal 100% (Mulus Tanpa Kendala)
            $ord10 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-10-SELESAI-NORMAL'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->subDays(7),
                    'rental_end_date' => Carbon::now()->subDays(5),
                    'duration_days' => 2,
                    'total_amount' => 160000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'selesai',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord10, $p4, 1, 80000.00, 160000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord10->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Selesai Normal)',
                    'identity_number' => '3573010101900010',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            Payment::updateOrCreate(
                ['order_id' => $ord10->id],
                [
                    'client_payment_method_id' => 1,
                    'amount' => 160000.00,
                    'payment_proof' => $dummyPayProof,
                    'status' => 'terverifikasi',
                    'verified_by' => $admin->id,
                    'paid_at' => Carbon::now()->subDays(7),
                    'verified_at' => Carbon::now()->subDays(7),
                ]
            );
            ReturnModel::updateOrCreate(
                ['order_id' => $ord10->id],
                [
                    'return_method' => 'ambil_toko',
                    'return_date' => Carbon::now()->subDays(5),
                    'late_hours' => 0,
                    'calculated_late_fee' => 0,
                    'late_fee_amount' => 0,
                    'late_fee_status' => 'tidak_ada',
                    'return_status' => 'diterima',
                    'notes' => '[CONTOH PENGUJIAN]: Barang diterima kembali oleh toko dalam kondisi sangat bersih dan lengkap.',
                    'processed_by' => $admin->id,
                ]
            );

            // SKENARIO 11: Klaim Kerusakan Baru (Menunggu Tanggapan & Pembayaran)
            $ord11 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-11-KLAIM-RUSAK-BARU'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->subDays(6),
                    'rental_end_date' => Carbon::now()->subDays(3),
                    'duration_days' => 3,
                    'total_amount' => 450000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'selesai',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord11, $p1, 1, 150000.00, 450000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord11->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Klaim Kerusakan Baru)',
                    'identity_number' => '3573010101900011',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            $dmgRep11 = DamageReport::updateOrCreate(
                ['order_id' => $ord11->id],
                [
                    'equipment_unit_id' => $u1?->id ?? 1,
                    'description' => '[CONTOH PENGUJIAN]: Ditemukan kanopi stroller retak pada sambungan engsel lipat saat pengembalian.',
                    'image' => 'damages/kanopi-retak.jpg',
                    'reported_by' => $admin->id,
                ]
            );
            DamageCase::updateOrCreate(
                ['damage_report_id' => $dmgRep11->id],
                [
                    'repair_cost' => 150000.00,
                    'fine_amount' => 50000.00,
                    'case_status' => 'menunggu_respons',
                    'payment_status' => 'belum_dibayar',
                    'resolution_notes' => null,
                ]
            );

            // SKENARIO 12: Kasus Kerusakan dalam Sanggahan / Negosiasi Customer
            $ord12 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-12-SANGGAHAN-CUSTOMER'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->subDays(8),
                    'rental_end_date' => Carbon::now()->subDays(5),
                    'duration_days' => 3,
                    'total_amount' => 375000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'selesai',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord12, $p2, 1, 125000.00, 375000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord12->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Sanggahan Kerusakan)',
                    'identity_number' => '3573010101900012',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            $dmgRep12 = DamageReport::updateOrCreate(
                ['order_id' => $ord12->id],
                [
                    'equipment_unit_id' => $u1?->id ?? 1,
                    'description' => '[CONTOH PENGUJIAN]: Busa pegangan handle robek cukup panjang.',
                    'image' => 'damages/busa-rusak.jpg',
                    'reported_by' => $admin->id,
                ]
            );
            DamageCase::updateOrCreate(
                ['damage_report_id' => $dmgRep12->id],
                [
                    'repair_cost' => 100000.00,
                    'fine_amount' => 0.00,
                    'case_status' => 'negosiasi',
                    'payment_status' => 'belum_dibayar',
                    'resolution_notes' => '[CONTOH PENGUJIAN - SANGGAHAN PENYEWA]: Mohon maaf pihak toko, sobekan pada busa pegangan sudah ada sewaktu kami terima pertama kali dari kurir. Mohon dicek kembali foto serah-terima awal.',
                ]
            );

            // SKENARIO 13: Customer Setuju Kompensasi — Menunggu Transfer Denda
            $ord13 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-13-BAYAR-DENDA-RUSAK'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->subDays(6),
                    'rental_end_date' => Carbon::now()->subDays(3),
                    'duration_days' => 3,
                    'total_amount' => 300000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'selesai',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord13, $p3, 1, 100000.00, 300000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord13->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Menunggu Bayar Denda)',
                    'identity_number' => '3573010101900013',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            $dmgRep13 = DamageReport::updateOrCreate(
                ['order_id' => $ord13->id],
                [
                    'equipment_unit_id' => $u1?->id ?? 1,
                    'description' => '[CONTOH PENGUJIAN]: Noda tumpahan susu pada alas matras box bayi yang sulit dihilangkan.',
                    'image' => 'damages/noda-dudukan.jpg',
                    'reported_by' => $admin->id,
                ]
            );
            DamageCase::updateOrCreate(
                ['damage_report_id' => $dmgRep13->id],
                [
                    'repair_cost' => 75000.00,
                    'fine_amount' => 25000.00,
                    'case_status' => 'menunggu_pembayaran',
                    'payment_status' => 'belum_dibayar',
                    'resolution_notes' => '[CONTOH PENGUJIAN]: Penyewa menyetujui biaya jasa cuci & treatment noda sebesar Rp 100.000.',
                ]
            );

            // SKENARIO 14: Kasus Kerusakan Selesai & Lunas (Tiket Resmi Ditutup)
            $ord14 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-14-KLAIM-RUSAK-LUNAS'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->subDays(10),
                    'rental_end_date' => Carbon::now()->subDays(8),
                    'duration_days' => 2,
                    'total_amount' => 220000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'selesai',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord14, $p6, 1, 110000.00, 220000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord14->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Klaim Lunas)',
                    'identity_number' => '3573010101900014',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            $dmgRep14 = DamageReport::updateOrCreate(
                ['order_id' => $ord14->id],
                [
                    'equipment_unit_id' => $u1?->id ?? 1,
                    'description' => '[CONTOH PENGUJIAN]: Tutup corong pompa asi retak.',
                    'image' => 'damages/kanopi-retak.jpg',
                    'reported_by' => $admin->id,
                ]
            );
            DamageCase::updateOrCreate(
                ['damage_report_id' => $dmgRep14->id],
                [
                    'repair_cost' => 50000.00,
                    'fine_amount' => 0.00,
                    'case_status' => 'selesai',
                    'payment_status' => 'lunas',
                    'resolution_notes' => '[CONTOH PENGUJIAN]: Pembayaran ganti rugi sebesar Rp 50.000 telah lunas diverifikasi admin toko pada tanggal ' . Carbon::now()->subDays(7)->translatedFormat('d M Y') . '.',
                ]
            );

            // SKENARIO 15: Pesanan Dibatalkan + Refund Selesai (Ada Bukti Transfer Toko)
            $ord15 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-15-BATAL-DAN-REFUND'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->addDays(2),
                    'rental_end_date' => Carbon::now()->addDays(4),
                    'duration_days' => 2,
                    'total_amount' => 300000.00,
                    'shipping_address' => $defaultAddress,
                    'shipping_method' => 'kurir',
                    'status' => 'dibatalkan',
                    'cancellation_reason' => '[CONTOH PENGUJIAN]: Pesanan dibatalkan oleh admin karena unit mendadak harus diservis. Dana customer telah ditransfer kembali 100%.',
                    'refund_status' => 'refund_selesai',
                    'refund_proof' => $dummyRefundProof,
                ]
            );
            $this->attachItem($ord15, $p1, 1, 150000.00, 300000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord15->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Refund Selesai)',
                    'identity_number' => '3573010101900015',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            Payment::updateOrCreate(
                ['order_id' => $ord15->id],
                [
                    'client_payment_method_id' => 1,
                    'amount' => 300000.00,
                    'payment_proof' => $dummyPayProof,
                    'status' => 'terverifikasi',
                    'verified_by' => $admin->id,
                    'paid_at' => Carbon::now()->subDays(1),
                    'verified_at' => Carbon::now()->subDays(1),
                ]
            );

            // SKENARIO 16: Pengambilan Sendiri di Toko (Self-Pickup / Ongkir Rp 0)
            $ord16 = Order::updateOrCreate(
                ['order_number' => 'ORD-CONTOH-16-AMBIL-SENDIRI-TOKO'],
                [
                    'client_id' => $client->id,
                    'user_id' => $customer->id,
                    'rental_start_date' => Carbon::now()->addDays(1),
                    'rental_end_date' => Carbon::now()->addDays(3),
                    'duration_days' => 2,
                    'total_amount' => 180000.00,
                    'shipping_address' => 'Pelanggan ambil langsung di toko rental',
                    'shipping_method' => 'pickup',
                    'status' => 'siap_kirim',
                    'refund_status' => 'tidak_ada',
                ]
            );
            $this->attachItem($ord16, $p5, 1, 90000.00, 180000.00, $u1);
            IdentityGuarantee::updateOrCreate(
                ['order_id' => $ord16->id],
                [
                    'customer_id' => $customer->id,
                    'full_name' => '[DATA CONTOH] iqouser (Simulasi Ambil Sendiri)',
                    'identity_number' => '3573010101900016',
                    'identity_document_image' => $dummyKtp,
                    'face_image' => $dummySelfie,
                    'identity_address' => $defaultAddress,
                    'status' => 'diverifikasi',
                    'reviewed_by' => $admin->id,
                ]
            );
            Payment::updateOrCreate(
                ['order_id' => $ord16->id],
                [
                    'client_payment_method_id' => 1,
                    'amount' => 180000.00,
                    'payment_proof' => $dummyPayProof,
                    'status' => 'terverifikasi',
                    'verified_by' => $admin->id,
                    'paid_at' => Carbon::now()->subHours(8),
                    'verified_at' => Carbon::now()->subHours(6),
                ]
            );

            // Pre-fill keranjang belanja untuk Customer iqouser
            $cart = Cart::firstOrCreate([
                'client_id' => $client->id,
                'user_id' => $customer->id,
            ]);
            CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'product_id' => $p1->id,
                ],
                [
                    'start_date' => Carbon::now()->addDays(2),
                    'end_date' => Carbon::now()->addDays(4),
                    'duration_days' => 2,
                    'quantity' => 1,
                    'subtotal' => 300000.00,
                ]
            );
            CartItem::updateOrCreate(
                [
                    'cart_id' => $cart->id,
                    'product_id' => $p4->id,
                ],
                [
                    'start_date' => Carbon::now()->addDays(2),
                    'end_date' => Carbon::now()->addDays(4),
                    'duration_days' => 2,
                    'quantity' => 1,
                    'subtotal' => 160000.00,
                ]
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function attachItem(Order $order, Product $product, int $qty, float $price, float $subtotal, ?EquipmentUnit $unit = null): void
    {
        $item = OrderItem::updateOrCreate(
            [
                'order_id' => $order->id,
                'product_id' => $product->id,
            ],
            [
                'quantity' => $qty,
                'price_per_day' => $price,
                'subtotal' => $subtotal,
            ]
        );

        if ($unit) {
            OrderItemUnit::firstOrCreate([
                'order_item_id' => $item->id,
                'equipment_unit_id' => $unit->id,
            ]);
        }
    }
}
