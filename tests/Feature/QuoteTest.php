<?php

use App\Enums\InputType;
use App\Enums\PricingStrategy;

it('returns a computed total for a valid tiered selection', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
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

it('returns a clean 422 when the quantity is missing', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->postJson("/{$catalogProduct->slug}/cotizar", ['selections' => []])
        ->assertStatus(422)
        ->assertJsonStructure(['message']);
});

it('returns a clean 422 for a choice value that does not exist', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    attachComponent($catalogProduct->productTemplate, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->postJson("/{$catalogProduct->slug}/cotizar", [
        'selections' => ['quantity' => 100, 'finish' => 'not-a-real-option'],
    ])->assertStatus(422);
});

it('404s when quoting an inactive product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['is_active' => false]);

    $this->postJson("/{$catalogProduct->slug}/cotizar", ['selections' => []])
        ->assertNotFound();
});

it('returns a live tier table reflecting the currently selected options', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $finish = attachComponent($catalogProduct->productTemplate, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 100, 'max_quantity' => null, 'unit_price' => 1.00,
    ]);
    $catalogProduct->pricingProfile->optionModifiers()->create([
        'component_option_id' => $finish->options->first()->id,
        'modifier_type' => 'FIXED_ADD',
        'value' => 20,
    ]);

    $this->postJson("/{$catalogProduct->slug}/rangos-precio", [
        'selections' => ['finish' => 'gloss'],
    ])
        ->assertOk()
        ->assertJson([
            'tiers' => [
                ['min_quantity' => 100, 'max_quantity' => null, 'unit_price' => 1.2, 'total' => 120.0],
            ],
        ]);
});

it('never throws from the tier table endpoint even with no selections at all', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    $this->postJson("/{$catalogProduct->slug}/rangos-precio", ['selections' => []])
        ->assertOk()
        ->assertJsonCount(1, 'tiers');
});

it('404s for the tier table endpoint on an inactive product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['is_active' => false]);

    $this->postJson("/{$catalogProduct->slug}/rangos-precio", ['selections' => []])
        ->assertNotFound();
});
