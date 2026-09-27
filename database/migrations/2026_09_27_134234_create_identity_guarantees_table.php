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
        Schema::create('identity_guarantees', function (Blueprint $table) {
        $table->id();

        $table->foreignId('order_id')
            ->unique()
            ->constrained('orders')
            ->restrictOnDelete();

        $table->foreignId('customer_id')
            ->constrained('users')
            ->restrictOnDelete();

        $table->string('nama_lengkap');

        $table->string('nomor_identitas');

        $table->string('foto_identitas');

        $table->string('foto_wajah');

        $table->text('alamat');

        $table->enum('status', [
            'menunggu',
            'diverifikasi',
            'ditolak',
        ])->default('menunggu');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identity_guarantees');
    }
};
