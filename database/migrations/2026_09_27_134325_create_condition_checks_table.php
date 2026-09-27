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
        Schema::create('condition_checks', function (Blueprint $table) {
        $table->id();

        $table->foreignId('order_id')
            ->constrained('orders')
            ->restrictOnDelete();

        $table->enum('tipe', [
            'sebelum',
            'sesudah',
        ]);

        $table->text('catatan');

        $table->string('foto')
            ->nullable();

        $table->foreignId('diperiksa_oleh')
            ->constrained('users')
            ->restrictOnDelete();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condition_checks');
    }
};
