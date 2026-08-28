<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

function makeDeliveryOrder(): Order
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

    return $order->fresh();
}

it('redirects guests to login for both delivery routes', function () {
    $order = makeDeliveryOrder();

    $this->patch(route('admin.orders.delivery.update', $order), ['estimated_delivery_date' => null, 'is_urgent' => false])
        ->assertRedirect(route('login'));

    $this->patch(route('admin.orders.sales-attention.update', $order), ['needs_sales_attention' => true])
        ->assertRedirect(route('login'));
});

it('lets an admin set the estimated delivery date and urgent flag', function () {
    $order = makeDeliveryOrder();
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.orders.delivery.update', $order), [
            'estimated_delivery_date' => '2026-09-15',
            'is_urgent' => true,
        ])
        ->assertRedirect();

    $order->refresh();
    expect($order->estimated_delivery_date->toDateString())->toBe('2026-09-15');
    expect($order->is_urgent)->toBeTrue();
});

it('lets ventas set the estimated delivery date and urgent flag', function () {
    $order = makeDeliveryOrder();
    $ventas = User::factory()->role(UserRole::Ventas)->create();

    $this->actingAs($ventas)
        ->patch(route('admin.orders.delivery.update', $order), [
            'estimated_delivery_date' => '2026-09-15',
            'is_urgent' => true,
        ])
        ->assertRedirect();

    expect($order->fresh()->is_urgent)->toBeTrue();
});

it('clears the estimated delivery date when set to null', function () {
    $order = makeDeliveryOrder();
    $order->update(['estimated_delivery_date' => '2026-09-15']);
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.orders.delivery.update', $order), [
            'estimated_delivery_date' => null,
            'is_urgent' => false,
        ])
        ->assertRedirect();

    expect($order->fresh()->estimated_delivery_date)->toBeNull();
});

it('blocks administrativo, produccion, and calidad from editing delivery', function () {
    foreach ([UserRole::Administrativo, UserRole::Produccion, UserRole::Calidad] as $role) {
        $order = makeDeliveryOrder();
        $user = User::factory()->role($role)->create();

        $this->actingAs($user)
            ->patch(route('admin.orders.delivery.update', $order), [
                'estimated_delivery_date' => '2026-09-15',
                'is_urgent' => true,
            ])
            ->assertForbidden();

        expect($order->fresh()->estimated_delivery_date)->toBeNull();
    }
});

it('rejects an invalid delivery date', function () {
    $order = makeDeliveryOrder();
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.orders.delivery.update', $order), [
            'estimated_delivery_date' => 'not-a-date',
            'is_urgent' => false,
        ])
        ->assertSessionHasErrors('estimated_delivery_date');
});

it('lets an admin or ventas toggle the sales-attention flag', function () {
    $order = makeDeliveryOrder();
    $ventas = User::factory()->role(UserRole::Ventas)->create();

    $this->actingAs($ventas)
        ->patch(route('admin.orders.sales-attention.update', $order), ['needs_sales_attention' => true])
        ->assertRedirect();

    expect($order->fresh()->needs_sales_attention)->toBeTrue();

    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.orders.sales-attention.update', $order), ['needs_sales_attention' => false])
        ->assertRedirect();

    expect($order->fresh()->needs_sales_attention)->toBeFalse();
});

it('blocks administrativo, produccion, and calidad from toggling sales attention', function () {
    foreach ([UserRole::Administrativo, UserRole::Produccion, UserRole::Calidad] as $role) {
        $order = makeDeliveryOrder();
        $user = User::factory()->role($role)->create();

        $this->actingAs($user)
            ->patch(route('admin.orders.sales-attention.update', $order), ['needs_sales_attention' => true])
            ->assertForbidden();

        expect($order->fresh()->needs_sales_attention)->toBeFalse();
    }
});
