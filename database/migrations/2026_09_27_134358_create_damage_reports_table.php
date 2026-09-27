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
        Schema::create('damage_reports', function (Blueprint $table) {
        $table->id();

        $table->foreignId('order_id')
            ->constrained('orders')
            ->restrictOnDelete();

        $table->foreignId('dilaporkan_oleh')
            ->constrained('users')
            ->restrictOnDelete();

        $table->text('deskripsi');

        $table->string('foto')
            ->nullable();

        $table->enum('status', [
            'dilaporkan',
            'ditinjau',
            'ditindaklanjuti',
            'selesai',
            'ditolak',
        ])->default('dilaporkan');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
