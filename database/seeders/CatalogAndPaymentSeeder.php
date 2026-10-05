<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CatalogAndPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('categories')->insert([
            [
                'id' => 1,
                'client_id' => 1,
                'name' => 'Kamera Mirrorless',
                'slug' => 'kamera-mirrorless',
                'description' => 'Kamera mirrorless full-frame dan APS-C.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'client_id' => 1,
                'name' => 'Lensa Profesional',
                'slug' => 'lensa-profesional',
                'description' => 'Lensa prime dan zoom master.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'client_id' => 1,
                'name' => 'Lighting & Studio',
                'slug' => 'lighting-studio',
                'description' => 'Perlengkapan pencahayaan studio.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'client_id' => 1,
                'name' => 'Aksesoris & Stabilizer',
                'slug' => 'aksesoris-stabilizer',
                'description' => 'Gimbal, tripod, dan aksesoris lainnya.',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('products')->insert([
            [
                'id' => 1,
                'client_id' => 1,
                'category_id' => 1,
                'name' => 'Sony Alpha A7 IV Body Only',
                'slug' => 'sony-alpha-a7-iv',
                'description' => 'Kamera hybrid 33MP 4K 60p.',
                'rental_price_per_day' => 350000.00,
                'deposit_fee' => 500000.00,
                'main_image' => 'products/sony-a7iv.jpg',
                'status' => 'aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'client_id' => 1,
                'category_id' => 2,
                'name' => 'Sony FE 24-70mm f/2.8 GM II',
                'slug' => 'sony-fe-24-70-gm2',
                'description' => 'Lensa zoom standar flaghip Sony G Master.',
                'rental_price_per_day' => 250000.00,
                'deposit_fee' => 300000.00,
                'main_image' => 'products/sony-2470gm2.jpg',
                'status' => 'aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'client_id' => 1,
                'category_id' => 1,
                'name' => 'Canon EOS R5 Body Only',
                'slug' => 'canon-eos-r5',
                'description' => 'Kamera 45MP 8K Raw.',
                'rental_price_per_day' => 450000.00,
                'deposit_fee' => 750000.00,
                'main_image' => 'products/canon-r5.jpg',
                'status' => 'aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'client_id' => 1,
                'category_id' => 2,
                'name' => 'Sony FE 70-200mm f/2.8 GM',
                'slug' => 'sony-fe-70-200-gm',
                'description' => 'Lensa Telephoto Zoom.',
                'rental_price_per_day' => 275000.00,
                'deposit_fee' => 400000.00,
                'main_image' => 'products/sony-70200gm.jpg',
                'status' => 'aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'client_id' => 1,
                'category_id' => 3,
                'name' => 'Godox SL-150W II Continuous',
                'slug' => 'godox-sl-150w-ii',
                'description' => 'Lampu Continuous Daylight.',
                'rental_price_per_day' => 150000.00,
                'deposit_fee' => 200000.00,
                'main_image' => 'products/godox-sl150.jpg',
                'status' => 'aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'client_id' => 1,
                'category_id' => 4,
                'name' => 'DJI RS 3 Pro Gimbal Combo',
                'slug' => 'dji-rs-3-pro',
                'description' => 'Gimbal profesional payload 4.5kg.',
                'rental_price_per_day' => 220000.00,
                'deposit_fee' => 300000.00,
                'main_image' => 'products/dji-rs3.jpg',
                'status' => 'aktif',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('equipment_units')->insert([
            [
                'id' => 1,
                'client_id' => 1,
                'product_id' => 1,
                'unit_code' => 'KMK-A74-01',
                'serial_number' => 'SN-SNY-9988771',
                'condition' => 'baik',
                'status' => 'tersedia',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'client_id' => 1,
                'product_id' => 1,
                'unit_code' => 'KMK-A74-02',
                'serial_number' => 'SN-SNY-9988772',
                'condition' => 'baik',
                'status' => 'disewa',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'client_id' => 1,
                'product_id' => 2,
                'unit_code' => 'KMK-L2470-01',
                'serial_number' => 'SN-LNS-1122331',
                'condition' => 'baik',
                'status' => 'tersedia',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'client_id' => 1,
                'product_id' => 3, // Canon R5
                'unit_code' => 'KMK-R5-01',
                'serial_number' => 'SN-CNN-332211',
                'condition' => 'baik',
                'status' => 'tersedia',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'client_id' => 1,
                'product_id' => 4, // Sony 70-200
                'unit_code' => 'KMK-L70200-01',
                'serial_number' => 'SN-LNS-887766',
                'condition' => 'baik',
                'status' => 'tersedia',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 6,
                'client_id' => 1,
                'product_id' => 5, // Godox
                'unit_code' => 'KMK-GDX-01',
                'serial_number' => 'SN-GDX-111111',
                'condition' => 'baik',
                'status' => 'tersedia',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 7,
                'client_id' => 1,
                'product_id' => 5, // Godox
                'unit_code' => 'KMK-GDX-02',
                'serial_number' => 'SN-GDX-222222',
                'condition' => 'baik',
                'status' => 'tersedia',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 8,
                'client_id' => 1,
                'product_id' => 6, // DJI
                'unit_code' => 'KMK-DJI-01',
                'serial_number' => 'SN-DJI-777777',
                'condition' => 'baik',
                'status' => 'tersedia',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('client_payment_methods')->insert([
            [
                'id' => 1,
                'client_id' => 1,
                'bank_name' => 'BCA',
                'account_number' => '8830991122',
                'account_holder' => 'KameraKu Studio Malang',
                'qris_image' => 'payments/qris-bca.png',
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'client_id' => 1,
                'bank_name' => 'Mandiri',
                'account_number' => '1440008877665',
                'account_holder' => 'KameraKu Studio Malang',
                'qris_image' => null,
                'is_active' => true,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('notifications')->insert([
            'id' => 1,
            'client_id' => 1,
            'user_id' => 2,
            'title' => 'Pesanan Baru #ORD-20261001-001',
            'message' => 'Rizky Pelanggan telah membuat pesanan sewa baru.',
            'type' => 'pesanan',
            'is_read' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
