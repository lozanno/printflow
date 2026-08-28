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
            $table->date('estimated_delivery_date')->nullable()->after('delivery_type');
            $table->boolean('is_urgent')->default(false)->after('estimated_delivery_date');
            // Manual flag Ventas sets/clears - not derived from status or
            // stage, since only a human following up with the client knows
            // when an order is actually stuck waiting on them.
            $table->boolean('needs_sales_attention')->default(false)->after('production_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['estimated_delivery_date', 'is_urgent', 'needs_sales_attention']);
        });
    }
};
