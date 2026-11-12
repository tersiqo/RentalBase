<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Client;
use App\Models\ClientPaymentMethod;
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
use App\Models\TenantProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProductionMasterDemoSeeder extends Seeder
{
    /**
     * Run the production-grade master demo seeder.
     */
    public function run(): void
    {
        $this->syncPostgresSequences();

        DB::transaction(function () {
            $users = $this->seedUsers();
            $stores = $this->seedTenantsAndCatalogs($users);
            $this->seedOwnerPlatformSamples();
            $this->seedBalancedOrdersForAllStores($users, $stores);
        });

        $this->syncPostgresSequences();
    }

    /**
     * 1. Seed Core Platform Users (Owner, Customers, Store Admins).
     */
    private function seedUsers(): array
    {
        // Owner Superadmin
        $owner = User::updateOrCreate(
            ['email' => 'owner@rentalbase.id'],
            [
                'name' => 'Owner RentalBase SaaS',
                'password' => Hash::make('password123'),
                'role' => 'owner',
                'client_id' => null,
                'phone' => '081122334455',
                'address' => 'HQ RentalBase Cyber Tower Lt. 18, Jakarta Selatan',
                'status' => 'aktif',
            ]
        );

        // Customer 1: Mas Iqo (Lead PM)
        $customer1 = User::updateOrCreate(
            ['email' => 'iqouser@gmail.com'],
            [
                'name' => 'Tersiqo Alfarezel',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'client_id' => null,
                'phone' => '081234567801',
                'address' => 'Jl. Candi Mendut Barat No. 12, Mojolangu, Lowokwaru, Kota Malang',
                'status' => 'aktif',
            ]
        );

        // Customer 2: Budi Santoso (Realistic Family / Event Renter)
        $customer2 = User::updateOrCreate(
            ['email' => 'budi.santoso@gmail.com'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'client_id' => null,
                'phone' => '081298765432',
                'address' => 'Jl. Besar Ijen No. 45, Oro-oro Dowo, Klojen, Kota Malang',
                'status' => 'aktif',
            ]
        );

        // Customer 3: Rizky Pelanggan
        $customer3 = User::updateOrCreate(
            ['email' => 'rizky@gmail.com'],
            [
                'name' => 'Rizky Pelanggan',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'client_id' => null,
                'phone' => '085711223344',
                'address' => 'Jl. Borobudur No. 88, Lowokwaru, Kota Malang',
                'status' => 'aktif',
            ]
        );

        // Customer 4: Siti Rahmawati
        $customer4 = User::updateOrCreate(
            ['email' => 'siti.customer@gmail.com'],
            [
                'name' => 'Siti Rahmawati',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'client_id' => null,
                'phone' => '081388776655',
                'address' => 'Jl. Mayjen Panjaitan No. 102, Penanggungan, Klojen, Kota Malang',
                'status' => 'aktif',
            ]
        );

        // Store Admins
        $adminBaby = User::updateOrCreate(
            ['email' => 'budi@babyrental.com'],
            [
                'name' => 'Budi Admin Baby Rental',
                'password' => Hash::make('password123'),
                'role' => 'admin_rental',
                'client_id' => 1,
                'phone' => '081234567890',
                'address' => 'Jl. Soekarno Hatta No. 45, Lowokwaru, Kota Malang',
                'status' => 'aktif',
            ]
        );

        $adminMainan = User::updateOrCreate(
            ['email' => 'admin.mainan@rentalbase.id'],
            [
                'name' => 'Admin EduPlay Mainan',
                'password' => Hash::make('password123'),
                'role' => 'admin_rental',
                'client_id' => 76,
                'phone' => '081234567891',
                'address' => 'Jl. Soekarno Hatta No. 42B, Lowokwaru, Kota Malang',
                'status' => 'aktif',
            ]
        );

        $adminTenda = User::updateOrCreate(
            ['email' => 'admin.tenda@rentalbase.id'],
            [
                'name' => 'Admin Berkah Tenda',
                'password' => Hash::make('password123'),
                'role' => 'admin_rental',
                'client_id' => 77,
                'phone' => '081234567892',
                'address' => 'Jl. Terusan Sulfat No. 88, Blimbing, Kota Malang',
                'status' => 'aktif',
            ]
        );

        $adminOutdoor = User::updateOrCreate(
            ['email' => 'admin.outdoor@rentalbase.id'],
            [
                'name' => 'Admin Malang Outdoor',
                'password' => Hash::make('password123'),
                'role' => 'admin_rental',
                'phone' => '081298765431',
                'address' => 'Jl. Bondowoso No. 18, Klojen, Kota Malang',
                'status' => 'aktif',
            ]
        );

        $adminKamera = User::updateOrCreate(
            ['email' => 'admin.kamera@rentalbase.id'],
            [
                'name' => 'Admin Lens & Light Studio',
                'password' => Hash::make('password123'),
                'role' => 'admin_rental',
                'phone' => '081298765432',
                'address' => 'Jl. Semeru No. 72, Oro-oro Dowo, Kota Malang',
                'status' => 'aktif',
            ]
        );

        $adminSound = User::updateOrCreate(
            ['email' => 'admin.sound@rentalbase.id'],
            [
                'name' => 'Admin SoundMaster Audio',
                'password' => Hash::make('password123'),
                'role' => 'admin_rental',
                'phone' => '081298765433',
                'address' => 'Jl. Kawi No. 34, Kauman, Klojen, Kota Malang',
                'status' => 'aktif',
            ]
        );

        return [
            'owner' => $owner,
            'customer1' => $customer1,
            'customer2' => $customer2,
            'customer3' => $customer3,
            'customer4' => $customer4,
            'adminBaby' => $adminBaby,
            'adminMainan' => $adminMainan,
            'adminTenda' => $adminTenda,
            'adminOutdoor' => $adminOutdoor,
            'adminKamera' => $adminKamera,
            'adminSound' => $adminSound,
        ];
    }

    /**
     * 2. Seed/Update 6 Production-Grade Active Stores with rich catalogs.
     */
    private function seedTenantsAndCatalogs(array $users): array
    {
        $now = Carbon::now();

        // -------------------------------------------------------------
        // STORE 1: Baby Rental Studio (ID: 1)
        // -------------------------------------------------------------
        $client1 = Client::find(1);
        if (! $client1) {
            $client1 = Client::create([
                'id' => 1,
                'subdomain' => 'babyrental',
                'status' => 'aktif',
            ]);
        }
        $client1->update([
            'business_name' => 'Baby Rental Studio',
            'description' => 'Persewaan perlengkapan bayi & balita premium higienis terpercaya di Malang: stroller cabin size, car seat ISOFIX, box tidur kayu, dan UV sterilizer bersertifikat.',
            'logo' => 'tenant-logos/babyrental.png',
            'theme_color' => '#F97316',
            'status' => 'aktif',
            'primary_admin_id' => $users['adminBaby']->id,
        ]);

        TenantProfile::updateOrCreate(
            ['client_id' => $client1->id],
            [
                'name' => 'Baby Rental Studio',
                'description' => 'Persewaan perlengkapan bayi & balita premium higienis terpercaya di Malang: stroller cabin size, car seat ISOFIX, box tidur kayu, dan UV sterilizer bersertifikat.',
                'address' => 'Jl. Soekarno Hatta No. 45, Lowokwaru',
                'city' => 'Kota Malang',
                'whatsapp' => '081234567890',
                'subdomain' => 'babyrental',
                'logo_path' => 'tenant-logos/babyrental.png',
                'theme_color' => '#F97316',
                'hero_showcase_mode' => 'auto',
            ]
        );

        Subscription::updateOrCreate(
            ['client_id' => $client1->id, 'plan_name' => 'Pro Enterprise'],
            [
                'max_products' => 200,
                'max_users' => 15,
                'price' => 299000.00,
                'billing_cycle' => 'bulanan',
                'start_date' => $now->copy()->subMonths(1),
                'end_date' => $now->copy()->addMonths(11),
                'status' => 'aktif',
                'payment_status' => 'diverifikasi',
                'payment_proof' => 'subscriptions/proofs/tf-sub-001.jpg',
            ]
        );

        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client1->id, 'bank_name' => 'QRIS'],
            [
                'account_number' => 'ID1020394857001',
                'account_holder' => 'Baby Rental Studio Malang',
                'qris_image' => 'client-qris/eduplay-qris.png',
                'is_active' => true,
            ]
        );
        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client1->id, 'bank_name' => 'BCA'],
            [
                'account_number' => '8830991122',
                'account_holder' => 'Baby Rental Studio Malang',
                'qris_image' => null,
                'is_active' => true,
            ]
        );

        // -------------------------------------------------------------
        // STORE 2: EduPlay Rental Mainan & Montessori (ID: 76)
        // -------------------------------------------------------------
        $client2 = Client::find(76) ?? Client::where('subdomain', 'eduplay')->first() ?? Client::where('subdomain', 'contoh-mainan-edukasi')->first();
        if (! $client2) {
            $client2 = Client::create(['id' => 76, 'subdomain' => 'eduplay', 'status' => 'aktif']);
        }
        $client2->update([
            'subdomain' => 'eduplay',
            'business_name' => 'EduPlay Rental Mainan & Montessori',
            'description' => 'Persewaan mainan edukasi anak, peraga kayu Montessori, sensory play, playground indoor, dan peraga motorik higienis di Malang.',
            'logo' => 'tenant-logos/eduplay-logo.png',
            'theme_color' => '#0284c7',
            'status' => 'aktif',
            'primary_admin_id' => $users['adminMainan']->id,
        ]);
        $users['adminMainan']->update(['client_id' => $client2->id]);

        TenantProfile::updateOrCreate(
            ['client_id' => $client2->id],
            [
                'name' => 'EduPlay Rental Mainan & Montessori',
                'description' => 'Persewaan mainan edukasi anak, peraga kayu Montessori, sensory play, playground indoor, dan peraga motorik higienis di Malang.',
                'address' => 'Jl. Soekarno Hatta No. 42B, Lowokwaru',
                'city' => 'Kota Malang',
                'whatsapp' => '081234567891',
                'subdomain' => 'eduplay',
                'logo_path' => 'tenant-logos/eduplay-logo.png',
                'theme_color' => '#0284c7',
                'hero_showcase_mode' => 'auto',
            ]
        );

        Subscription::updateOrCreate(
            ['client_id' => $client2->id, 'plan_name' => 'Business'],
            [
                'max_products' => 50,
                'max_users' => 5,
                'price' => 299000.00,
                'billing_cycle' => 'bulanan',
                'start_date' => $now->copy()->subDays(10),
                'end_date' => $now->copy()->addMonths(6),
                'status' => 'aktif',
                'payment_status' => 'diverifikasi',
                'payment_proof' => 'subscriptions/proofs/eduplay-sub.jpg',
            ]
        );

        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client2->id, 'bank_name' => 'QRIS'],
            [
                'account_number' => 'ID1020394857121',
                'account_holder' => 'EduPlay Rental Malang',
                'qris_image' => 'client-qris/eduplay-qris.png',
                'is_active' => true,
            ]
        );
        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client2->id, 'bank_name' => 'BCA'],
            [
                'account_number' => '8271039481',
                'account_holder' => 'EduPlay Rental Malang',
                'qris_image' => null,
                'is_active' => true,
            ]
        );

        // -------------------------------------------------------------
        // STORE 3: Berkah Tenda & Pesta Malang (ID: 77)
        // -------------------------------------------------------------
        $client3 = Client::find(77) ?? Client::where('subdomain', 'berkahtenda')->first() ?? Client::where('subdomain', 'contoh-tenda-pesta')->first();
        if (! $client3) {
            $client3 = Client::create(['id' => 77, 'subdomain' => 'berkahtenda', 'status' => 'aktif']);
        }
        $client3->update([
            'subdomain' => 'berkahtenda',
            'business_name' => 'Berkah Tenda & Pesta Malang',
            'description' => 'Sewa tenda roder, plafon dekorasi, sarnafil kerucut, panggung rigging, kursi Tiffany & Futura, serta misty fan blower acara outdoor.',
            'logo' => 'tenant-logos/berkahtenda-logo.png',
            'theme_color' => '#d97706',
            'status' => 'aktif',
            'primary_admin_id' => $users['adminTenda']->id,
        ]);
        $users['adminTenda']->update(['client_id' => $client3->id]);

        TenantProfile::updateOrCreate(
            ['client_id' => $client3->id],
            [
                'name' => 'Berkah Tenda & Pesta Malang',
                'description' => 'Sewa tenda roder, plafon dekorasi, sarnafil kerucut, panggung rigging, kursi Tiffany & Futura, serta misty fan blower acara outdoor.',
                'address' => 'Jl. Terusan Sulfat No. 88, Blimbing',
                'city' => 'Kota Malang',
                'whatsapp' => '081234567892',
                'subdomain' => 'berkahtenda',
                'logo_path' => 'tenant-logos/berkahtenda-logo.png',
                'theme_color' => '#d97706',
                'hero_showcase_mode' => 'auto',
            ]
        );

        Subscription::updateOrCreate(
            ['client_id' => $client3->id, 'plan_name' => 'Professional'],
            [
                'max_products' => 100,
                'max_users' => 10,
                'price' => 499000.00,
                'billing_cycle' => 'bulanan',
                'start_date' => $now->copy()->subDays(15),
                'end_date' => $now->copy()->addMonths(5),
                'status' => 'aktif',
                'payment_status' => 'diverifikasi',
                'payment_proof' => 'subscriptions/proofs/berkahtenda-sub.jpg',
            ]
        );

        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client3->id, 'bank_name' => 'QRIS'],
            [
                'account_number' => 'ID1020394857122',
                'account_holder' => 'Berkah Tenda Malang',
                'qris_image' => 'client-qris/berkahtenda-qris.png',
                'is_active' => true,
            ]
        );
        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client3->id, 'bank_name' => 'Mandiri'],
            [
                'account_number' => '1440023456781',
                'account_holder' => 'Berkah Tenda Malang',
                'qris_image' => null,
                'is_active' => true,
            ]
        );

        // -------------------------------------------------------------
        // STORE 4: Malang Outdoor Camp & Adventure
        // -------------------------------------------------------------
        $client4 = Client::updateOrCreate(
            ['subdomain' => 'malang-outdoor'],
            [
                'business_name' => 'Malang Outdoor Camp & Adventure',
                'description' => 'Spesialis persewaan alat camping, tenda dome waterproof, tas carrier, kompor ultralight, dan perlengkapan mendaki gunung di Malang & Batu.',
                'logo' => 'tenant-logos/malangoutdoor-logo.png',
                'theme_color' => '#059669',
                'status' => 'aktif',
                'primary_admin_id' => $users['adminOutdoor']->id,
            ]
        );
        $users['adminOutdoor']->update(['client_id' => $client4->id]);

        TenantProfile::updateOrCreate(
            ['client_id' => $client4->id],
            [
                'name' => 'Malang Outdoor Camp & Adventure',
                'description' => 'Spesialis persewaan alat camping, tenda dome waterproof, tas carrier, kompor ultralight, dan perlengkapan mendaki gunung di Malang & Batu.',
                'address' => 'Jl. Bondowoso No. 18, Klojen',
                'city' => 'Kota Malang',
                'whatsapp' => '081298765431',
                'subdomain' => 'malang-outdoor',
                'logo_path' => 'tenant-logos/malangoutdoor-logo.png',
                'theme_color' => '#059669',
                'hero_showcase_mode' => 'auto',
            ]
        );

        Subscription::updateOrCreate(
            ['client_id' => $client4->id, 'plan_name' => 'Business'],
            [
                'max_products' => 50,
                'max_users' => 5,
                'price' => 249000.00,
                'billing_cycle' => 'bulanan',
                'start_date' => $now->copy()->subMonths(2),
                'end_date' => $now->copy()->addMonths(4),
                'status' => 'aktif',
                'payment_status' => 'diverifikasi',
                'payment_proof' => 'subscriptions/proofs/tf-sub-001.jpg',
            ]
        );

        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client4->id, 'bank_name' => 'QRIS'],
            [
                'account_number' => 'ID1020394857104',
                'account_holder' => 'MALANG OUTDOOR ADVENTURE',
                'qris_image' => 'client-qris/malangoutdoor-qris.png',
                'is_active' => true,
            ]
        );
        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client4->id, 'bank_name' => 'BCA'],
            [
                'account_number' => '0119882233',
                'account_holder' => 'Malang Outdoor Adventure',
                'qris_image' => null,
                'is_active' => true,
            ]
        );

        $this->seedOutdoorProducts($client4);

        // -------------------------------------------------------------
        // STORE 5: Lens & Light Studio Malang
        // -------------------------------------------------------------
        $client5 = Client::updateOrCreate(
            ['subdomain' => 'lens-light-malang'],
            [
                'business_name' => 'Lens & Light Studio Malang',
                'description' => 'Persewaan kamera mirrorless Sony/Canon, lensa prime & zoom GM, stabilizer gimbal, lighting studio, dan aksesoris videografi profesional di Malang.',
                'logo' => 'tenant-logos/lenslight-logo.png',
                'theme_color' => '#7c3aed',
                'status' => 'aktif',
                'primary_admin_id' => $users['adminKamera']->id,
            ]
        );
        $users['adminKamera']->update(['client_id' => $client5->id]);

        TenantProfile::updateOrCreate(
            ['client_id' => $client5->id],
            [
                'name' => 'Lens & Light Studio Malang',
                'description' => 'Persewaan kamera mirrorless Sony/Canon, lensa prime & zoom GM, stabilizer gimbal, lighting studio, dan aksesoris videografi profesional di Malang.',
                'address' => 'Jl. Semeru No. 72, Oro-oro Dowo',
                'city' => 'Kota Malang',
                'whatsapp' => '081298765432',
                'subdomain' => 'lens-light-malang',
                'logo_path' => 'tenant-logos/lenslight-logo.png',
                'theme_color' => '#7c3aed',
                'hero_showcase_mode' => 'auto',
            ]
        );

        Subscription::updateOrCreate(
            ['client_id' => $client5->id, 'plan_name' => 'Pro Enterprise'],
            [
                'max_products' => 100,
                'max_users' => 10,
                'price' => 349000.00,
                'billing_cycle' => 'bulanan',
                'start_date' => $now->copy()->subMonths(1),
                'end_date' => $now->copy()->addMonths(5),
                'status' => 'aktif',
                'payment_status' => 'diverifikasi',
                'payment_proof' => 'subscriptions/proofs/tf-sub-001.jpg',
            ]
        );

        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client5->id, 'bank_name' => 'QRIS'],
            [
                'account_number' => 'ID1020394857105',
                'account_holder' => 'LENS & LIGHT STUDIO MALANG',
                'qris_image' => 'client-qris/lenslight-qris.png',
                'is_active' => true,
            ]
        );
        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client5->id, 'bank_name' => 'BCA'],
            [
                'account_number' => '0117765544',
                'account_holder' => 'Lens and Light Studio',
                'qris_image' => null,
                'is_active' => true,
            ]
        );

        $this->seedKameraProducts($client5);

        // -------------------------------------------------------------
        // STORE 6: SoundMaster Audio & Instrument Malang
        // -------------------------------------------------------------
        $client6 = Client::updateOrCreate(
            ['subdomain' => 'soundmaster-malang'],
            [
                'business_name' => 'SoundMaster Audio & Instrument Malang',
                'description' => 'Solusi sewa portable PA system, speaker aktif, mic wireless panggung, keyboard synthesizer, dan alat musik band profesional untuk event di Malang.',
                'logo' => 'tenant-logos/soundmaster-logo.png',
                'theme_color' => '#e11d48',
                'status' => 'aktif',
                'primary_admin_id' => $users['adminSound']->id,
            ]
        );
        $users['adminSound']->update(['client_id' => $client6->id]);

        TenantProfile::updateOrCreate(
            ['client_id' => $client6->id],
            [
                'name' => 'SoundMaster Audio & Instrument Malang',
                'description' => 'Solusi sewa portable PA system, speaker aktif, mic wireless panggung, keyboard synthesizer, dan alat musik band profesional untuk event di Malang.',
                'address' => 'Jl. Kawi No. 34, Kauman, Klojen',
                'city' => 'Kota Malang',
                'whatsapp' => '081298765433',
                'subdomain' => 'soundmaster-malang',
                'logo_path' => 'tenant-logos/soundmaster-logo.png',
                'theme_color' => '#e11d48',
                'hero_showcase_mode' => 'auto',
            ]
        );

        Subscription::updateOrCreate(
            ['client_id' => $client6->id, 'plan_name' => 'Pro Enterprise'],
            [
                'max_products' => 100,
                'max_users' => 10,
                'price' => 349000.00,
                'billing_cycle' => 'bulanan',
                'start_date' => $now->copy()->subMonths(2),
                'end_date' => $now->copy()->addMonths(10),
                'status' => 'aktif',
                'payment_status' => 'diverifikasi',
                'payment_proof' => 'subscriptions/proofs/tf-sub-001.jpg',
            ]
        );

        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client6->id, 'bank_name' => 'QRIS'],
            [
                'account_number' => 'ID1020394857106',
                'account_holder' => 'SOUNDMASTER AUDIO MALANG',
                'qris_image' => 'client-qris/soundmaster-qris.png',
                'is_active' => true,
            ]
        );
        ClientPaymentMethod::updateOrCreate(
            ['client_id' => $client6->id, 'bank_name' => 'BCA'],
            [
                'account_number' => '0115544332',
                'account_holder' => 'SoundMaster Audio Malang',
                'qris_image' => null,
                'is_active' => true,
            ]
        );

        $this->seedSoundProducts($client6);

        return [
            'baby' => $client1,
            'mainan' => $client2,
            'tenda' => $client3,
            'outdoor' => $client4,
            'kamera' => $client5,
            'sound' => $client6,
        ];
    }

    /**
     * Seed Products and Equipment Units for Malang Outdoor.
     */
    private function seedOutdoorProducts(Client $client): void
    {
        $categories = [
            'tenda-dome-shelter' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'tenda-dome-shelter'],
                ['name' => 'Tenda Dome & Shelter', 'description' => 'Tenda kapasitas 2-6 orang waterproof badai dan flysheet pelindung.']
            ),
            'tas-carrier-daypack' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'tas-carrier-daypack'],
                ['name' => 'Tas Carrier & Daypack', 'description' => 'Ransel pendakian ergonomis dengan backsystem anti-pegal.']
            ),
            'cooking-gear-kompor' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'cooking-gear-kompor'],
                ['name' => 'Cooking Gear & Kompor', 'description' => 'Kompor windproof, nesting cooking set, dan tabung gas kaleng.']
            ),
            'tidur-camp-sleeping-bag' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'tidur-camp-sleeping-bag'],
                ['name' => 'Sleeping Bag & Matras', 'description' => 'Kantung tidur bulu angsa dan matras aluminium foil hangat.']
            ),
            'aksesoris-pendakian' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'aksesoris-pendakian'],
                ['name' => 'Aksesoris & Lampu Tenda', 'description' => 'Lampu LED tenda, headlamp, dan trekking pole pendakian.']
            ),
        ];

        $items = [
            [
                'name' => 'Tenda Dome Eiger Shira 4P Waterproof',
                'slug' => 'tenda-dome-eiger-shira-4p',
                'cat' => $categories['tenda-dome-shelter']->id,
                'desc' => 'Tenda double-layer kapasitas 4 orang dengan ketahanan air 3000mm PU, frame aluminium kokoh, dan teras vestibule luas.',
                'price' => 45000,
                'deposit' => 100000,
                'main' => 'products/outdoor-tenda-dome-main.jpg',
                'imgs' => ['products/outdoor-tenda-dome-1.jpg', 'products/outdoor-tenda-dome-2.jpg'],
                'units' => ['MOC-TND-01', 'MOC-TND-02', 'MOC-TND-03'],
            ],
            [
                'name' => 'Carrier Osprey Atmos AG 65L Premium',
                'slug' => 'carrier-osprey-atmos-ag-65l',
                'cat' => $categories['tas-carrier-daypack']->id,
                'desc' => 'Tas carrier legendaris dengan Anti-Gravity suspension system 3D mesh ventilasi maksimal dan raincover bawaan.',
                'price' => 55000,
                'deposit' => 150000,
                'main' => 'products/outdoor-carrier-osprey-main.jpg',
                'imgs' => ['products/outdoor-carrier-osprey-1.jpg', 'products/outdoor-carrier-osprey-2.jpg'],
                'units' => ['MOC-CAR-01', 'MOC-CAR-02'],
            ],
            [
                'name' => 'Set Kompor Windproof & Cooking Set DS-308',
                'slug' => 'set-kompor-windproof-cooking-set',
                'cat' => $categories['cooking-gear-kompor']->id,
                'desc' => 'Paket masak lengkap 3-4 orang: panci teflon anti lengket, teko air 0.8L, wajan mini, mangkok, dan kompor gas mawar penahan angin.',
                'price' => 25000,
                'deposit' => 50000,
                'main' => 'products/outdoor-cooking-set-main.jpg',
                'imgs' => ['products/outdoor-cooking-set-1.jpg', 'products/outdoor-cooking-set-2.jpg'],
                'units' => ['MOC-CKG-01', 'MOC-CKG-02'],
            ],
            [
                'name' => 'Sleeping Bag Bulu Angsa Eiger Down 800',
                'slug' => 'sleeping-bag-eiger-down-800',
                'cat' => $categories['tidur-camp-sleeping-bag']->id,
                'desc' => 'Kantung tidur ultra hangat suhu limit 0 derajat Celcius dengan isian bulu angsa alami, ringan dan sangat compact saat dipacking.',
                'price' => 30000,
                'deposit' => 75000,
                'main' => 'products/outdoor-sleeping-bag-main.jpg',
                'imgs' => ['products/outdoor-sleeping-bag-1.jpg', 'products/outdoor-sleeping-bag-2.jpg'],
                'units' => ['MOC-SLP-01', 'MOC-SLP-02'],
            ],
            [
                'name' => 'Lampu Tenda LED Solar Rechargeable & Powerbank',
                'slug' => 'lampu-tenda-led-solar-rechargeable',
                'cat' => $categories['aksesoris-pendakian']->id,
                'desc' => 'Lampu tenda gantung 300 lumens dengan 3 mode pencahayaan, solar panel darurat, dan baterai 4400mAh berfungsi sebagai powerbank.',
                'price' => 15000,
                'deposit' => 30000,
                'main' => 'products/outdoor-lampu-tenda-main.jpg',
                'imgs' => ['products/outdoor-lampu-tenda-1.jpg', 'products/outdoor-lampu-tenda-2.jpg'],
                'units' => ['MOC-LMP-01', 'MOC-LMP-02'],
            ],
            [
                'name' => 'Trekking Pole Antishock Naturehike Carbon',
                'slug' => 'trekking-pole-naturehike-carbon',
                'cat' => $categories['aksesoris-pendakian']->id,
                'desc' => 'Sepasang tongkat pendakian berbahan carbon fiber super ringan dengan peredam kejut antishock dan grip busa EVA anti selip.',
                'price' => 20000,
                'deposit' => 40000,
                'main' => 'products/outdoor-trekking-pole-main.jpg',
                'imgs' => ['products/outdoor-trekking-pole-1.jpg', 'products/outdoor-trekking-pole-2.jpg'],
                'units' => ['MOC-TRK-01', 'MOC-TRK-02'],
            ],
        ];

        $this->persistProductsAndUnits($client, $items);
    }

    /**
     * Seed Products and Equipment Units for Lens & Light Studio.
     */
    private function seedKameraProducts(Client $client): void
    {
        $categories = [
            'kamera-mirrorless-cinema' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'kamera-mirrorless-cinema'],
                ['name' => 'Kamera Mirrorless & Cinema', 'description' => 'Kamera full-frame resolusi tinggi dan perekaman video 4K 10-bit 4:2:2.']
            ),
            'lensa-profesional-gm' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'lensa-profesional-gm'],
                ['name' => 'Lensa Profesional & Cinema', 'description' => 'Lensa zoom dan prime aperture konstan F2.8 dan F1.4 ketajaman maksimal.']
            ),
            'stabilizer-gimbal' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'stabilizer-gimbal'],
                ['name' => 'Stabilizer Gimbal & Rig', 'description' => 'Gimbal 3-axis bermotor untuk pergerakan video halus tanpa getaran.']
            ),
            'lighting-studio-softbox' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'lighting-studio-softbox'],
                ['name' => 'Lighting Studio & Softbox', 'description' => 'Lampu LED continuous daylight dan modifier cahaya octabox.']
            ),
            'audio-recording-mic' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'audio-recording-mic'],
                ['name' => 'Audio & Wireless Mic', 'description' => 'Mic nirkabel dual channel dengan peredam bising ENC onboard recording.']
            ),
        ];

        $items = [
            [
                'name' => 'Sony Alpha 7 IV Full-Frame Mirrorless Body',
                'slug' => 'sony-alpha-7-iv-mirrorless',
                'cat' => $categories['kamera-mirrorless-cinema']->id,
                'desc' => 'Sensor BSI 33MP Full-Frame, video 4K 60p 10-bit 4:2:2, autofocus real-time eye tracking manusia & hewan, 2 baterai NP-FZ100.',
                'price' => 350000,
                'deposit' => 1000000,
                'main' => 'products/kamera-sony-a7iv-main.jpg',
                'imgs' => ['products/kamera-sony-a7iv-1.jpg', 'products/kamera-sony-a7iv-2.jpg'],
                'units' => ['LLS-CAM-01', 'LLS-CAM-02'],
            ],
            [
                'name' => 'Lensa Sony FE 24-70mm F2.8 GM II',
                'slug' => 'lensa-sony-fe-24-70mm-f28-gm-ii',
                'cat' => $categories['lensa-profesional-gm']->id,
                'desc' => 'Lensa standar zoom seri G Master generasi kedua yang 22% lebih ringan dengan ketajaman optik sudut ke sudut dan bokeh bulat halus.',
                'price' => 250000,
                'deposit' => 800000,
                'main' => 'products/kamera-lensa-24-70-main.jpg',
                'imgs' => ['products/kamera-lensa-24-70-1.jpg', 'products/kamera-lensa-24-70-2.jpg'],
                'units' => ['LLS-LNS-01', 'LLS-LNS-02'],
            ],
            [
                'name' => 'Gimbal Stabilizer DJI RS 3 Pro Combo',
                'slug' => 'gimbal-stabilizer-dji-rs-3-pro-combo',
                'cat' => $categories['stabilizer-gimbal']->id,
                'desc' => 'Stabilizer profesional serat karbon payload hingga 4.5kg, kunci sumbu otomatis, motor fokus LiDAR, dan pemancar video transmisi.',
                'price' => 200000,
                'deposit' => 500000,
                'main' => 'products/kamera-gimbal-dji-main.jpg',
                'imgs' => ['products/kamera-gimbal-dji-1.jpg', 'products/kamera-gimbal-dji-2.jpg'],
                'units' => ['LLS-GMB-01', 'LLS-GMB-02'],
            ],
            [
                'name' => 'Continuous Light Godox SL60W + Softbox Octa 95cm',
                'slug' => 'continuous-light-godox-sl60w-octa',
                'cat' => $categories['lighting-studio-softbox']->id,
                'desc' => 'Lampu studio LED 60 Watt daylight 5600K CRI 95+ dengan Bowens mount, diffuser kain ganda grid honeycomb, dan light stand 2.8m kokoh.',
                'price' => 120000,
                'deposit' => 300000,
                'main' => 'products/kamera-godox-sl60w-main.jpg',
                'imgs' => ['products/kamera-godox-sl60w-1.jpg', 'products/kamera-godox-sl60w-2.jpg'],
                'units' => ['LLS-LGT-01', 'LLS-LGT-02'],
            ],
            [
                'name' => 'Wireless Mic Hollyland Lark Max Duo',
                'slug' => 'wireless-mic-hollyland-lark-max-duo',
                'cat' => $categories['audio-recording-mic']->id,
                'desc' => 'Sistem mikrofon nirkabel 2 transmitter + 1 receiver dengan internal recording 14 jam per transmitter, Environmental Noise Cancellation.',
                'price' => 100000,
                'deposit' => 250000,
                'main' => 'products/kamera-hollyland-mic-main.jpg',
                'imgs' => ['products/kamera-hollyland-mic-1.jpg', 'products/kamera-hollyland-mic-2.jpg'],
                'units' => ['LLS-MIC-01', 'LLS-MIC-02'],
            ],
            [
                'name' => 'Monitor Eksternal Feelworld LUT7 7 Inch 4K HDMI',
                'slug' => 'monitor-feelworld-lut7-4k-hdmi',
                'cat' => $categories['kamera-mirrorless-cinema']->id,
                'desc' => 'Layar sentuh monitor kamera ultra terang 2200 nits terlihat jelas di bawah sinar matahari langsung, mendukung 3D LUT kustom dan waveform.',
                'price' => 90000,
                'deposit' => 200000,
                'main' => 'products/kamera-monitor-feelworld-main.jpg',
                'imgs' => ['products/kamera-monitor-feelworld-1.jpg', 'products/kamera-monitor-feelworld-2.jpg'],
                'units' => ['LLS-MON-01', 'LLS-MON-02'],
            ],
        ];

        $this->persistProductsAndUnits($client, $items);
    }

    /**
     * Seed Products and Equipment Units for SoundMaster Audio.
     */
    private function seedSoundProducts(Client $client): void
    {
        $categories = [
            'portable-pa-sound-system' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'portable-pa-sound-system'],
                ['name' => 'Portable PA & Sound System', 'description' => 'Sound system set lengkap dengan mixer terintegrasi dan koneksi Bluetooth.']
            ),
            'wireless-microphone-vokal' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'wireless-microphone-vokal'],
                ['name' => 'Wireless Microphone & Vokal', 'description' => 'Mikrofon genggam panggung UHF jangkauan luas tanpa putus suara.']
            ),
            'alat-musik-gitar-bass' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'alat-musik-gitar-bass'],
                ['name' => 'Alat Musik Akustik & Elektrik', 'description' => 'Gitar akustik preamp pickup panggung, bass, dan aksesoris hardcase.']
            ),
            'keyboard-synthesizer' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'keyboard-synthesizer'],
                ['name' => 'Keyboard & Synthesizer', 'description' => 'Keyboard workstation 61-tuts dengan soundbank instrumen nusantara & pop.']
            ),
            'mixer-audio-monitor' => Category::updateOrCreate(
                ['client_id' => $client->id, 'slug' => 'mixer-audio-monitor'],
                ['name' => 'Mixer Audio & Speaker Monitor', 'description' => 'Mixer multi-channel USB interface dan speaker panggung bertenaga tinggi.']
            ),
        ];

        $items = [
            [
                'name' => 'Yamaha Stagepas 600BT Portable PA System',
                'slug' => 'yamaha-stagepas-600bt-pa-system',
                'cat' => $categories['portable-pa-sound-system']->id,
                'desc' => 'Sistem sound portable 680 Watt terdiri dari sepasang speaker 10 inci, mixer bertenaga 10 channel yang dapat dilepas, Bluetooth audio, dan reverb SPX Yamaha.',
                'price' => 350000,
                'deposit' => 750000,
                'main' => 'products/audio-yamaha-stagepas-main.jpg',
                'imgs' => ['products/audio-yamaha-stagepas-1.jpg', 'products/audio-yamaha-stagepas-2.jpg'],
                'units' => ['SMA-PAS-01', 'SMA-PAS-02'],
            ],
            [
                'name' => 'Wireless Mic Sennheiser EW 100 G4 Dual Handheld',
                'slug' => 'sennheiser-ew-100-g4-dual-handheld',
                'cat' => $categories['wireless-microphone-vokal']->id,
                'desc' => 'Mikrofon nirkabel vokal standar panggung internasional dengan modul kapsul e835 dinamis cardioid, transmisi UHF stabil jangkauan 100 meter.',
                'price' => 175000,
                'deposit' => 400000,
                'main' => 'products/audio-sennheiser-mic-main.jpg',
                'imgs' => ['products/audio-sennheiser-mic-1.jpg', 'products/audio-sennheiser-mic-2.jpg'],
                'units' => ['SMA-MIC-01', 'SMA-MIC-02'],
            ],
            [
                'name' => 'Gitar Akustik Elektrik Yamaha FX310A + Hardcase',
                'slug' => 'gitar-akustik-elektrik-yamaha-fx310a',
                'cat' => $categories['alat-musik-gitar-bass']->id,
                'desc' => 'Gitar bodi dreadnought bersuara resonan merdu, dilengkapi pickup undersaddle piezo, preamp aktif dengan kontrol tone, dan hardcase fiber tahan banting.',
                'price' => 75000,
                'deposit' => 200000,
                'main' => 'products/audio-gitar-yamaha-main.jpg',
                'imgs' => ['products/audio-gitar-yamaha-1.jpg', 'products/audio-gitar-yamaha-2.jpg'],
                'units' => ['SMA-GTR-01', 'SMA-GTR-02'],
            ],
            [
                'name' => 'Synthesizer Keyboard Roland XPS-10 Expandable',
                'slug' => 'synthesizer-keyboard-roland-xps-10',
                'cat' => $categories['keyboard-synthesizer']->id,
                'desc' => 'Keyboard pro 61 tuts ringan dengan lebih dari 1500 tone Roland legendaris, termasuk koleksi suara etnik Indonesia, piano akustik, string, dan brass.',
                'price' => 200000,
                'deposit' => 500000,
                'main' => 'products/audio-keyboard-roland-main.jpg',
                'imgs' => ['products/audio-keyboard-roland-1.jpg', 'products/audio-keyboard-roland-2.jpg'],
                'units' => ['SMA-KBD-01', 'SMA-KBD-02'],
            ],
            [
                'name' => 'Behringer Xenyx QX1202USB Mixer 12 Channel',
                'slug' => 'behringer-xenyx-qx1202usb-mixer',
                'cat' => $categories['mixer-audio-monitor']->id,
                'desc' => 'Mixer audio analog 12 input dengan 4 preamp mikrofon XENYX berkualitas studio, kompresor satu tombol, prosesor multi-efek KLARK TEKNIK, dan audio interface USB.',
                'price' => 125000,
                'deposit' => 300000,
                'main' => 'products/audio-mixer-behringer-main.jpg',
                'imgs' => ['products/audio-mixer-behringer-1.jpg', 'products/audio-mixer-behringer-2.jpg'],
                'units' => ['SMA-MIX-01', 'SMA-MIX-02'],
            ],
            [
                'name' => 'Speaker Monitor Aktif Baretone MAX15RC 15 Inch 500W',
                'slug' => 'speaker-monitor-aktif-baretone-max15rc',
                'cat' => $categories['mixer-audio-monitor']->id,
                'desc' => 'Speaker aktif bertenaga 500 Watt RMS woofer 15 inci dengan suara bass punchy dan vokal jernih, input mikrofon & line terpisah, cocok untuk floor monitor atau PA ruangan.',
                'price' => 150000,
                'deposit' => 350000,
                'main' => 'products/audio-speaker-baretone-main.jpg',
                'imgs' => ['products/audio-speaker-baretone-1.jpg', 'products/audio-speaker-baretone-2.jpg'],
                'units' => ['SMA-SPK-01', 'SMA-SPK-02'],
            ],
        ];

        $this->persistProductsAndUnits($client, $items);
    }

    /**
     * Helper to persist products and equipment units.
     */
    private function persistProductsAndUnits(Client $client, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::updateOrCreate(
                ['client_id' => $client->id, 'slug' => $item['slug']],
                [
                    'category_id' => $item['cat'],
                    'name' => $item['name'],
                    'description' => $item['desc'],
                    'rental_price_per_day' => $item['price'],
                    'deposit_fee' => $item['deposit'],
                    'main_image' => $item['main'],
                    'images' => json_encode($item['imgs']),
                    'status' => 'aktif',
                ]
            );

            foreach ($item['units'] as $sn) {
                EquipmentUnit::updateOrCreate(
                    ['client_id' => $client->id, 'unit_code' => $sn],
                    [
                        'product_id' => $product->id,
                        'serial_number' => $sn,
                        'status' => 'tersedia',
                        'condition' => 'baik',
                    ]
                );
            }
        }
    }

    /**
     * 3. Seed Owner Platform demonstration records (Pending, Rejected, Suspended, Expiring).
     */
    private function seedOwnerPlatformSamples(): void
    {
        $now = Carbon::now();

        // 1. Pending Tenant Registration (Menunggu Verifikasi)
        ClientRegistration::updateOrCreate(
            ['subdomain' => 'dronefly-malang'],
            [
                'business_name' => 'DroneFly Studio Malang',
                'description' => 'Rental drone DJI Mavic & FPV untuk pemetaan dan sinematografi di Jawa Timur.',
                'plan_name' => 'Professional',
                'admin_name' => 'Rian Dirgantara',
                'admin_email' => 'rian@dronefly.id',
                'admin_phone' => '081234567899',
                'admin_password' => Hash::make('password123'),
                'status' => 'menunggu_verifikasi',
                'payment_status' => 'menunggu',
                'payment_proof' => 'subscriptions/proofs/tf-sub-001.jpg',
                'created_at' => $now->copy()->subHours(3),
            ]
        );

        // 2. Rejected Tenant Registration (Ditolak dengan alasan)
        ClientRegistration::updateOrCreate(
            ['subdomain' => 'playstation-rental-batu'],
            [
                'business_name' => 'Rental PlayStation Batu',
                'description' => 'Persewaan konsol PS5 dan game disk di Kota Batu.',
                'plan_name' => 'Starter',
                'admin_name' => 'Doni Gamer',
                'admin_email' => 'doni@gamerbatu.test',
                'admin_phone' => '081234567888',
                'admin_password' => Hash::make('password123'),
                'status' => 'ditolak',
                'payment_status' => 'ditolak',
                'payment_proof' => 'subscriptions/proofs/tf-sub-001.jpg',
                'rejection_reason' => 'Bukti transfer tidak terbaca dan nominal transfer tidak sesuai paket Starter.',
                'reviewed_at' => $now->copy()->subDays(2),
            ]
        );

        // 3. Suspended Client (Toko Disuspend untuk demo Superadmin)
        $suspendedClient = Client::updateOrCreate(
            ['subdomain' => 'sepeda-listrik-malang'],
            [
                'business_name' => 'Rental Sepeda Listrik Malang Eco',
                'description' => 'Penyewaan e-bike & skuter listrik keliling kota Malang.',
                'status' => 'suspended',
                'theme_color' => '#64748b',
                'created_at' => $now->copy()->subMonths(3),
            ]
        );
        Subscription::updateOrCreate(
            ['client_id' => $suspendedClient->id, 'plan_name' => 'Basic'],
            [
                'max_products' => 30,
                'max_users' => 2,
                'price' => 149000.00,
                'billing_cycle' => 'bulanan',
                'start_date' => $now->copy()->subMonths(2),
                'end_date' => $now->copy()->addMonths(1),
                'status' => 'aktif',
                'payment_status' => 'diverifikasi',
            ]
        );

        // 4. Inactive Client (Toko Nonaktif / Subscription Kadaluarsa)
        $inactiveClient = Client::updateOrCreate(
            ['subdomain' => 'karangploso-genset'],
            [
                'business_name' => 'Genset & Power Rental Karangploso',
                'description' => 'Sewa genset silent 5kVA - 50kVA untuk proyek.',
                'status' => 'nonaktif',
                'theme_color' => '#475569',
                'created_at' => $now->copy()->subMonths(6),
            ]
        );
        Subscription::updateOrCreate(
            ['client_id' => $inactiveClient->id, 'plan_name' => 'Starter'],
            [
                'max_products' => 20,
                'max_users' => 1,
                'price' => 99000.00,
                'billing_cycle' => 'bulanan',
                'start_date' => $now->copy()->subMonths(4),
                'end_date' => $now->copy()->subDays(10), // Sudah lewat = kadaluarsa
                'status' => 'kadaluarsa',
                'payment_status' => 'diverifikasi',
            ]
        );

        // 5. Expiring Soon Subscription Sample (Sisa 5 hari untuk dashboard alert Superadmin)
        Subscription::updateOrCreate(
            ['client_id' => $suspendedClient->id, 'plan_name' => 'Basic (Simulasi Expiring)'],
            [
                'max_products' => 30,
                'max_users' => 2,
                'price' => 149000.00,
                'billing_cycle' => 'bulanan',
                'start_date' => $now->copy()->subDays(25),
                'end_date' => $now->copy()->addDays(5), // Sisa 5 hari! Masuk tab "Akan Berakhir"
                'status' => 'aktif',
                'payment_status' => 'diverifikasi',
            ]
        );
    }

    /**
     * 4. Seed Balanced, Realistic Order Lifecycle Scenarios across ALL 6 stores.
     *
     * Total Orders: exactly 60 clean orders (10 scenarios per store, exactly 15 orders per customer).
     * Order numbers: ORD-202611-0101 to ORD-202611-0610.
     */
    private function seedBalancedOrdersForAllStores(array $users, array $stores): void
    {
        $now = Carbon::now();

        // 1. Purge any existing orders to guarantee a 100% clean state
        $existingOrderIds = Order::pluck('id');
        if ($existingOrderIds->isNotEmpty()) {
            $orderItemIds = OrderItem::whereIn('order_id', $existingOrderIds)->pluck('id');
            OrderItemUnit::whereIn('order_item_id', $orderItemIds)->delete();
            OrderItem::whereIn('id', $orderItemIds)->delete();

            $damageReportIds = DamageReport::whereIn('order_id', $existingOrderIds)->pluck('id');
            DamageCase::whereIn('damage_report_id', $damageReportIds)->delete();
            DamageReport::whereIn('id', $damageReportIds)->delete();

            ReturnModel::whereIn('order_id', $existingOrderIds)->delete();
            IdentityGuarantee::whereIn('order_id', $existingOrderIds)->delete();
            Payment::whereIn('order_id', $existingOrderIds)->delete();
            Shipment::whereIn('order_id', $existingOrderIds)->delete();
            Order::whereIn('id', $existingOrderIds)->delete();
        }

        // Reset all equipment units to 'tersedia' and 'baik'
        EquipmentUnit::query()->update(['status' => 'tersedia', 'condition' => 'baik']);

        $customers = [
            $users['customer1'], // Tersiqo
            $users['customer2'], // Budi
            $users['customer3'], // Rizky
            $users['customer4'], // Siti
        ];

        $niks = [
            $users['customer1']->id => '3573011203990001',
            $users['customer2']->id => '3573021904920003',
            $users['customer3']->id => '3573010705910002',
            $users['customer4']->id => '3573032508950004',
        ];

        // Store configurations (Index 1 to 6)
        $storeList = [
            ['client' => $stores['baby'], 'admin' => $users['adminBaby'], 'num' => 1],
            ['client' => $stores['mainan'], 'admin' => $users['adminMainan'], 'num' => 2],
            ['client' => $stores['tenda'], 'admin' => $users['adminTenda'], 'num' => 3],
            ['client' => $stores['outdoor'], 'admin' => $users['adminOutdoor'], 'num' => 4],
            ['client' => $stores['kamera'], 'admin' => $users['adminKamera'], 'num' => 5],
            ['client' => $stores['sound'], 'admin' => $users['adminSound'], 'num' => 6],
        ];

        // Customer allocation grid (store s in 0..5, scenario sc in 0..9)
        // Ensures exactly 15 orders per customer and at least 1 order per scenario per customer
        $grid = [
            [0, 3, 2, 3, 0, 1, 2, 3, 0, 1], // Store 1 (Baby Rental)
            [1, 2, 3, 0, 1, 2, 3, 0, 1, 2], // Store 2 (EduPlay)
            [2, 3, 0, 1, 2, 3, 0, 1, 2, 3], // Store 3 (Berkah Tenda)
            [3, 0, 1, 2, 3, 0, 1, 2, 3, 0], // Store 4 (Malang Outdoor)
            [0, 1, 2, 3, 0, 1, 2, 3, 0, 1], // Store 5 (Lens & Light)
            [1, 2, 3, 0, 1, 2, 3, 0, 1, 2], // Store 6 (SoundMaster)
        ];

        foreach ($storeList as $s => $cfg) {
            $client = $cfg['client'];
            $admin = $cfg['admin'];
            $storeNum = $cfg['num'];

            $products = Product::where('client_id', $client->id)
                ->where('status', 'aktif')
                ->where('name', 'NOT LIKE', '[DATA CONTOH]%')
                ->orderBy('id')
                ->get();

            if ($products->isEmpty()) {
                continue;
            }

            $payMethod = ClientPaymentMethod::where('client_id', $client->id)->where('is_active', true)->first();

            for ($sc = 0; $sc < 10; $sc++) {
                $orderNumber = sprintf('ORD-202611-0%d%02d', $storeNum, $sc + 1);
                $cust = $customers[$grid[$s][$sc]];
                $product = $products[$sc % $products->count()];
                $units = EquipmentUnit::where('client_id', $client->id)->where('product_id', $product->id)->get();
                $unit = $units->first();

                switch ($sc) {
                    case 0:
                        // Skenario 1: Menunggu Pembayaran (< 2 jam)
                        $order = Order::create([
                            'order_number' => $orderNumber,
                            'client_id' => $client->id,
                            'user_id' => $cust->id,
                            'rental_start_date' => $now->copy()->addDays(2),
                            'rental_end_date' => $now->copy()->addDays(5),
                            'duration_days' => 3,
                            'total_amount' => $product->rental_price_per_day * 3,
                            'shipping_method' => 'pickup',
                            'shipping_address' => 'Ambil Sendiri di Toko ' . $client->business_name,
                            'status' => 'menunggu_pembayaran',
                            'created_at' => $now->copy()->subMinutes(25),
                            'updated_at' => $now->copy()->subMinutes(25),
                        ]);
                        $this->attachItem($order, $product, 1, $order->total_amount);
                        break;

                    case 1:
                        // Skenario 2: Menunggu Verifikasi (Transfer & KTP uploaded)
                        $order = Order::create([
                            'order_number' => $orderNumber,
                            'client_id' => $client->id,
                            'user_id' => $cust->id,
                            'rental_start_date' => $now->copy()->addDays(1),
                            'rental_end_date' => $now->copy()->addDays(3),
                            'duration_days' => 2,
                            'total_amount' => $product->rental_price_per_day * 2,
                            'shipping_method' => 'delivery',
                            'shipping_address' => $cust->address,
                            'status' => 'menunggu_verifikasi',
                            'created_at' => $now->copy()->subHours(1),
                            'updated_at' => $now->copy()->subHours(1),
                        ]);
                        $this->attachItem($order, $product, 1, $order->total_amount);
                        Payment::create([
                            'order_id' => $order->id,
                            'client_payment_method_id' => $payMethod?->id,
                            'amount' => $order->total_amount,
                            'payment_proof' => 'payments/bukti-transfer-contoh.jpg',
                            'status' => 'menunggu_konfirmasi',
                            'paid_at' => $now->copy()->subMinutes(50),
                        ]);
                        IdentityGuarantee::create([
                            'order_id' => $order->id,
                            'customer_id' => $cust->id,
                            'full_name' => $cust->name,
                            'identity_number' => $niks[$cust->id],
                            'identity_document_image' => 'identity_guarantees/ktp-contoh.jpg',
                            'face_image' => 'identity_guarantees/selfie-contoh.jpg',
                            'identity_address' => $cust->address,
                            'status' => 'menunggu',
                        ]);
                        break;

                    case 2:
                        // Skenario 3: Siap Kirim (Verifikasi lolos, antrean alokasi unit fisik)
                        $order = Order::create([
                            'order_number' => $orderNumber,
                            'client_id' => $client->id,
                            'user_id' => $cust->id,
                            'rental_start_date' => $now->copy()->addDays(1),
                            'rental_end_date' => $now->copy()->addDays(4),
                            'duration_days' => 3,
                            'total_amount' => $product->rental_price_per_day * 3,
                            'shipping_method' => 'delivery',
                            'shipping_address' => $cust->address,
                            'status' => 'siap_kirim',
                            'created_at' => $now->copy()->subHours(4),
                            'updated_at' => $now->copy()->subHours(3),
                        ]);
                        $this->attachItem($order, $product, 1, $order->total_amount);
                        Payment::create([
                            'order_id' => $order->id,
                            'client_payment_method_id' => $payMethod?->id,
                            'amount' => $order->total_amount,
                            'payment_proof' => 'payments/bukti-transfer-contoh.jpg',
                            'status' => 'diverifikasi',
                            'paid_at' => $now->copy()->subHours(4),
                            'verified_at' => $now->copy()->subHours(3),
                        ]);
                        IdentityGuarantee::create([
                            'order_id' => $order->id,
                            'customer_id' => $cust->id,
                            'full_name' => $cust->name,
                            'identity_number' => $niks[$cust->id],
                            'identity_document_image' => 'identity_guarantees/ktp-contoh.jpg',
                            'face_image' => 'identity_guarantees/selfie-contoh.jpg',
                            'identity_address' => $cust->address,
                            'status' => 'diverifikasi',
                        ]);
                        break;

                    case 3:
                        // Skenario 4: Dikirim / Dalam Perjalanan Kurir
                        $order = Order::create([
                            'order_number' => $orderNumber,
                            'client_id' => $client->id,
                            'user_id' => $cust->id,
                            'rental_start_date' => $now->copy()->startOfDay(),
                            'rental_end_date' => $now->copy()->addDays(3),
                            'duration_days' => 3,
                            'total_amount' => $product->rental_price_per_day * 3,
                            'shipping_method' => 'delivery',
                            'shipping_address' => $cust->address,
                            'status' => 'dikirim',
                            'created_at' => $now->copy()->subHours(5),
                            'updated_at' => $now->copy()->subHours(1),
                        ]);
                        $item = $this->attachItem($order, $product, 1, $order->total_amount);
                        Payment::create([
                            'order_id' => $order->id,
                            'client_payment_method_id' => $payMethod?->id,
                            'amount' => $order->total_amount,
                            'payment_proof' => 'payments/bukti-transfer-contoh.jpg',
                            'status' => 'diverifikasi',
                            'paid_at' => $now->copy()->subHours(5),
                            'verified_at' => $now->copy()->subHours(4),
                        ]);
                        IdentityGuarantee::create([
                            'order_id' => $order->id,
                            'customer_id' => $cust->id,
                            'full_name' => $cust->name,
                            'identity_number' => $niks[$cust->id],
                            'identity_document_image' => 'identity_guarantees/ktp-contoh.jpg',
                            'face_image' => 'identity_guarantees/selfie-contoh.jpg',
                            'identity_address' => $cust->address,
                            'status' => 'diverifikasi',
                        ]);
                        if ($unit) {
                            $unit->update(['status' => 'disewa']);
                            OrderItemUnit::create([
                                'order_item_id' => $item->id,
                                'equipment_unit_id' => $unit->id,
                            ]);
                        }
                        Shipment::create([
                            'order_id' => $order->id,
                            'shipping_method' => 'delivery',
                            'delivery_address' => $cust->address,
                            'courier_name' => 'Kurir Toko ' . $client->business_name,
                            'tracking_number' => sprintf('RB-EXP-%02d%02d', $storeNum, $sc + 1),
                            'shipped_at' => $now->copy()->subHours(1),
                            'status' => 'dikirim',
                        ]);
                        break;

                    case 4:
                        // Skenario 5: Sedang Disewa Normal (Sisa 2 Hari)
                        $order = Order::create([
                            'order_number' => $orderNumber,
                            'client_id' => $client->id,
                            'user_id' => $cust->id,
                            'rental_start_date' => $now->copy()->subDays(1),
                            'rental_end_date' => $now->copy()->addDays(2),
                            'duration_days' => 3,
                            'total_amount' => $product->rental_price_per_day * 3,
                            'shipping_method' => 'pickup',
                            'shipping_address' => 'Ambil Sendiri di Toko ' . $client->business_name,
                            'status' => 'sedang_disewa',
                            'created_at' => $now->copy()->subDays(2),
                            'updated_at' => $now->copy()->subDays(1),
                        ]);
                        $item = $this->attachItem($order, $product, 1, $order->total_amount);
                        Payment::create([
                            'order_id' => $order->id,
                            'client_payment_method_id' => $payMethod?->id,
                            'amount' => $order->total_amount,
                            'payment_proof' => 'payments/bukti-transfer-contoh.jpg',
                            'status' => 'diverifikasi',
                            'paid_at' => $now->copy()->subDays(2),
                            'verified_at' => $now->copy()->subDays(2),
                        ]);
                        IdentityGuarantee::create([
                            'order_id' => $order->id,
                            'customer_id' => $cust->id,
                            'full_name' => $cust->name,
                            'identity_number' => $niks[$cust->id],
                            'identity_document_image' => 'identity_guarantees/ktp-contoh.jpg',
                            'face_image' => 'identity_guarantees/selfie-contoh.jpg',
                            'identity_address' => $cust->address,
                            'status' => 'dititipkan',
                            'deposited_at' => $now->copy()->subDays(1),
                        ]);
                        if ($unit) {
                            $unit->update(['status' => 'disewa']);
                            OrderItemUnit::create([
                                'order_item_id' => $item->id,
                                'equipment_unit_id' => $unit->id,
                            ]);
                        }
                        break;

                    case 5:
                        // Skenario 6: Dikembalikan (Menunggu Inspeksi Fisik)
                        $order = Order::create([
                            'order_number' => $orderNumber,
                            'client_id' => $client->id,
                            'user_id' => $cust->id,
                            'rental_start_date' => $now->copy()->subDays(3),
                            'rental_end_date' => $now->copy()->subHours(1),
                            'duration_days' => 3,
                            'total_amount' => $product->rental_price_per_day * 3,
                            'shipping_method' => 'pickup',
                            'shipping_address' => 'Diantar ke Toko ' . $client->business_name,
                            'status' => 'dikembalikan',
                            'created_at' => $now->copy()->subDays(4),
                            'updated_at' => $now->copy()->subHours(1),
                        ]);
                        $item = $this->attachItem($order, $product, 1, $order->total_amount);
                        Payment::create([
                            'order_id' => $order->id,
                            'client_payment_method_id' => $payMethod?->id,
                            'amount' => $order->total_amount,
                            'payment_proof' => 'payments/bukti-transfer-contoh.jpg',
                            'status' => 'diverifikasi',
                            'paid_at' => $now->copy()->subDays(4),
                        ]);
                        IdentityGuarantee::create([
                            'order_id' => $order->id,
                            'customer_id' => $cust->id,
                            'full_name' => $cust->name,
                            'identity_number' => $niks[$cust->id],
                            'identity_document_image' => 'identity_guarantees/ktp-contoh.jpg',
                            'face_image' => 'identity_guarantees/selfie-contoh.jpg',
                            'identity_address' => $cust->address,
                            'status' => 'dititipkan',
                            'deposited_at' => $now->copy()->subDays(3),
                        ]);
                        ReturnModel::create([
                            'order_id' => $order->id,
                            'return_method' => 'pickup',
                            'return_date' => $now->copy()->subHours(1),
                            'return_status' => 'diterima',
                            'late_hours' => 0,
                            'calculated_late_fee' => 0,
                            'late_fee_amount' => 0,
                            'late_fee_status' => 'tidak_ada',
                            'notes' => 'Barang tiba di toko, staf sedang melakukan inspeksi fisik & kelengkapan unit.',
                        ]);
                        if ($unit) {
                            $unit->update(['status' => 'tersedia']);
                            OrderItemUnit::create([
                                'order_item_id' => $item->id,
                                'equipment_unit_id' => $unit->id,
                            ]);
                        }
                        break;

                    case 6:
                        // Skenario 7: Menunggu Pembayaran Denda Telat (KTP Ditahan)
                        $order = Order::create([
                            'order_number' => $orderNumber,
                            'client_id' => $client->id,
                            'user_id' => $cust->id,
                            'rental_start_date' => $now->copy()->subDays(5),
                            'rental_end_date' => $now->copy()->subDays(1),
                            'duration_days' => 4,
                            'total_amount' => $product->rental_price_per_day * 4,
                            'shipping_method' => 'pickup',
                            'shipping_address' => 'Ambil Sendiri di Toko ' . $client->business_name,
                            'status' => 'menunggu_pembayaran_denda',
                            'created_at' => $now->copy()->subDays(6),
                            'updated_at' => $now->copy()->subHours(3),
                        ]);
                        $item = $this->attachItem($order, $product, 1, $order->total_amount);
                        Payment::create([
                            'order_id' => $order->id,
                            'client_payment_method_id' => $payMethod?->id,
                            'amount' => $order->total_amount,
                            'payment_proof' => 'payments/bukti-transfer-contoh.jpg',
                            'status' => 'diverifikasi',
                            'paid_at' => $now->copy()->subDays(6),
                        ]);
                        ReturnModel::create([
                            'order_id' => $order->id,
                            'return_method' => 'pickup',
                            'return_date' => $now->copy()->subHours(3),
                            'return_status' => 'diterima',
                            'late_hours' => 24,
                            'calculated_late_fee' => 50000.00,
                            'late_fee_amount' => 50000.00,
                            'late_fee_status' => 'menunggu_pembayaran',
                            'notes' => 'Pengembalian terlambat 24 jam. Tagihan denda Rp 50.000 diterbitkan.',
                            'processed_by' => $admin->id,
                        ]);
                        IdentityGuarantee::create([
                            'order_id' => $order->id,
                            'customer_id' => $cust->id,
                            'full_name' => $cust->name,
                            'identity_number' => $niks[$cust->id],
                            'identity_document_image' => 'identity_guarantees/ktp-contoh.jpg',
                            'face_image' => 'identity_guarantees/selfie-contoh.jpg',
                            'identity_address' => $cust->address,
                            'status' => 'dititipkan',
                            'deposited_at' => $now->copy()->subDays(5),
                            'reviewed_by' => $admin->id,
                        ]);
                        if ($unit) {
                            $unit->update(['status' => 'tersedia']);
                            OrderItemUnit::create([
                                'order_item_id' => $item->id,
                                'equipment_unit_id' => $unit->id,
                            ]);
                        }
                        break;

                    case 7:
                        // Skenario 8: Damage Case / Klaim Kerusakan (KTP Ditahan)
                        $order = Order::create([
                            'order_number' => $orderNumber,
                            'client_id' => $client->id,
                            'user_id' => $cust->id,
                            'rental_start_date' => $now->copy()->subDays(4),
                            'rental_end_date' => $now->copy()->subDays(1),
                            'duration_days' => 3,
                            'total_amount' => $product->rental_price_per_day * 3,
                            'shipping_method' => 'pickup',
                            'shipping_address' => 'Ambil Sendiri di Toko ' . $client->business_name,
                            'status' => 'dikembalikan',
                            'created_at' => $now->copy()->subDays(5),
                            'updated_at' => $now->copy()->subHours(4),
                        ]);
                        $item = $this->attachItem($order, $product, 1, $order->total_amount);
                        Payment::create([
                            'order_id' => $order->id,
                            'client_payment_method_id' => $payMethod?->id,
                            'amount' => $order->total_amount,
                            'payment_proof' => 'payments/bukti-transfer-contoh.jpg',
                            'status' => 'diverifikasi',
                            'paid_at' => $now->copy()->subDays(5),
                        ]);
                        if ($unit) {
                            $unit->update(['status' => 'rusak']);
                            OrderItemUnit::create([
                                'order_item_id' => $item->id,
                                'equipment_unit_id' => $unit->id,
                            ]);
                        }
                        ReturnModel::create([
                            'order_id' => $order->id,
                            'return_method' => 'pickup',
                            'return_date' => $now->copy()->subHours(4),
                            'return_status' => 'diterima',
                            'late_hours' => 0,
                            'calculated_late_fee' => 0,
                            'late_fee_amount' => 0,
                            'late_fee_status' => 'tidak_ada',
                            'notes' => 'Ditemukan cacat fisik / goresan pada bodi unit saat inspeksi pengembalian.',
                            'processed_by' => $admin->id,
                        ]);
                        $damageReport = DamageReport::create([
                            'order_id' => $order->id,
                            'equipment_unit_id' => $unit?->id,
                            'description' => 'Ditemukan goresan dan benturan fisik pada unit saat inspeksi pengembalian.',
                            'image' => 'damages/busa-rusak.jpg',
                            'reported_by' => $admin->id,
                        ]);
                        DamageCase::create([
                            'damage_report_id' => $damageReport->id,
                            'repair_cost' => 75000.00,
                            'fine_amount' => 75000.00,
                            'payment_status' => 'belum_dibayar',
                            'case_status' => 'menunggu_respons',
                            'resolution_notes' => 'Toko mengajukan biaya perbaikan resmi sebesar Rp 75.000.',
                        ]);
                        IdentityGuarantee::create([
                            'order_id' => $order->id,
                            'customer_id' => $cust->id,
                            'full_name' => $cust->name,
                            'identity_number' => $niks[$cust->id],
                            'identity_document_image' => 'identity_guarantees/ktp-contoh.jpg',
                            'face_image' => 'identity_guarantees/selfie-contoh.jpg',
                            'identity_address' => $cust->address,
                            'status' => 'dititipkan',
                            'deposited_at' => $now->copy()->subDays(4),
                            'reviewed_by' => $admin->id,
                        ]);
                        break;

                    case 8:
                        // Skenario 9: Selesai Sukses Bebas Kendala (Omzet)
                        $order = Order::create([
                            'order_number' => $orderNumber,
                            'client_id' => $client->id,
                            'user_id' => $cust->id,
                            'rental_start_date' => $now->copy()->subDays(6),
                            'rental_end_date' => $now->copy()->subDays(3),
                            'duration_days' => 3,
                            'total_amount' => $product->rental_price_per_day * 3,
                            'shipping_method' => 'pickup',
                            'shipping_address' => 'Ambil Sendiri di Toko ' . $client->business_name,
                            'status' => 'selesai',
                            'created_at' => $now->copy()->subDays(7),
                            'updated_at' => $now->copy()->subDays(3),
                        ]);
                        $item = $this->attachItem($order, $product, 1, $order->total_amount);
                        Payment::create([
                            'order_id' => $order->id,
                            'client_payment_method_id' => $payMethod?->id,
                            'amount' => $order->total_amount,
                            'payment_proof' => 'payments/bukti-transfer-contoh.jpg',
                            'status' => 'diverifikasi',
                            'paid_at' => $now->copy()->subDays(7),
                        ]);
                        ReturnModel::create([
                            'order_id' => $order->id,
                            'return_method' => 'pickup',
                            'return_date' => $now->copy()->subDays(3),
                            'return_status' => 'selesai',
                            'late_hours' => 0,
                            'calculated_late_fee' => 0,
                            'late_fee_amount' => 0,
                            'late_fee_status' => 'tidak_ada',
                            'notes' => 'Unit kembali lengkap, bersih, fungsi normal 100%. Transaksi selesai.',
                        ]);
                        IdentityGuarantee::create([
                            'order_id' => $order->id,
                            'customer_id' => $cust->id,
                            'full_name' => $cust->name,
                            'identity_number' => $niks[$cust->id],
                            'identity_document_image' => 'identity_guarantees/ktp-contoh.jpg',
                            'face_image' => 'identity_guarantees/selfie-contoh.jpg',
                            'identity_address' => $cust->address,
                            'status' => 'dikembalikan',
                            'returned_at' => $now->copy()->subDays(3),
                        ]);
                        if ($unit) {
                            $unit->update(['status' => 'tersedia']);
                            OrderItemUnit::create([
                                'order_item_id' => $item->id,
                                'equipment_unit_id' => $unit->id,
                            ]);
                        }
                        break;

                    case 9:
                        // Skenario 10: Dibatalkan & Refund Selesai
                        $order = Order::create([
                            'order_number' => $orderNumber,
                            'client_id' => $client->id,
                            'user_id' => $cust->id,
                            'rental_start_date' => $now->copy()->subDays(2),
                            'rental_end_date' => $now->copy()->addDays(1),
                            'duration_days' => 3,
                            'total_amount' => $product->rental_price_per_day * 3,
                            'shipping_method' => 'delivery',
                            'shipping_address' => $cust->address,
                            'status' => 'dibatalkan',
                            'cancellation_reason' => 'Penyewa mengajukan pembatalan pemesanan karena perubahan jadwal mendesak.',
                            'refund_status' => 'selesai',
                            'refund_proof' => 'refunds/bukti-refund-contoh.jpg',
                            'created_at' => $now->copy()->subDays(2),
                            'updated_at' => $now->copy()->subDays(1),
                        ]);
                        $this->attachItem($order, $product, 1, $order->total_amount);
                        break;
                }
            }
        }
    }

    /**
     * Attach an order item cleanly.
     */
    private function attachItem(Order $order, Product $product, int $qty, float $subtotal): OrderItem
    {
        return OrderItem::updateOrCreate(
            ['order_id' => $order->id, 'product_id' => $product->id],
            [
                'quantity' => $qty,
                'price_per_day' => $product->rental_price_per_day,
                'subtotal' => $subtotal,
            ]
        );
    }

    /**
     * Synchronize PostgreSQL auto-increment sequences.
     */
    private function syncPostgresSequences(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $tables = [
            'client_registrations',
            'clients',
            'tenant_profiles',
            'subscriptions',
            'client_payment_methods',
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
            'notifications',
        ];

        foreach ($tables as $table) {
            try {
                DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), COALESCE((SELECT MAX(id) FROM {$table}), 1))");
            } catch (\Throwable $e) {
                // Ignore if sequence not present
            }
        }
    }
}
