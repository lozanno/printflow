<?php

use App\Enums\InputType;
use App\Enums\PricingStrategy;

it('returns a computed total for a valid tiered selection', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    attachComponent($catalogProduct->productTemplate, 'quantity', 'Cantidad', InputType::Choice, options: [
        ['500', '500 piezas'],
    ]);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1.10,
    ]);

    $this->postJson("/{$catalogProduct->slug}/cotizar", [
        'selections' => ['quantity' => '500'],
    ])
        ->assertOk()
        ->assertJson([
            'base_price' => 550.0,
            'total' => 550.0,
            'currency' => 'MXN',
        ]);
});

it('returns a computed total for a valid area-based selection', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerAreaWithSetup);
    attachComponent($catalogProduct->productTemplate, 'dimensions', 'Dimensiones', InputType::Dimensions);
    $catalogProduct->pricingProfile->update(['params' => ['rate_per_sqm' => 180, 'setup_fee' => 50]]);

    $this->postJson("/{$catalogProduct->slug}/cotizar", [
        'selections' => ['dimensions' => ['width' => 2, 'height' => 1.5]],
    ])
        ->assertOk()
        ->assertJson(['total' => 590.0]);
});

it('returns a clean 422 when a required selection is missing', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    attachComponent($catalogProduct->productTemplate, 'quantity', 'Cantidad', InputType::Number);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->postJson("/{$catalogProduct->slug}/cotizar", ['selections' => []])
        ->assertStatus(422)
        ->assertJsonStructure(['message']);
});

it('returns a clean 422 for a choice value that does not exist', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    attachComponent($catalogProduct->productTemplate, 'quantity', 'Cantidad', InputType::Choice, options: [
        ['100', '100 piezas'],
    ]);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->postJson("/{$catalogProduct->slug}/cotizar", [
        'selections' => ['quantity' => 'not-a-real-option'],
    ])->assertStatus(422);
});

it('404s when quoting an inactive product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['is_active' => false]);

    $this->postJson("/{$catalogProduct->slug}/cotizar", ['selections' => []])
        ->assertNotFound();
});
