<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('plan_name');
            $table->integer('max_products')->default(50);
            $table->integer('max_users')->default(5);
            $table->decimal('price', 12, 2);
            $table->string('billing_cycle');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->string('status')->default('aktif');
            $table->string('payment_proof')->nullable(); // Foto bukti bayar perpanjangan/upgrade
            $table->string('payment_status')->default('diverifikasi'); // menunggu, diverifikasi, ditolak
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
