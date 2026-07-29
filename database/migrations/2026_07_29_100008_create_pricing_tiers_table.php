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
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_profile_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('min_quantity');
            $table->unsignedInteger('max_quantity')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->timestamps();

            $table->index(['pricing_profile_id', 'min_quantity']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_tiers');
    }
};
