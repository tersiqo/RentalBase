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
