<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('return_method'); // kurir atau langsung
            $table->string('courier_name')->nullable();
            $table->string('tracking_number')->nullable();
            $table->dateTime('return_date');
            $table->integer('late_hours')->default(0); // <--- Sebelumnya late_days
            $table->decimal('calculated_late_fee', 12, 2)->default(0); // Denda otomatis jam
            $table->decimal('late_fee_amount', 12, 2)->default(0); // Override nominal denda admin
            $table->string('late_fee_status')->default('tidak_ada'); // tidak_ada, menunggu_pembayaran, lunas
            $table->string('late_fee_proof')->nullable(); // Upload bukti bayar denda
            $table->string('return_status')->default('diajukan'); // <--- Sebelumnya status
            $table->text('notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};