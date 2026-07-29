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
        Schema::create('pricing_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_product_id')->unique()->constrained()->cascadeOnDelete();

            // Shape depends on the owning blueprint's pricing_strategy, e.g.
            // PER_AREA_WITH_SETUP -> {"rate_per_sqm": ..., "setup_fee": ...}
            $table->json('params')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_profiles');
    }
};
