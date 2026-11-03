<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_profiles', function (Blueprint $table) {
            $table->string('hero_showcase_mode')->default('illustration')->after('theme_color');
            $table->foreignId('hero_featured_product_id')->nullable()->after('hero_showcase_mode')->constrained('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenant_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hero_featured_product_id');
            $table->dropColumn('hero_showcase_mode');
        });
    }
};
