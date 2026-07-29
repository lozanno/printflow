<?php

use App\Enums\PricingStrategy;
use App\Models\User;

it('redirects guests for every pricing tier route', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $tier = $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->post(route('admin.catalog-products.pricing-tiers.store', $catalogProduct))->assertRedirect(route('login'));
    $this->put(route('admin.catalog-products.pricing-tiers.update', [$catalogProduct, $tier]))->assertRedirect(route('login'));
    $this->delete(route('admin.catalog-products.pricing-tiers.destroy', [$catalogProduct, $tier]))->assertRedirect(route('login'));
});

it('creates a pricing tier with an adjustment_percent', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.catalog-products.pricing-tiers.store', $catalogProduct), [
            'min_quantity' => 100,
            'max_quantity' => null,
            'unit_price' => 1.5,
            'adjustment_percent' => -5,
        ])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    $tier = $catalogProduct->pricingProfile->tiers()->first();

    expect((float) $tier->adjustment_percent)->toBe(-5.0);
});

it('updates a pricing tier in place instead of requiring delete and recreate', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $tier = $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.catalog-products.pricing-tiers.update', [$catalogProduct, $tier]), [
            'min_quantity' => 1,
            'max_quantity' => 99,
            'unit_price' => 2.5,
            'adjustment_percent' => 15,
        ])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    $tier->refresh();

    expect((float) $tier->unit_price)->toBe(2.5)
        ->and($tier->max_quantity)->toBe(99)
        ->and((float) $tier->adjustment_percent)->toBe(15.0)
        ->and($catalogProduct->pricingProfile->tiers()->count())->toBe(1);
});

it('deletes a pricing tier', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $tier = $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.catalog-products.pricing-tiers.destroy', [$catalogProduct, $tier]))
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    expect($catalogProduct->pricingProfile->tiers()->count())->toBe(0);
});
