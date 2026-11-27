<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
use App\Models\EquipmentUnit;
use App\Models\Product;
use App\Models\Subscription;
use App\Models\TenantProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoStoresDataSeeder extends Seeder
{
    /**
     * Seed full production-grade demo data for Mainan and Tenda stores.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedMainanStore();
            $this->seedTendaStore();
        });
    }

    private function seedMainanStore(): void
    {
        $subdomain = 'contoh-mainan-edukasi';
        $client = Client::where('subdomain', $subdomain)->first();

        if (! $client) {
            $client = Client::create([
                'id' => 76,
                'subdomain' => $subdomain,
                'status' => 'aktif',
            ]);
        }

        // 1. Update Client Core
        $client->update([
            'business_name' => 'EduPlay Rental Mainan & Montessori',
            'description' => 'Persewaan mainan edukasi anak, peraga kayu Montessori, sensory play, playground indoor, dan peraga motorik higienis di Malang.',
            'logo' => 'tenant-logos/eduplay-logo.png',
            'theme_color' => '#0284c7',
            'status' => 'aktif',
        ]);

        // 2. Tenant Profile
        TenantProfile::updateOrCreate(
            ['client_id' => $client->id],
            [
                'name' => 'EduPlay Rental Mainan & Montessori',
                'description' => 'Persewaan mainan edukasi anak, peraga kayu Montessori, sensory play, playground indoor, dan peraga motorik higienis di Malang.',
                'address' => 'Jl. Soekarno Hatta No. 42B, Lowokwaru',
                'city' => 'Kota Malang',
                'whatsapp' => '081234567891',
                'subdomain' => $subdomain,
                'logo_path' => 'tenant-logos/eduplay-logo.png',
                'theme_color' => '#0284c7',
                'hero_showcase_mode' => 'auto',
            ]
        );

        // 3. Active Subscription
        Subscription::updateOrCreate(
            ['client_id' => $client->id, 'plan_name' => 'Business'],
            [
                'max_products' => 50,
                'max_users' => 5,
                'price' => 299000.00,
                'billing_cycle' => 'bulanan',
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->addMonths(6),
                'status' => 'aktif',
                'payment_status' => 'diverifikasi',
                'payment_proof' => 'subscriptions/proofs/eduplay-sub.jpg',
            ]
        );

        // 4. Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin.mainan@rentalbase.id'],
            [
                'name' => 'Admin EduPlay Mainan',
                'password' => Hash::make('12345678'),
                'role' => 'admin_rental',
                'client_id' => $client->id,
                'phone' => '081234567891',
                'address' => 'Jl. Soekarno Hatta No. 42B, Lowokwaru, Kota Malang',
                'status' => 'aktif',
            ]
        );

        $client->update(['primary_admin_id' => $admin->id]);

        // 5. Payment Methods
        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client->id, 'bank_name' => 'QRIS'],
            [
                'account_number' => 'ID1020394857121',
                'account_holder' => 'EduPlay Rental Malang',
                'qris_image' => 'client-qris/eduplay-qris.png',
                'is_active' => true,
            ]
        );

        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client->id, 'bank_name' => 'BCA'],
            [
                'account_number' => '8271039481',
                'account_holder' => 'EduPlay Rental Malang',
                'qris_image' => null,
                'is_active' => true,
            ]
        );

        // 6. Categories
        $categoriesData = [
            'montessori-wooden-toys' => [
                'name' => 'Montessori & Wooden Toys',
                'description' => 'Mainan kayu ramah lingkungan standar Montessori anak usia dini.',
            ],
            'sensory-motorik-anak' => [
                'name' => 'Sensory & Motorik Anak',
                'description' => 'Media stimulasi sensorik, motorik halus, dan keseimbangan balita.',
            ],
            'role-play-sains' => [
                'name' => 'Role Play & Edukasi Sains',
                'description' => 'Peralatan bermain peran kreatif, dapur mini, dan instrumen belajar.',
            ],
            'indoor-playground-bouncer' => [
                'name' => 'Indoor Playground & Bouncer',
                'description' => 'Wahana bermain aktif dalam ruangan yang aman, bersih, dan higienis.',
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $slug => $cData) {
            $categories[$slug] = Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => $slug],
                [
                    'name' => $cData['name'],
                    'description' => $cData['description'],
                ]
            );
        }

        // 7. Products & Equipment Units
        $productsData = [
            [
                'category_slug' => 'montessori-wooden-toys',
                'name' => 'Pikler Triangle 3-in-1 Wooden Climbing Set',
                'slug' => 'pikler-triangle-3-in-1',
                'description' => 'Set panjat pikler kayu natural bersertifikasi aman untuk anak usia 1-5 tahun. Dilengkapi dengan perosotan kayu dua sisi (sisi halus meluncur dan sisi pijakan panjat tebing mini). Membantu melatih keseimbangan, kekuatan motorik kasar, serta keberanian balita di dalam rumah.',
                'rental_price_per_day' => 45000.00,
                'deposit_fee' => 100000.00,
                'late_fee_per_hour' => 5000.00,
                'main_image' => 'products/eduplay-pikler-main.jpg',
                'images' => [
                    'products/eduplay-pikler-1.jpg',
                    'products/eduplay-pikler-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'EDP-PKL-01', 'serial_number' => 'SN-PKL-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'EDP-PKL-02', 'serial_number' => 'SN-PKL-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'EDP-PKL-03', 'serial_number' => 'SN-PKL-2026-003', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
            [
                'category_slug' => 'sensory-motorik-anak',
                'name' => 'Soft Foam Ball Pit Pastel + 200 Bola Higienis',
                'slug' => 'soft-foam-ball-pit-pastel',
                'description' => 'Kolam bola busa memori premium berbalut kain velvet katun lembut yang dapat dilepas dan dicuci. Sudah termasuk 200 pcs bola pastel BPA-free yang telah disterilisasi UV. Sangat aman dan empuk untuk eksplorasi sensorik bayi dan balita.',
                'rental_price_per_day' => 50000.00,
                'deposit_fee' => 100000.00,
                'late_fee_per_hour' => 5000.00,
                'main_image' => 'products/eduplay-ballpit-main.jpg',
                'images' => [
                    'products/eduplay-ballpit-1.jpg',
                    'products/eduplay-ballpit-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'EDP-BLP-01', 'serial_number' => 'SN-BLP-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'EDP-BLP-02', 'serial_number' => 'SN-BLP-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'EDP-BLP-03', 'serial_number' => 'SN-BLP-2026-003', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
            [
                'category_slug' => 'role-play-sains',
                'name' => 'Wooden Play Kitchen Set Pastel & Cookware Set',
                'slug' => 'wooden-play-kitchen-set-pastel',
                'description' => 'Dapur mainan kayu minimalis bernuansa pastel modern lengkap dengan kompor bersuara, oven kaca berputar, wastafel, dan set panci/spatula kayu. Mengasah imajinasi sosial, komunikasi, dan kreativitas role-play anak.',
                'rental_price_per_day' => 60000.00,
                'deposit_fee' => 150000.00,
                'late_fee_per_hour' => 6000.00,
                'main_image' => 'products/eduplay-kitchen-main.jpg',
                'images' => [
                    'products/eduplay-kitchen-1.jpg',
                    'products/eduplay-kitchen-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'EDP-KTC-01', 'serial_number' => 'SN-KTC-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'EDP-KTC-02', 'serial_number' => 'SN-KTC-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
            [
                'category_slug' => 'sensory-motorik-anak',
                'name' => 'Curved Balance Board Waldorf Natural Beech Wood',
                'slug' => 'curved-balance-board-waldorf',
                'description' => 'Papan keseimbangan lengkung berbahan kayu beech eropa alami dengan lapisan pelindung transparan non-toxic. Mampu menahan beban hingga 120 kg. Dapat difungsikan sebagai ayunan santai, jembatan mobil mainan, tempat membaca buku, maupun senam stimulasi anak.',
                'rental_price_per_day' => 30000.00,
                'deposit_fee' => 50000.00,
                'late_fee_per_hour' => 3000.00,
                'main_image' => 'products/eduplay-balance-main.jpg',
                'images' => [
                    'products/eduplay-balance-1.jpg',
                    'products/eduplay-balance-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'EDP-BLB-01', 'serial_number' => 'SN-BLB-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'EDP-BLB-02', 'serial_number' => 'SN-BLB-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'EDP-BLB-03', 'serial_number' => 'SN-BLB-2026-003', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'EDP-BLB-04', 'serial_number' => 'SN-BLB-2026-004', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
            [
                'category_slug' => 'montessori-wooden-toys',
                'name' => 'Sensory Activity Busy Board Jumbo Montessori 12-in-1',
                'slug' => 'sensory-busy-board-jumbo',
                'description' => 'Papan stimulasi motorik halus ukuran jumbo 60x80cm dengan 12 fitur interaktif: resleting, gesper tas, saklar lampu LED mini, roda gigi putar, kunci grendel, dan labirin manik. Mengalihkan perhatian anak dari layar gawai (screen-free play).',
                'rental_price_per_day' => 35000.00,
                'deposit_fee' => 50000.00,
                'late_fee_per_hour' => 4000.00,
                'main_image' => 'products/eduplay-busyboard-main.jpg',
                'images' => [
                    'products/eduplay-busyboard-1.jpg',
                    'products/eduplay-busyboard-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'EDP-BSB-01', 'serial_number' => 'SN-BSB-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'EDP-BSB-02', 'serial_number' => 'SN-BSB-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'EDP-BSB-03', 'serial_number' => 'SN-BSB-2026-003', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
            [
                'category_slug' => 'indoor-playground-bouncer',
                'name' => 'Foldable Kids Mini Trampoline with Safety Handle Bar',
                'slug' => 'foldable-kids-mini-trampoline',
                'description' => 'Trampolin mini anak dengan pegangan busa empuk yang dapat diatur ketinggiannya. Menggunakan pegas elastis senyap dan bantalan tepi ekstra tebal. Sangat cocok untuk menyalurkan energi aktif dan melatih kekuatan otot anak di dalam rumah.',
                'rental_price_per_day' => 55000.00,
                'deposit_fee' => 100000.00,
                'late_fee_per_hour' => 5000.00,
                'main_image' => 'products/eduplay-trampoline-main.jpg',
                'images' => [
                    'products/eduplay-trampoline-1.jpg',
                    'products/eduplay-trampoline-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'EDP-TRM-01', 'serial_number' => 'SN-TRM-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'EDP-TRM-02', 'serial_number' => 'SN-TRM-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
        ];

        foreach ($productsData as $pData) {
            $cat = $categories[$pData['category_slug']];
            $product = Product::updateOrCreate(
                ['client_id' => $client->id, 'slug' => $pData['slug']],
                [
                    'category_id' => $cat->id,
                    'name' => $pData['name'],
                    'description' => $pData['description'],
                    'rental_price_per_day' => $pData['rental_price_per_day'],
                    'deposit_fee' => $pData['deposit_fee'],
                    'late_fee_per_hour' => $pData['late_fee_per_hour'],
                    'main_image' => $pData['main_image'],
                    'images' => $pData['images'],
                    'status' => $pData['status'],
                ]
            );

            foreach ($pData['units'] as $uData) {
                EquipmentUnit::updateOrCreate(
                    ['client_id' => $client->id, 'unit_code' => $uData['unit_code']],
                    [
                        'product_id' => $product->id,
                        'serial_number' => $uData['serial_number'],
                        'status' => $uData['status'],
                        'condition' => $uData['condition'],
                    ]
                );
            }
        }
    }

    private function seedTendaStore(): void
    {
        $subdomain = 'contoh-tenda-pesta';
        $client = Client::where('subdomain', $subdomain)->first();

        if (! $client) {
            $client = Client::create([
                'id' => 77,
                'subdomain' => $subdomain,
                'status' => 'aktif',
            ]);
        }

        // 1. Update Client Core
        $client->update([
            'business_name' => 'Berkah Tenda & Pesta Malang',
            'description' => 'Persewaan tenda plafon dekorasi, tenda VIP sarnafil, panggung rigging modul, kursi futura, misty fan blower, dan perlengkapan hajatan di Jawa Timur.',
            'logo' => 'tenant-logos/berkahtenda-logo.png',
            'theme_color' => '#1e3a8a',
            'status' => 'aktif',
        ]);

        // 2. Tenant Profile
        TenantProfile::updateOrCreate(
            ['client_id' => $client->id],
            [
                'name' => 'Berkah Tenda & Pesta Malang',
                'description' => 'Persewaan tenda plafon dekorasi, tenda VIP sarnafil, panggung rigging modul, kursi futura, misty fan blower, dan perlengkapan hajatan di Jawa Timur.',
                'address' => 'Jl. Danau Toba Raya No. 18, Sawojajar',
                'city' => 'Kota Malang',
                'whatsapp' => '081298765432',
                'subdomain' => $subdomain,
                'logo_path' => 'tenant-logos/berkahtenda-logo.png',
                'theme_color' => '#1e3a8a',
                'hero_showcase_mode' => 'auto',
            ]
        );

        // 3. Active Subscription
        Subscription::updateOrCreate(
            ['client_id' => $client->id, 'plan_name' => 'Business'],
            [
                'max_products' => 50,
                'max_users' => 5,
                'price' => 299000.00,
                'billing_cycle' => 'tahunan',
                'start_date' => Carbon::now()->subMonths(1),
                'end_date' => Carbon::now()->addMonths(11),
                'status' => 'aktif',
                'payment_status' => 'diverifikasi',
                'payment_proof' => 'subscriptions/proofs/berkahtenda-sub.jpg',
            ]
        );

        // 4. Admin User
        $admin = User::updateOrCreate(
            ['email' => 'admin.tenda@rentalbase.id'],
            [
                'name' => 'Admin Berkah Tenda',
                'password' => Hash::make('12345678'),
                'role' => 'admin_rental',
                'client_id' => $client->id,
                'phone' => '081298765432',
                'address' => 'Jl. Danau Toba Raya No. 18, Sawojajar, Kota Malang',
                'status' => 'aktif',
            ]
        );

        $client->update(['primary_admin_id' => $admin->id]);

        // 5. Payment Methods
        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client->id, 'bank_name' => 'QRIS'],
            [
                'account_number' => 'ID1098765432109',
                'account_holder' => 'Berkah Tenda Pesta Malang',
                'qris_image' => 'client-qris/berkahtenda-qris.png',
                'is_active' => true,
            ]
        );

        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client->id, 'bank_name' => 'Mandiri'],
            [
                'account_number' => '1440029381729',
                'account_holder' => 'Berkah Tenda Pesta Malang',
                'qris_image' => null,
                'is_active' => true,
            ]
        );

        // 6. Categories
        $categoriesData = [
            'tenda-pesta-roder' => [
                'name' => 'Tenda Pesta & Roder',
                'description' => 'Tenda kerucut VIP sarnafil, tenda plafon serut dekorasi, dan kanopi acara.',
            ],
            'kursi-meja-pesta' => [
                'name' => 'Kursi & Meja Pesta',
                'description' => 'Kursi futura stainless steel cover, kursi tiffany kayu rustic, dan set meja.',
            ],
            'pendingin-kelistrikan' => [
                'name' => 'Pendingin & Kelistrikan',
                'description' => 'Misty fan blower kabut air, standing blower, dan instalasi kelistrikan acara.',
            ],
            'panggung-flooring' => [
                'name' => 'Panggung & Flooring',
                'description' => 'Panggung modul rigging knock-down karpet merah dan alas lantai pameran.',
            ],
        ];

        $categories = [];
        foreach ($categoriesData as $slug => $cData) {
            $categories[$slug] = Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => $slug],
                [
                    'name' => $cData['name'],
                    'description' => $cData['description'],
                ]
            );
        }

        // 7. Products & Equipment Units
        $productsData = [
            [
                'category_slug' => 'tenda-pesta-roder',
                'name' => 'Tenda Sarnafil VIP Kerucut 3x3 Meter Putih Bersih',
                'slug' => 'tenda-sarnafil-vip-3x3m',
                'description' => 'Tenda kerucut sarnafil premium ukuran 3x3 meter dengan rangka aluminium hollow heavy-duty anti karat dan atap terpal PVC tebal 550gsm anti bocor & tahan angin kencang. Ideal untuk stand pameran VIP, posko acara, dan akad nikah outdoor.',
                'rental_price_per_day' => 200000.00,
                'deposit_fee' => 300000.00,
                'late_fee_per_hour' => 25000.00,
                'main_image' => 'products/tenda-sarnafil-main.jpg',
                'images' => [
                    'products/tenda-sarnafil-1.jpg',
                    'products/tenda-sarnafil-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'BTP-SRN-01', 'serial_number' => 'SN-SRN-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-SRN-02', 'serial_number' => 'SN-SRN-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-SRN-03', 'serial_number' => 'SN-SRN-2026-003', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-SRN-04', 'serial_number' => 'SN-SRN-2026-004', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
            [
                'category_slug' => 'tenda-pesta-roder',
                'name' => 'Paket Tenda Plafon Dekorasi VIP 4x6 Meter',
                'slug' => 'paket-tenda-plafon-dekorasi-4x6m',
                'description' => 'Tenda hajatan 4x6 meter dengan dekorasi plafon serut kain satin kombinasi putih & gold bersih. Menggunakan rangka pipa besi galvanis kokoh setinggi 3.5 meter. Sudah termasuk tirai rumbai samping dan lampu penerangan LED 50 watt.',
                'rental_price_per_day' => 450000.00,
                'deposit_fee' => 500000.00,
                'late_fee_per_hour' => 40000.00,
                'main_image' => 'products/tenda-plafon-main.jpg',
                'images' => [
                    'products/tenda-plafon-1.jpg',
                    'products/tenda-plafon-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'BTP-TND-01', 'serial_number' => 'SN-TND-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-TND-02', 'serial_number' => 'SN-TND-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-TND-03', 'serial_number' => 'SN-TND-2026-003', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
            [
                'category_slug' => 'kursi-meja-pesta',
                'name' => 'Paket Kursi Futura Stainless Steel + Sarung Ketat Putih (10 Pcs)',
                'slug' => 'paket-kursi-futura-cover-putih-10pcs',
                'description' => 'Satu set berisi 10 unit kursi Futura original dengan rangka stainless steel kokoh, busa rebound empuk tidak gembos, serta dibalut sarung stretch putih bersih tanpa noda dan pita dekorasi satin pilihan.',
                'rental_price_per_day' => 120000.00,
                'deposit_fee' => 150000.00,
                'late_fee_per_hour' => 15000.00,
                'main_image' => 'products/kursi-futura-main.jpg',
                'images' => [
                    'products/kursi-futura-1.jpg',
                    'products/kursi-futura-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'BTP-KRS-01', 'serial_number' => 'SN-KRS-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-KRS-02', 'serial_number' => 'SN-KRS-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-KRS-03', 'serial_number' => 'SN-KRS-2026-003', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-KRS-04', 'serial_number' => 'SN-KRS-2026-004', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-KRS-05', 'serial_number' => 'SN-KRS-2026-005', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-KRS-06', 'serial_number' => 'SN-KRS-2026-006', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
            [
                'category_slug' => 'kursi-meja-pesta',
                'name' => 'Kursi Tiffany Kayu Rustic VIP White / Gold (Set 5 Pcs)',
                'slug' => 'kursi-tiffany-kayu-rustic-vip-5pcs',
                'description' => 'Satu set berisi 5 unit kursi Tiffany kayu solid berdesain klasik elegan khas pesta pernikahan modern/rustic. Dilengkapi bantalan duduk busa tebal warna broken white yang nyaman untuk tamu VIP.',
                'rental_price_per_day' => 175000.00,
                'deposit_fee' => 200000.00,
                'late_fee_per_hour' => 20000.00,
                'main_image' => 'products/kursi-tiffany-main.jpg',
                'images' => [
                    'products/kursi-tiffany-1.jpg',
                    'products/kursi-tiffany-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'BTP-TIF-01', 'serial_number' => 'SN-TIF-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-TIF-02', 'serial_number' => 'SN-TIF-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-TIF-03', 'serial_number' => 'SN-TIF-2026-003', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-TIF-04', 'serial_number' => 'SN-TIF-2026-004', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
            [
                'category_slug' => 'pendingin-kelistrikan',
                'name' => 'Misty Fan Kipas Angin Embun Air Kabut Blower 26 Inch',
                'slug' => 'misty-fan-embun-blower-26-inch',
                'description' => 'Kipas angin kabut air industri berdaya hembus tinggi 26 inch dengan motor tembaga heavy duty. Tangki air besar 40 liter mampu menyemburkan partikel kabut sejuk terus-menerus hingga 8 jam tanpa henti, menjaga kenyamanan para tamu di area outdoor.',
                'rental_price_per_day' => 180000.00,
                'deposit_fee' => 250000.00,
                'late_fee_per_hour' => 20000.00,
                'main_image' => 'products/misty-fan-main.jpg',
                'images' => [
                    'products/misty-fan-1.jpg',
                    'products/misty-fan-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'BTP-FAN-01', 'serial_number' => 'SN-FAN-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-FAN-02', 'serial_number' => 'SN-FAN-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-FAN-03', 'serial_number' => 'SN-FAN-2026-003', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-FAN-04', 'serial_number' => 'SN-FAN-2026-004', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-FAN-05', 'serial_number' => 'SN-FAN-2026-005', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
            [
                'category_slug' => 'panggung-flooring',
                'name' => 'Panggung Modul Rigging Portable Karpet Merah 3x4m',
                'slug' => 'panggung-modul-rigging-portable-3x4m',
                'description' => 'Panggung modul sistem knock-down ukuran 3x4 meter dengan rangka besi holo tebal dan lantai multiplek 18mm kuat menahan beban hingga 1.5 ton. Dilapisi karpet merah tebal dan rok samping panggung hitam elegan serta tangga naik.',
                'rental_price_per_day' => 600000.00,
                'deposit_fee' => 500000.00,
                'late_fee_per_hour' => 50000.00,
                'main_image' => 'products/panggung-rigging-main.jpg',
                'images' => [
                    'products/panggung-rigging-1.jpg',
                    'products/panggung-rigging-2.jpg',
                ],
                'status' => 'aktif',
                'units' => [
                    ['unit_code' => 'BTP-PGG-01', 'serial_number' => 'SN-PGG-2026-001', 'status' => 'tersedia', 'condition' => 'baik'],
                    ['unit_code' => 'BTP-PGG-02', 'serial_number' => 'SN-PGG-2026-002', 'status' => 'tersedia', 'condition' => 'baik'],
                ],
            ],
        ];

        foreach ($productsData as $pData) {
            $cat = $categories[$pData['category_slug']];
            $product = Product::updateOrCreate(
                ['client_id' => $client->id, 'slug' => $pData['slug']],
                [
                    'category_id' => $cat->id,
                    'name' => $pData['name'],
                    'description' => $pData['description'],
                    'rental_price_per_day' => $pData['rental_price_per_day'],
                    'deposit_fee' => $pData['deposit_fee'],
                    'late_fee_per_hour' => $pData['late_fee_per_hour'],
                    'main_image' => $pData['main_image'],
                    'images' => $pData['images'],
                    'status' => $pData['status'],
                ]
            );

            foreach ($pData['units'] as $uData) {
                EquipmentUnit::updateOrCreate(
                    ['client_id' => $client->id, 'unit_code' => $uData['unit_code']],
                    [
                        'product_id' => $product->id,
                        'serial_number' => $uData['serial_number'],
                        'status' => $uData['status'],
                        'condition' => $uData['condition'],
                    ]
                );
            }
        }
    }
}
