<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->text('description')->nullable();
            $table->string('subdomain');
            $table->string('plan_name');
            $table->string('admin_name');
            $table->string('admin_email');
            $table->string('admin_phone')->nullable();
            $table->string('admin_password');
            $table->string('status')->default('menunggu_verifikasi');
            $table->string('payment_proof')->nullable(); // Foto bukti bayar subskripsi pendaftaran
            $table->string('payment_status')->default('menunggu'); // menunggu, diverifikasi, ditolak
            $table->text('rejection_reason')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable(); // FK ke users (tabel users dibuat setelah tabel ini, jadi tanpa constraint)
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_registrations');
    }
};
