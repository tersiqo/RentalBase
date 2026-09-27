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
        Schema::create('shipments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('order_id')
            ->constrained('orders')
            ->restrictOnDelete();

        $table->enum('metode_pengiriman', [
            'kurir',
            'langsung',
        ]);

        $table->string('nama_kurir')
            ->nullable();

        $table->string('nomor_resi')
            ->nullable();

        $table->date('tanggal_kirim')
            ->nullable();

        $table->date('tanggal_diterima')
            ->nullable();

        $table->enum('status_pengiriman', [
            'menunggu',
            'diproses',
            'dikirim',
            'diterima',
            'dibatalkan',
        ])->default('menunggu');

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
        Schema::dropIfExists('shipments');
    }
};
