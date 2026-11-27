<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations to synchronize legacy plan names with config/packages.php
     * WITHOUT modifying any table structure.
     */
    public function up(): void
    {
        // 1. Sinkronisasi tabel 'subscriptions'
        // 'Pro Enterprise' atau nama mengandung 'enterprise' diubah menjadi 'Professional'
        DB::table('subscriptions')
            ->whereRaw("LOWER(plan_name) LIKE '%enterprise%'")
            ->orWhereRaw("LOWER(plan_name) = 'pro enterprise'")
            ->update([
                'plan_name' => 'Professional',
            ]);

        // 'Basic' atau 'Basic (Simulasi Expiring)' diubah menjadi 'Business'
        DB::table('subscriptions')
            ->whereRaw("LOWER(plan_name) LIKE '%basic%'")
            ->update([
                'plan_name' => 'Business',
            ]);

        // Normalisasi nama simulasi seperti 'Starter (Simulasi Expired)' menjadi 'Starter'
        DB::table('subscriptions')
            ->whereRaw("LOWER(plan_name) LIKE '%starter%' AND plan_name != 'Starter'")
            ->update([
                'plan_name' => 'Starter',
            ]);

        // 2. Sinkronisasi tabel 'client_registrations'
        DB::table('client_registrations')
            ->whereRaw("LOWER(plan_name) LIKE '%enterprise%'")
            ->orWhereRaw("LOWER(plan_name) = 'pro enterprise'")
            ->update([
                'plan_name' => 'Professional',
            ]);

        DB::table('client_registrations')
            ->whereRaw("LOWER(plan_name) LIKE '%basic%'")
            ->update([
                'plan_name' => 'Business',
            ]);

        DB::table('client_registrations')
            ->whereRaw("LOWER(plan_name) LIKE '%starter%' AND plan_name != 'Starter'")
            ->update([
                'plan_name' => 'Starter',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback tidak mengubah struktur data karena hanya normalisasi nilai string
    }
};
