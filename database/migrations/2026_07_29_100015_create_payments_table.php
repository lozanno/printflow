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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            $table->string('provider')->default('stripe');
            $table->string('provider_reference');

            // method, populated once Stripe resolves it: card | oxxo | spei
            $table->string('method')->nullable();

            // PaymentStatus: PENDING | SUCCEEDED | FAILED | CANCELLED
            $table->string('status')->default('PENDING');

            $table->decimal('amount', 10, 2);
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->unique(['provider', 'provider_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
