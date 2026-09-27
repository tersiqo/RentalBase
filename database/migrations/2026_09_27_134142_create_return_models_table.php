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
        Schema::create('returns', function (Blueprint $table) {
        $table->id();

        $table->foreignId('order_id')
            ->constrained('orders')
            ->restrictOnDelete();

        $table->enum('metode_pengembalian', [
            'kurir',
            'langsung',
        ]);

        $table->string('nama_kurir')
            ->nullable();

        $table->string('nomor_resi')
            ->nullable();

        $table->date('tanggal_pengembalian')
            ->nullable();

        $table->enum('status_pengembalian', [
            'diajukan',
            'diproses',
            'dalam_pengembalian',
            'diterima',
            'selesai',
            'dibatalkan',
        ])->default('diajukan');

        $table->text('catatan')
            ->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_models');
    }
};
