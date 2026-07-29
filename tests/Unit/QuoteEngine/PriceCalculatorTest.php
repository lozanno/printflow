<?php

use App\Enums\InputType;
use App\Enums\ModifierType;
use App\Enums\PricingStrategy;
use App\Models\ProductTemplate;
use App\Models\Shop;
use App\QuoteEngine\Exceptions\QuoteCannotBeCalculatedException;
use App\QuoteEngine\PriceCalculator;

it('calculates a tiered price using the tier that covers the quantity', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    $catalogProduct->pricingProfile->tiers()->createMany([
        ['min_quantity' => 100, 'max_quantity' => 249, 'unit_price' => 1.50],
        ['min_quantity' => 250, 'max_quantity' => null, 'unit_price' => 1.10],
    ]);

    $result = (new PriceCalculator)->calculate($catalogProduct->fresh(), ['quantity' => '500']);

    expect($result->basePrice)->toBe(550.0)
        ->and($result->total)->toBe(550.0)
        ->and($result->modifiers)->toBeEmpty()
        ->and($result->currency)->toBe('MXN');
});

it('throws when the quantity falls outside every tier', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 100, 'max_quantity' => 249, 'unit_price' => 1.50,
    ]);

    (new PriceCalculator)->calculate($catalogProduct->fresh(), ['quantity' => 50]);
})->throws(QuoteCannotBeCalculatedException::class);

it('throws when quantity is missing entirely for a per_unit_tiered product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    (new PriceCalculator)->calculate($catalogProduct->fresh(), []);
})->throws(QuoteCannotBeCalculatedException::class);

it('calculates an area-based price', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerArea);
    attachComponent($catalogProduct->productTemplate, 'dimensions', 'Dimensiones', InputType::Dimensions);
    $catalogProduct->pricingProfile->update(['params' => ['rate_per_sqm' => 180]]);

    $result = (new PriceCalculator)->calculate($catalogProduct->fresh(), [
        'dimensions' => ['width' => 2, 'height' => 1.5],
    ]);

    expect($result->total)->toBe(540.0);
});

it('calculates an area-based price with a setup fee added on top', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerAreaWithSetup);
    attachComponent($catalogProduct->productTemplate, 'dimensions', 'Dimensiones', InputType::Dimensions);
    $catalogProduct->pricingProfile->update(['params' => ['rate_per_sqm' => 180, 'setup_fee' => 50]]);

    $result = (new PriceCalculator)->calculate($catalogProduct->fresh(), [
        'dimensions' => ['width' => 2, 'height' => 1.5],
    ]);

    expect($result->basePrice)->toBe(590.0)
        ->and($result->total)->toBe(590.0);
});

it('reads dimensions from whichever component has that role, not a fixed "dimensions" code', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerArea);
    attachComponent($catalogProduct->productTemplate, 'size_lona', 'Tamano de lona', InputType::Dimensions, options: [
        ['420x594', '420 x 594 mm (A2)'],
    ]);
    $catalogProduct->pricingProfile->update(['params' => ['rate_per_sqm' => 180]]);

    $result = (new PriceCalculator)->calculate($catalogProduct->fresh(), [
        'size_lona' => ['width' => 2, 'height' => 1.5],
    ]);

    expect($result->total)->toBe(540.0);
});

it('lets two PER_AREA products use independently coded dimensions components with their own presets', function () {
    $lona = makeCatalogProduct(PricingStrategy::PerArea);
    attachComponent($lona->productTemplate, 'size_lona', 'Tamano de lona', InputType::Dimensions, options: [
        ['420x594', '420 x 594 mm (A2)'],
    ]);
    $lona->pricingProfile->update(['params' => ['rate_per_sqm' => 180]]);

    $vinil = makeCatalogProduct(PricingStrategy::PerArea);
    attachComponent($vinil->productTemplate, 'size_vinil', 'Tamano de vinil', InputType::Dimensions, options: [
        ['300x300', '300 x 300 mm'],
    ]);
    $vinil->pricingProfile->update(['params' => ['rate_per_sqm' => 90]]);

    $lonaComponent = $lona->fresh()->productTemplate->components->firstWhere('code', 'size_lona');
    $vinilComponent = $vinil->fresh()->productTemplate->components->firstWhere('code', 'size_vinil');

    expect($lonaComponent->options->pluck('value')->all())->toBe(['420x594'])
        ->and($vinilComponent->options->pluck('value')->all())->toBe(['300x300']);
});

it('throws when an area strategy is missing its rate', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerArea);
    attachComponent($catalogProduct->productTemplate, 'dimensions', 'Dimensiones', InputType::Dimensions);

    (new PriceCalculator)->calculate($catalogProduct->fresh(), [
        'dimensions' => ['width' => 2, 'height' => 1.5],
    ]);
})->throws(QuoteCannotBeCalculatedException::class);

it('applies a fixed_add modifier once regardless of quantity', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $template = $catalogProduct->productTemplate;
    $finish = attachComponent($template, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);

    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 100, 'max_quantity' => null, 'unit_price' => 1.00,
    ]);

    $catalogProduct->pricingProfile->optionModifiers()->create([
        'component_option_id' => $finish->options->first()->id,
        'modifier_type' => ModifierType::FixedAdd,
        'value' => 25,
    ]);

    $result = (new PriceCalculator)->calculate($catalogProduct->fresh(), [
        'quantity' => 100,
        'finish' => 'gloss',
    ]);

    expect($result->basePrice)->toBe(100.0)
        ->and($result->modifiers)->toHaveCount(1)
        ->and($result->modifiers[0]->amount)->toBe(25.0)
        ->and($result->modifiers[0]->label)->toBe('Acabado: Laminado brillante')
        ->and($result->modifiers[0]->type)->toBe(ModifierType::FixedAdd)
        ->and($result->total)->toBe(125.0);
});

it('scales a per_unit_add modifier by the quantity', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $template = $catalogProduct->productTemplate;
    $finish = attachComponent($template, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);

    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 100, 'max_quantity' => null, 'unit_price' => 1.00,
    ]);

    $catalogProduct->pricingProfile->optionModifiers()->create([
        'component_option_id' => $finish->options->first()->id,
        'modifier_type' => ModifierType::PerUnitAdd,
        'value' => 0.5,
    ]);

    $result = (new PriceCalculator)->calculate($catalogProduct->fresh(), [
        'quantity' => 100,
        'finish' => 'gloss',
    ]);

    expect($result->modifiers[0]->amount)->toBe(50.0)
        ->and($result->total)->toBe(150.0);
});

it('supports a negative per_unit_add value as a volume discount', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $template = $catalogProduct->productTemplate;
    $quantityTier = attachComponent($template, 'volume', 'Volumen', InputType::Choice, options: [
        ['high', 'Mas de 500'],
    ]);

    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1.00,
    ]);

    $catalogProduct->pricingProfile->optionModifiers()->create([
        'component_option_id' => $quantityTier->options->first()->id,
        'modifier_type' => ModifierType::PerUnitAdd,
        'value' => -0.15,
    ]);

    $result = (new PriceCalculator)->calculate($catalogProduct->fresh(), [
        'quantity' => 500,
        'volume' => 'high',
    ]);

    expect($result->basePrice)->toBe(500.0)
        ->and($result->modifiers[0]->amount)->toBe(-75.0)
        ->and($result->total)->toBe(425.0);
});

it('compounds a percent_multiply modifier on top of additive modifiers', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $template = $catalogProduct->productTemplate;
    $finish = attachComponent($template, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);
    $turnaround = attachComponent($template, 'turnaround', 'Entrega', InputType::Choice, options: [
        ['rush', 'Urgente'],
    ]);

    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 100, 'max_quantity' => null, 'unit_price' => 1.00,
    ]);

    $catalogProduct->pricingProfile->optionModifiers()->createMany([
        [
            'component_option_id' => $finish->options->first()->id,
            'modifier_type' => ModifierType::FixedAdd,
            'value' => 20,
        ],
        [
            'component_option_id' => $turnaround->options->first()->id,
            'modifier_type' => ModifierType::PercentMultiply,
            'value' => 0.10,
        ],
    ]);

    $result = (new PriceCalculator)->calculate($catalogProduct->fresh(), [
        'quantity' => 100,
        'finish' => 'gloss',
        'turnaround' => 'rush',
    ]);

    // base 100 -> +20 fixed = 120 -> +10% of 120 = 12 -> 132
    expect($result->basePrice)->toBe(100.0)
        ->and($result->modifiers)->toHaveCount(2)
        ->and($result->modifiers[0]->amount)->toBe(20.0)
        ->and($result->modifiers[1]->amount)->toBe(12.0)
        ->and($result->total)->toBe(132.0);
});

it('throws when a required component has no selection', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    attachComponent($catalogProduct->productTemplate, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    (new PriceCalculator)->calculate($catalogProduct->fresh(), ['quantity' => 10]);
})->throws(QuoteCannotBeCalculatedException::class);

it('ignores an optional component that has no selection', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $template = $catalogProduct->productTemplate;
    attachComponent($template, 'notes', 'Notas', InputType::Number, required: false);

    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 2,
    ]);

    $result = (new PriceCalculator)->calculate($catalogProduct->fresh(), ['quantity' => 10]);

    expect($result->total)->toBe(20.0);
});

it('throws when a choice selection does not match any configured option', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    attachComponent($catalogProduct->productTemplate, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    (new PriceCalculator)->calculate($catalogProduct->fresh(), [
        'quantity' => 1,
        'finish' => 'nonexistent',
    ]);
})->throws(QuoteCannotBeCalculatedException::class);

it('throws when the catalog product has no pricing profile', function () {
    $shop = Shop::create(['name' => 'Test Shop', 'slug' => 'test-shop-'.uniqid(), 'currency' => 'MXN']);
    $template = ProductTemplate::create([
        'code' => 'template-'.uniqid(),
        'name' => 'Test Template',
        'pricing_strategy' => PricingStrategy::PerUnitTiered,
    ]);
    $catalogProduct = $shop->catalogProducts()->create([
        'product_template_id' => $template->id,
        'is_active' => true,
    ]);

    (new PriceCalculator)->calculate($catalogProduct->fresh(), ['quantity' => 100]);
})->throws(QuoteCannotBeCalculatedException::class);

it('applies a tier adjustment_percent to the base price before option modifiers', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1.00, 'adjustment_percent' => -10,
    ]);

    $result = (new PriceCalculator)->calculate($catalogProduct->fresh(), ['quantity' => 100]);

    // 100 * 1.00 = 100, then -10% = 90
    expect($result->basePrice)->toBe(90.0)
        ->and($result->total)->toBe(90.0);
});

it('builds a full tier table with option modifiers applied to every row', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $finish = attachComponent($catalogProduct->productTemplate, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);
    $catalogProduct->pricingProfile->tiers()->createMany([
        ['min_quantity' => 100, 'max_quantity' => 249, 'unit_price' => 1.50],
        ['min_quantity' => 250, 'max_quantity' => null, 'unit_price' => 1.10, 'adjustment_percent' => 10],
    ]);
    $catalogProduct->pricingProfile->optionModifiers()->create([
        'component_option_id' => $finish->options->first()->id,
        'modifier_type' => ModifierType::FixedAdd,
        'value' => 20,
    ]);

    $rows = (new PriceCalculator)->calculateTierTable($catalogProduct->fresh(), ['finish' => 'gloss']);

    // tier 1: 100 * 1.50 = 150 -> +20 fixed = 170
    // tier 2: 250 * (1.10 * 1.10) = 302.5 -> +20 fixed = 322.5
    expect($rows)->toHaveCount(2)
        ->and($rows[0]['min_quantity'])->toBe(100)
        ->and($rows[0]['total'])->toBe(170.0)
        ->and($rows[1]['min_quantity'])->toBe(250)
        ->and($rows[1]['total'])->toBe(322.5);
});

it('ignores missing required selections when building the tier table preview', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    attachComponent($catalogProduct->productTemplate, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1.00,
    ]);

    $rows = (new PriceCalculator)->calculateTierTable($catalogProduct->fresh(), []);

    expect($rows)->toHaveCount(1)
        ->and($rows[0]['total'])->toBe(1.0);
});

it('returns an empty tier table for a non-per_unit_tiered product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerArea);
    $catalogProduct->pricingProfile->update(['params' => ['rate_per_sqm' => 180]]);

    $rows = (new PriceCalculator)->calculateTierTable($catalogProduct->fresh(), []);

    expect($rows)->toBe([]);
});
