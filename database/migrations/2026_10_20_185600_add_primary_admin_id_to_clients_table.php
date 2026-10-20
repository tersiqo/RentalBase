<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('primary_admin_id')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();
        });

        // Backfill data: Untuk tenant yang sudah ada, tetapkan user pertama yang ber-role admin_rental sebagai primary_admin_id
        DB::statement("
            UPDATE clients
            SET primary_admin_id = (
                SELECT id
                FROM users
                WHERE users.client_id = clients.id
                  AND users.role = 'admin_rental'
                ORDER BY id ASC
                LIMIT 1
            )
            WHERE primary_admin_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['primary_admin_id']);
            $table->dropColumn('primary_admin_id');
        });
    }
};
