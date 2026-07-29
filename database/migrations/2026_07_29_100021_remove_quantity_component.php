<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The "quantity" Component role is retired: the customer-facing
     * quantity picker is now generated directly from each CatalogProduct's
     * PricingTier rows, not from a shared Component/ComponentOption pair.
     * Deleting it cascades to its options and its ProductTemplateComponent
     * attachments (both FKs are cascadeOnDelete).
     */
    public function up(): void
    {
        DB::table('components')->where('code', 'quantity')->delete();
    }

    /**
     * Reverse the migrations.
     *
     * Data cleanup, not reversible: the deleted Component, its options,
     * and its template attachments are gone for good.
     */
    public function down(): void {}
};
