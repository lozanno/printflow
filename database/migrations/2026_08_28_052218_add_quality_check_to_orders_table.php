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
        Schema::table('orders', function (Blueprint $table) {
            // Calidad's sign-off gate - see Order::advanceProductionStage(),
            // which refuses to move an order to READY/DELIVERED while this
            // is null.
            $table->timestamp('quality_checked_at')->nullable()->after('production_stage');
            $table->foreignId('quality_checked_by_user_id')->nullable()->after('quality_checked_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('quality_checked_by_user_id');
            $table->dropColumn('quality_checked_at');
        });
    }
};
