<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('damage_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('damage_report_id')->constrained('damage_reports')->onDelete('cascade');
            $table->decimal('repair_cost', 12, 2);
            $table->decimal('fine_amount', 12, 2);
            $table->string('payment_status')->default('belum_dibayar');
            $table->string('case_status')->default('terbuka');
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('damage_cases');
    }
};