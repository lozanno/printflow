<?php

use App\Enums\PricingStrategy;
use App\Models\User;

it('redirects guests to login', function () {
    $this->get(route('admin.orders.index'))->assertRedirect(route('login'));
});

it('lists placed orders', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1.10,
    ]);

    $this->post("/{$catalogProduct->slug}/pedido", [
        'customer' => ['name' => 'Ana Garcia', 'email' => 'ana@example.com'],
        'delivery_type' => 'PICKUP',
        'payment_method' => 'card',
        'selections' => ['quantity' => 500],
    ]);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.orders.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/orders/index')
            ->has('orders', 1)
            ->where('orders.0.customer_name', 'Ana Garcia')
            ->where('orders.0.needs_sales_attention', false)
            ->where('orders.0.estimated_delivery_date', null)
            ->where('orders.0.is_urgent', false)
        );
});
