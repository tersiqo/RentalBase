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
        Schema::create('tenant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained('clients')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('subdomain')->unique();
            $table->string('logo_path')->nullable();
            $table->string('theme_color', 7)->default('#F97316');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_profiles');
    }
};
