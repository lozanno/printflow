<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductionStage;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;

function makeDetailOrder(): Order
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
    $order = makeDetailOrder();

    $this->get(route('admin.orders.show', $order))->assertRedirect(route('login'));
});

it('blocks a user without a role', function () {
    $order = makeDetailOrder();
    $user = User::factory()->withoutRole()->create();

    $this->actingAs($user)
        ->get(route('admin.orders.show', $order))
        ->assertForbidden();
});

it('shows the order detail page with a chronological timeline to any assigned role', function () {
    $order = makeDetailOrder();
    $admin = User::factory()->create();
    $operator = User::factory()->role(UserRole::Produccion)->create();

    $this->travel(1)->seconds();

    $this->actingAs($admin)
        ->patch(route('admin.orders.production-stage.update', $order), ['production_stage' => 'IN_PRODUCTION']);

    $ventas = User::factory()->role(UserRole::Ventas)->create();

    $this->actingAs($ventas)
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/orders/show')
            ->where('order.id', $order->id)
            ->where('order.customer_email', 'ana@example.com')
            ->where('order.production_stage', 'IN_PRODUCTION')
            ->has('events', 2)
            ->where('events.0.type', 'stage_change')
            ->where('events.0.to_stage', 'IN_PRODUCTION')
        );

    expect($operator)->not->toBeNull();
});

it('lets any assigned role add a free-text note that appears in the timeline', function () {
    $order = makeDetailOrder();
    $ventas = User::factory()->role(UserRole::Ventas)->create();

    $this->travel(1)->seconds();

    $this->actingAs($ventas)
        ->post(route('admin.orders.notes.store', $order), ['body' => 'Cliente pidio cambiar el color.'])
        ->assertRedirect();

    expect($order->notes()->count())->toBe(1);
    $note = $order->notes()->first();
    expect($note->body)->toBe('Cliente pidio cambiar el color.');
    expect($note->user_id)->toBe($ventas->id);

    $this->actingAs($ventas)
        ->get(route('admin.orders.show', $order))
        ->assertInertia(fn ($page) => $page
            ->has('events', 2)
            ->where('events.0.type', 'note')
            ->where('events.0.body', 'Cliente pidio cambiar el color.')
            ->where('events.0.user_name', $ventas->name)
        );
});

it('rejects an empty note', function () {
    $order = makeDetailOrder();
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.orders.notes.store', $order), ['body' => ''])
        ->assertSessionHasErrors('body');
});

it('merges stage changes, quality check, and notes into one timeline sorted newest first', function () {
    $order = makeDetailOrder();
    $admin = User::factory()->create();
    $calidad = User::factory()->role(UserRole::Calidad)->create();

    $this->travel(1)->seconds();

    $this->actingAs($admin)
        ->patch(route('admin.orders.production-stage.update', $order), ['production_stage' => 'IN_PRODUCTION']);

    $this->travel(1)->seconds();

    $this->actingAs($admin)
        ->post(route('admin.orders.notes.store', $order), ['body' => 'Primera nota.']);

    $this->travel(1)->seconds();

    $this->actingAs($calidad)
        ->patch(route('admin.orders.quality-check.update', $order), ['passed' => true]);

    $this->actingAs($admin)
        ->get(route('admin.orders.show', $order))
        ->assertInertia(fn ($page) => $page
            ->has('events', 4)
            ->where('events.0.type', 'quality_check')
            ->where('events.1.type', 'note')
            ->where('events.2.type', 'stage_change')
            ->where('events.2.to_stage', 'IN_PRODUCTION')
            ->where('events.3.type', 'stage_change')
            ->where('events.3.to_stage', 'PENDING')
        );
});
