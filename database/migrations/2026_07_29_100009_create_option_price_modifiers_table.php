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
        Schema::create('option_price_modifiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('component_option_id')->constrained()->cascadeOnDelete();

            // ModifierType: FIXED_ADD | PERCENT_MULTIPLY | PER_UNIT_ADD
            $table->string('modifier_type');
            $table->decimal('value', 10, 4);

            $table->timestamps();

            $table->unique(
                ['pricing_profile_id', 'component_option_id'],
                'option_price_modifiers_profile_option_unique'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('option_price_modifiers');
    }
};
