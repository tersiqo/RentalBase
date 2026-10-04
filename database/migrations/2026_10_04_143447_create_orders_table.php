<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->dateTime('rental_start_date');
            $table->dateTime('rental_end_date');
            $table->integer('duration_days');
            $table->decimal('total_amount', 12, 2);
            $table->text('shipping_address'); // Alamat pengiriman customer saat checkout
            $table->text('cancellation_reason')->nullable();
            $table->string('refund_status')->default('tidak_ada'); // tidak_ada, menunggu_refund, refund_selesai
            $table->string('refund_proof')->nullable(); // Foto bukti transfer refund dari admin
            $table->string('status')->default('menunggu_pembayaran');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};