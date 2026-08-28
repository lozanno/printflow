<?php

use App\Enums\InputType;
use App\Enums\PricingStrategy;

it('shows the checkout page with the computed total for valid selections', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1.10,
    ]);

    $this->get("/{$catalogProduct->slug}/pedido?selections[quantity]=500")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('checkout/show')
            ->where('catalogProduct.slug', $catalogProduct->slug)
            ->where('quote.total', 550)
            ->where('selectionSummary', ['Cantidad: 500 unidades'])
        );
});

it('describes choice and dimension selections in plain language', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerAreaWithSetup);
    attachComponent($catalogProduct->productTemplate, 'dimensions', 'Dimensiones', InputType::Dimensions);
    $catalogProduct->pricingProfile->update(['params' => ['rate_per_sqm' => 180, 'setup_fee' => 50]]);

    $this->get("/{$catalogProduct->slug}/pedido?selections[dimensions][width]=2&selections[dimensions][height]=1.5")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('checkout/show')
            ->where('selectionSummary', ['Dimensiones: 2m x 1.5m'])
        );
});

it('redirects back to the product page when selections cannot be quoted', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->get("/{$catalogProduct->slug}/pedido")
        ->assertRedirect("/{$catalogProduct->slug}");
});

it('404s the checkout page for an inactive product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['is_active' => false]);

    $this->get("/{$catalogProduct->slug}/pedido?selections[quantity]=1")
        ->assertNotFound();
});
