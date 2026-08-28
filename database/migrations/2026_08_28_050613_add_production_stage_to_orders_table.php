<?php

use App\Enums\ProductionStage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // ProductionStage - a separate axis from `status` (payment).
            $table->string('production_stage')->nullable()->after('status');
        });

        // Orders placed before this shipped have no production history -
        // start them at the beginning of the pipeline rather than leaving
        // them null, since every other order created from here on gets a
        // stage the moment it's paid (see OrderController::store).
        DB::table('orders')->update(['production_stage' => ProductionStage::Pending->value]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('production_stage');
        });
    }
};
