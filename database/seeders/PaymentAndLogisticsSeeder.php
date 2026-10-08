<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentAndLogisticsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('payments')->insert([
            'id' => 1,
            'order_id' => 1,
            'client_payment_method_id' => 1,
            'amount' => 300000.00,
            'payment_proof' => 'payments/proofs/tf-rizky-001.jpg',
            'status' => 'dikonfirmasi',
            'notes' => 'Transfer via M-BCA senilai 300rb (Biaya Sewa 2 Hari)',
            'verified_by' => 3,
            'paid_at' => Carbon::now()->subHours(2),
            'verified_at' => Carbon::now()->subHour(1),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('shipments')->insert([
            'id' => 1,
            'order_id' => 1,
            'shipping_method' => 'ambil_di_toko',
            'tracking_number' => null,
            'delivery_address' => 'Diambil langsung oleh Rizky di toko Baby Rental Studio',
            'courier_name' => null,
            'status' => 'siap_diambil',
            'shipped_at' => null,
            'delivered_at' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('returns')->insert([
            'id' => 1,
            'order_id' => 1,
            'return_method' => 'langsung',
            'courier_name' => null,
            'tracking_number' => null,
            'return_date' => Carbon::now()->addDays(3)->setTime(10, 0),
            'late_hours' => 0,
            'calculated_late_fee' => 0.00,
            'late_fee_amount' => 0.00,
            'late_fee_status' => 'tidak_ada',
            'late_fee_proof' => null,
            'return_status' => 'menunggu_pengecekan',
            'notes' => null,
            'processed_by' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('condition_checks')->insert([
            [
                'id' => 1,
                'equipment_unit_id' => 2,
                'check_type' => 'sebelum_sewa',
                'condition' => 'baik',
                'notes' => 'Roda lancar, kain bersih, sudah disterilisasi UV.',
                'image' => 'checks/yoyo-02-before.jpg',
                'checked_by' => 3,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        DB::table('damage_reports')->insert([
            'id' => 1,
            'order_id' => 1,
            'equipment_unit_id' => 2,
            'description' => 'Terdapat noda makanan membandel di dudukan.',
            'image' => 'damages/stain-seat.jpg',
            'reported_by' => 3,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        DB::table('damage_cases')->insert([
            'id' => 1,
            'damage_report_id' => 1,
            'repair_cost' => 50000.00,
            'fine_amount' => 50000.00,
            'payment_status' => 'belum_dibayar',
            'case_status' => 'terbuka',
            'resolution_notes' => 'Biaya cuci khusus Rp 50rb + denda kotor Rp 50rb ditagihkan ke customer.',
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}