<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('identity_guarantees', function (Blueprint $table) {
            $table->timestamp('deposited_at')->nullable()->after('status');
            $table->timestamp('returned_at')->nullable()->after('deposited_at');
        });
    }

    public function down(): void
    {
        Schema::table('identity_guarantees', function (Blueprint $table) {
            $table->dropColumn(['deposited_at', 'returned_at']);
        });
    }
};
