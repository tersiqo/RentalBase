<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ClientAndAuthSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('clients')->insert([
            'id' => 1,
            'business_name' => 'KameraKu Studio Malang',
            'description' => 'Sewa kamera dan perlengkapan fotografi profesional Malang.',
            'logo' => 'clients/logos/kameraku.png',
            'subdomain' => 'kameraku',
            'theme_color' => '#3B82F6',
            'status' => 'aktif',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('subscriptions')->insert([
            'id' => 1,
            'client_id' => 1,
            'plan_name' => 'Pro Enterprise',
            'max_products' => 200,
            'max_users' => 15,
            'price' => 299000.00,
            'billing_cycle' => 'bulanan',
            'start_date' => Carbon::now()->subMonths(1),
            'end_date' => Carbon::now()->addMonths(11),
            'status' => 'aktif',
            'payment_proof' => 'subscriptions/proofs/tf-sub-001.jpg',
            'payment_status' => 'diverifikasi',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('users')->insert([
            [
                'id' => 1,
                'client_id' => null,
                'name' => 'Owner RentalBase SaaS',
                'email' => 'owner@rentalbase.id',
                'password' => Hash::make('password123'),
                'role' => 'owner', // Role resmi Owner SaaS Platform
                'phone' => '081122334455',
                'address' => 'HQ RentalBase Jakarta',
                'avatar' => null,
                'status' => 'aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'client_id' => 1,
                'name' => 'Budi Admin KameraKu',
                'email' => 'budi@kameraku.com',
                'password' => Hash::make('password123'),
                'role' => 'admin_rental', // Role resmi Admin Rental Toko
                'phone' => '081234567890',
                'address' => 'Jl. Soekarno Hatta No. 45 Malang',
                'avatar' => null,
                'status' => 'aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'client_id' => 1,
                'name' => 'Siti Admin KameraKu',
                'email' => 'siti@kameraku.com',
                'password' => Hash::make('password123'),
                'role' => 'admin_rental', // Role resmi Admin Rental Toko
                'phone' => '081299887766',
                'address' => 'Jl. Suhat No. 12 Malang',
                'avatar' => null,
                'status' => 'aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'client_id' => 1,
                'name' => 'Rizky Pelanggan',
                'email' => 'rizky@gmail.com',
                'password' => Hash::make('password123'),
                'role' => 'customer',
                'phone' => '085711223344',
                'address' => 'Jl. Borobudur No. 88 Malang',
                'avatar' => null,
                'status' => 'aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('client_registrations')->insert([
            'id' => 1,
            'business_name' => 'Outdoor Malang Rental',
            'description' => 'Penyewaan tenda dan alat kamping gunung.',
            'subdomain' => 'outdoormalang',
            'plan_name' => 'Basic',
            'admin_name' => 'Dedi Outdoor',
            'admin_email' => 'dedi@outdoormalang.com',
            'admin_phone' => '081334455667',
            'admin_password' => Hash::make('password123'),
            'status' => 'menunggu_verifikasi',
            'payment_proof' => 'registrations/proofs/tf-reg-dedi.jpg',
            'payment_status' => 'menunggu',
            'rejection_reason' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}

