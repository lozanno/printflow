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
        Schema::table('shops', function (Blueprint $table) {
            $table->string('pickup_line1')->nullable();
            $table->string('pickup_line2')->nullable();
            $table->string('pickup_city')->nullable();
            $table->string('pickup_state')->nullable();
            $table->string('pickup_postal_code')->nullable();
            $table->string('pickup_phone')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'pickup_line1',
                'pickup_line2',
                'pickup_city',
                'pickup_state',
                'pickup_postal_code',
                'pickup_phone',
            ]);
        });
    }
};
