<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PricingStrategy;
use App\Models\Customer;
use App\Models\Order;

function validOrderPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'customer' => [
            'name' => 'Ana Garcia',
            'email' => 'ana@example.com',
            'phone' => '5512345678',
        ],
        'delivery_type' => 'PICKUP',
        'payment_method' => 'card',
        'selections' => ['quantity' => 500],
    ], $overrides);
}

it('places a pickup order and marks it paid immediately', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1.10,
    ]);

    $response = $this->post("/{$catalogProduct->slug}/pedido", validOrderPayload());

    $order = Order::sole();
    $response->assertRedirect("/pedidos/{$order->id}/confirmacion");

    expect($order->status)->toBe(OrderStatus::Paid);
    expect((float) $order->total)->toBe(550.0);
    expect($order->customer->email)->toBe('ana@example.com');
    expect($order->items()->sole()->catalog_product_id)->toBe($catalogProduct->id);
    expect($order->shippingAddress)->toBeNull();

    $payment = $order->payments()->sole();
    expect($payment->status)->toBe(PaymentStatus::Succeeded);
    expect($payment->method->value)->toBe('card');
});

it('stores a shipping address for a ship order', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->post("/{$catalogProduct->slug}/pedido", validOrderPayload([
        'delivery_type' => 'SHIP',
        'shipping' => [
            'recipient_name' => 'Ana Garcia',
            'phone' => '5512345678',
            'line1' => 'Calle Falsa 123',
            'city' => 'CDMX',
            'state' => 'CDMX',
            'postal_code' => '01000',
        ],
    ]))->assertRedirect();

    $order = Order::sole();
    expect($order->shippingAddress->line1)->toBe('Calle Falsa 123');
});

it('reuses the existing customer record for a repeat email', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->post("/{$catalogProduct->slug}/pedido", validOrderPayload())->assertRedirect();
    $this->post("/{$catalogProduct->slug}/pedido", validOrderPayload())->assertRedirect();

    expect(Customer::count())->toBe(1);
    expect(Order::count())->toBe(2);
});

it('fails validation instead of creating an order when the quote cannot be calculated', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 100, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->post("/{$catalogProduct->slug}/pedido", validOrderPayload(['selections' => ['quantity' => 1]]))
        ->assertSessionHasErrors('selections');

    expect(Order::count())->toBe(0);
});

it('404s when ordering an inactive product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['is_active' => false]);

    $this->post("/{$catalogProduct->slug}/pedido", validOrderPayload())->assertNotFound();
});

it('shows the order confirmation page', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->post("/{$catalogProduct->slug}/pedido", validOrderPayload());
    $order = Order::sole();

    $this->get("/pedidos/{$order->id}/confirmacion")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('checkout/confirmation')
            ->where('order.id', $order->id)
            ->where('order.status', 'PAID')
        );
});
