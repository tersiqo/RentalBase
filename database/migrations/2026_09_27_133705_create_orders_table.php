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
        Schema::create('orders', function (Blueprint $table) {
        $table->id();

        $table->foreignId('client_id')
            ->constrained('clients')
            ->restrictOnDelete();

        $table->foreignId('customer_id')
            ->constrained('users')
            ->restrictOnDelete();

        $table->string('kode_order');

        $table->date('tanggal_mulai');
        $table->date('tanggal_selesai');

        $table->text('alamat_pengiriman');

        $table->decimal('total_harga', 12, 2);

        $table->enum('status', [
            'menunggu_konfirmasi',
            'menunggu_pembayaran',
            'pembayaran_terverifikasi',
            'diproses',
            'dikirim',
            'diterima',
            'dikembalikan',
            'selesai',
            'ditolak',
            'dibatalkan',
        ])->default('menunggu_konfirmasi');

        $table->timestamps();

        $table->index(['client_id', 'status']);
        $table->index(['customer_id', 'client_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
