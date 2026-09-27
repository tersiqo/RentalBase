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
        Schema::create('payments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('order_id')
            ->constrained('orders')
            ->restrictOnDelete();

        $table->enum('metode', [
            'bank_transfer',
        ]);

        $table->string('bukti_pembayaran')
            ->nullable();

        $table->timestamp('tanggal_bayar')
            ->nullable();

        $table->enum('status', [
            'menunggu',
            'diverifikasi',
            'ditolak',
        ])->default('menunggu');

        $table->foreignId('diverifikasi_oleh')
            ->nullable()
            ->constrained('users')
            ->restrictOnDelete();

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
        Schema::dropIfExists('payments');
    }
};
