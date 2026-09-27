<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('damage_cases', function (Blueprint $table) {
        $table->id();

        $table->foreignId('damage_report_id')
            ->unique()
            ->constrained('damage_reports')
            ->restrictOnDelete();

        $table->enum('status', [
            'dibuka',
            'menunggu_tanggapan_customer',
            'diproses',
            'selesai',
            'dibatalkan',
        ])->default('dibuka');

        $table->text('tanggapan_customer')
            ->nullable();

        $table->text('catatan_admin')
            ->nullable();

        $table->text('hasil_penyelesaian')
            ->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_cases');
    }
};
