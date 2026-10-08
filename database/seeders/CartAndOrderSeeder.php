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
            [
                'id' => 1,
                'client_id' => 1,
                'user_id' => 4,
                'order_number' => 'ORD-20261001-001',
                'rental_start_date' => Carbon::now()->addDays(1)->setTime(10, 0),
                'rental_end_date' => Carbon::now()->addDays(3)->setTime(10, 0),
                'duration_days' => 2,
                'total_amount' => 300000.00,
                'shipping_address' => 'Jl. Borobudur No. 88 Malang',
                'cancellation_reason' => null,
                'refund_status' => 'tidak_ada',
                'refund_proof' => null,
                'status' => 'menunggu_pembayaran',
                'created_at' => Carbon::now()->subHours(2),
                'updated_at' => Carbon::now()->subHours(2),
            ],
            [
                'id' => 2,
                'client_id' => 1,
                'user_id' => 4,
                'order_number' => 'ORD-20261002-002',
                'rental_start_date' => Carbon::now()->addDays(2)->setTime(9, 0),
                'rental_end_date' => Carbon::now()->addDays(3)->setTime(9, 0),
                'duration_days' => 1,
                'total_amount' => 125000.00,
                'shipping_address' => 'Jl. Soekarno Hatta No. 10 Malang',
                'cancellation_reason' => null,
                'refund_status' => 'tidak_ada',
                'refund_proof' => null,
                'status' => 'menunggu_konfirmasi',
                'created_at' => Carbon::now()->subHours(5),
                'updated_at' => Carbon::now()->subHours(5),
            ],
            [
                'id' => 3,
                'client_id' => 1,
                'user_id' => 4,
                'order_number' => 'ORD-20261003-003',
                'rental_start_date' => Carbon::now()->subDays(2)->setTime(10, 0),
                'rental_end_date' => Carbon::now()->addDays(2)->setTime(10, 0),
                'duration_days' => 4,
                'total_amount' => 400000.00,
                'shipping_address' => 'Jl. MT Haryono No. 5 Malang',
                'cancellation_reason' => null,
                'refund_status' => 'tidak_ada',
                'refund_proof' => null,
                'status' => 'sedang_disewa',
                'created_at' => Carbon::now()->subDays(3),
                'updated_at' => Carbon::now()->subDays(3),
            ],
            [
                'id' => 4,
                'client_id' => 1,
                'user_id' => 4,
                'order_number' => 'ORD-20261004-004',
                'rental_start_date' => Carbon::now()->addDays(1)->setTime(8, 0),
                'rental_end_date' => Carbon::now()->addDays(3)->setTime(8, 0),
                'duration_days' => 2,
                'total_amount' => 160000.00,
                'shipping_address' => 'Jl. Sigura-gura No. 2 Malang',
                'cancellation_reason' => null,
                'refund_status' => 'tidak_ada',
                'refund_proof' => null,
                'status' => 'siap_kirim',
                'created_at' => Carbon::now()->subDays(1),
                'updated_at' => Carbon::now()->subDays(1),
            ],
            [
                'id' => 5,
                'client_id' => 1,
                'user_id' => 4,
                'order_number' => 'ORD-20261005-005',
                'rental_start_date' => Carbon::now()->addDays(5)->setTime(12, 0),
                'rental_end_date' => Carbon::now()->addDays(10)->setTime(12, 0),
                'duration_days' => 5,
                'total_amount' => 450000.00,
                'shipping_address' => 'Jl. Galunggung No. 4 Malang',
                'cancellation_reason' => null,
                'refund_status' => 'tidak_ada',
                'refund_proof' => null,
                'status' => 'menunggu_pembayaran',
                'created_at' => Carbon::now()->subMinutes(30),
                'updated_at' => Carbon::now()->subMinutes(30),
            ]
        ]);

        DB::table('order_items')->insert([
            [
                'id' => 1,
                'order_id' => 1,
                'product_id' => 1,
                'quantity' => 1,
                'price_per_day' => 150000.00,
                'subtotal' => 300000.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 2,
                'order_id' => 2,
                'product_id' => 2,
                'quantity' => 1,
                'price_per_day' => 125000.00,
                'subtotal' => 125000.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 3,
                'order_id' => 3,
                'product_id' => 3,
                'quantity' => 1,
                'price_per_day' => 100000.00,
                'subtotal' => 400000.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 4,
                'order_id' => 4,
                'product_id' => 4,
                'quantity' => 1,
                'price_per_day' => 80000.00,
                'subtotal' => 160000.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'id' => 5,
                'order_id' => 5,
                'product_id' => 5,
                'quantity' => 1,
                'price_per_day' => 90000.00,
                'subtotal' => 450000.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
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