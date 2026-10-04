<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CartAndOrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('carts')->insert([
            'id' => 1,
            'client_id' => 1,
            'user_id' => 4,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('cart_items')->insert([
            'id' => 1,
            'cart_id' => 1,
            'product_id' => 2,
            'start_date' => Carbon::now()->addDays(5)->setTime(9, 0),
            'end_date' => Carbon::now()->addDays(7)->setTime(9, 0),
            'duration_days' => 2,
            'quantity' => 1,
            'subtotal' => 500000.00,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('orders')->insert([
            'id' => 1,
            'client_id' => 1,
            'user_id' => 4,
            'order_number' => 'ORD-20261001-001',
            'rental_start_date' => Carbon::now()->addDays(1)->setTime(10, 0),
            'rental_end_date' => Carbon::now()->addDays(3)->setTime(10, 0),
            'duration_days' => 2,
            'total_amount' => 700000.00,
            'shipping_address' => 'Jl. Borobudur No. 88 Malang',
            'cancellation_reason' => null,
            'refund_status' => 'tidak_ada',
            'refund_proof' => null,
            'status' => 'pembayaran_terverifikasi',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('order_items')->insert([
            'id' => 1,
            'order_id' => 1,
            'product_id' => 1,
            'quantity' => 1,
            'price_per_day' => 350000.00,
            'subtotal' => 700000.00,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('order_item_units')->insert([
            'id' => 1,
            'order_item_id' => 1,
            'equipment_unit_id' => 2,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('identity_guarantees')->insert([
            'id' => 1,
            'order_id' => 1,
            'customer_id' => 4,
            'full_name' => 'Rizky Pelanggan',
            'identity_number' => '3573019988770001',
            'identity_document_image' => 'guarantees/ktp-rizky.jpg',
            'face_image' => 'guarantees/selfie-rizky.jpg',
            'identity_address' => 'Jl. Borobudur No. 88 Malang',
            'status' => 'diverifikasi',
            'reviewed_by' => 3,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}