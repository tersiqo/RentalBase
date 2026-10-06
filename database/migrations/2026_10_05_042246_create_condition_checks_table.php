<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('condition_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_unit_id')->constrained('equipment_units')->onDelete('cascade');
            $table->string('check_type');
            $table->string('condition');
            $table->text('notes')->nullable();
            $table->string('image')->nullable();
            $table->foreignId('checked_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('condition_checks');
    }
};