<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductionStage;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

function makePaidOrder(): Order
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

    return $order->fresh();
}

it('redirects guests to login', function () {
    $order = makePaidOrder();

    $this->patch(route('admin.orders.production-stage.update', $order), ['production_stage' => 'IN_PRODUCTION'])
        ->assertRedirect(route('login'));
});

it('lets an admin advance the production stage and records who did it', function () {
    $order = makePaidOrder();
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.orders.production-stage.update', $order), ['production_stage' => 'IN_PRODUCTION'])
        ->assertRedirect();

    $order->refresh();
    expect($order->production_stage)->toBe(ProductionStage::InProduction);

    $latestChange = $order->stageChanges()->latest('id')->first();
    expect($latestChange->from_stage)->toBe(ProductionStage::Pending);
    expect($latestChange->to_stage)->toBe(ProductionStage::InProduction);
    expect($latestChange->changed_by_user_id)->toBe($admin->id);
});

it('lets produccion advance the production stage', function () {
    $order = makePaidOrder();
    $operator = User::factory()->role(UserRole::Produccion)->create();

    $this->actingAs($operator)
        ->patch(route('admin.orders.production-stage.update', $order), ['production_stage' => 'IN_PRODUCTION'])
        ->assertRedirect();

    expect($order->fresh()->production_stage)->toBe(ProductionStage::InProduction);
});

it('blocks ventas, administrativo, and calidad from advancing the production stage', function () {
    foreach ([UserRole::Ventas, UserRole::Administrativo, UserRole::Calidad] as $role) {
        $order = makePaidOrder();
        $user = User::factory()->role($role)->create();

        $this->actingAs($user)
            ->patch(route('admin.orders.production-stage.update', $order), ['production_stage' => 'IN_PRODUCTION'])
            ->assertForbidden();

        expect($order->fresh()->production_stage)->toBe(ProductionStage::Pending);
    }
});

it('rejects an invalid stage value', function () {
    $order = makePaidOrder();
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.orders.production-stage.update', $order), ['production_stage' => 'NOT_A_STAGE'])
        ->assertSessionHasErrors('production_stage');
});
