<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductionStage;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

function makeOrderInQualityCheck(): Order
{
    $shop = makeShop();
    $customer = $shop->customers()->create(['name' => 'Ana Garcia', 'email' => 'ana@example.com']);

    $order = $customer->orders()->create([
        'shop_id' => $shop->id,
        'delivery_type' => 'PICKUP',
        'status' => OrderStatus::Paid,
        'subtotal' => 100,
        'shipping_cost' => 0,
        'total' => 100,
        'currency' => 'MXN',
    ]);

    $order->payments()->create([
        'provider' => 'manual',
        'provider_reference' => (string) Str::uuid(),
        'method' => 'card',
        'status' => PaymentStatus::Succeeded,
        'amount' => 100,
        'paid_at' => now(),
    ]);

    $order->advanceProductionStage(ProductionStage::Pending, null);
    $order->advanceProductionStage(ProductionStage::InProduction, null);
    $order->advanceProductionStage(ProductionStage::QualityCheck, null);

    return $order->fresh();
}

it('redirects guests to login', function () {
    $order = makeOrderInQualityCheck();

    $this->patch(route('admin.orders.quality-check.update', $order), ['passed' => true])
        ->assertRedirect(route('login'));
});

it('lets calidad approve the quality check and records who did it', function () {
    $order = makeOrderInQualityCheck();
    $inspector = User::factory()->role(UserRole::Calidad)->create();

    $this->actingAs($inspector)
        ->patch(route('admin.orders.quality-check.update', $order), ['passed' => true])
        ->assertRedirect();

    $order->refresh();
    expect($order->quality_checked_at)->not->toBeNull();
    expect($order->quality_checked_by_user_id)->toBe($inspector->id);
});

it('lets calidad revert a quality check approval', function () {
    $order = makeOrderInQualityCheck();
    $inspector = User::factory()->role(UserRole::Calidad)->create();
    $order->setQualityChecked(true, $inspector->id);

    $this->actingAs($inspector)
        ->patch(route('admin.orders.quality-check.update', $order), ['passed' => false])
        ->assertRedirect();

    $order->refresh();
    expect($order->quality_checked_at)->toBeNull();
    expect($order->quality_checked_by_user_id)->toBeNull();
});

it('lets an admin approve the quality check too', function () {
    $order = makeOrderInQualityCheck();
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.orders.quality-check.update', $order), ['passed' => true])
        ->assertRedirect();

    expect($order->fresh()->quality_checked_at)->not->toBeNull();
});

it('blocks ventas, administrativo, and produccion from the quality check', function () {
    foreach ([UserRole::Ventas, UserRole::Administrativo, UserRole::Produccion] as $role) {
        $order = makeOrderInQualityCheck();
        $user = User::factory()->role($role)->create();

        $this->actingAs($user)
            ->patch(route('admin.orders.quality-check.update', $order), ['passed' => true])
            ->assertForbidden();

        expect($order->fresh()->quality_checked_at)->toBeNull();
    }
});

it('blocks moving an order to ready without a quality check', function () {
    $order = makeOrderInQualityCheck();
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.orders.production-stage.update', $order), ['production_stage' => 'READY'])
        ->assertSessionHasErrors('production_stage');

    expect($order->fresh()->production_stage)->toBe(ProductionStage::QualityCheck);
});

it('allows moving an order to ready once quality has been checked', function () {
    $order = makeOrderInQualityCheck();
    $admin = User::factory()->create();
    $order->setQualityChecked(true, $admin->id);

    $this->actingAs($admin)
        ->patch(route('admin.orders.production-stage.update', $order), ['production_stage' => 'READY'])
        ->assertRedirect();

    expect($order->fresh()->production_stage)->toBe(ProductionStage::Ready);
});
