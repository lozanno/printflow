<?php

use App\Enums\InputType;
use App\Enums\ModifierType;
use App\Enums\PricingStrategy;
use App\Models\CatalogProduct;
use App\Models\Component;
use App\Models\ProductTemplate;
use App\Models\Shop;
use App\QuoteEngine\Exceptions\QuoteCannotBeCalculatedException;
use App\QuoteEngine\PriceCalculator;

function makeCatalogProduct(PricingStrategy $strategy): CatalogProduct
{
    $shop = Shop::create([
        'name' => 'Test Shop',
        'slug' => 'test-shop-'.uniqid(),
        'currency' => 'MXN',
    ]);

    $template = ProductTemplate::create([
        'code' => 'template-'.uniqid(),
        'name' => 'Test Template',
        'pricing_strategy' => $strategy,
    ]);

    $catalogProduct = $shop->catalogProducts()->create([
        'product_template_id' => $template->id,
        'is_active' => true,
    ]);

    $catalogProduct->pricingProfile()->create();

    return $catalogProduct->fresh();
}

/**
 * @param  list<array{0: string, 1: string}>  $options
 */
function attachComponent(
    ProductTemplate $template,
    string $code,
    string $label,
    InputType $type,
    bool $required = true,
    array $options = [],
): Component {
    $component = Component::create(['code' => $code, 'label' => $label, 'input_type' => $type]);

    $template->components()->attach($component->id, [
        'sort_order' => ($template->templateComponents()->max('sort_order') ?? 0) + 1,
        'is_required' => $required,
    ]);

    foreach ($options as $i => [$value, $optionLabel]) {
        $component->options()->create(['value' => $value, 'label' => $optionLabel, 'sort_order' => $i]);
    }

    return $component->fresh();
}

it('calculates a tiered price using the tier that covers the quantity', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    attachComponent($catalogProduct->productTemplate, 'quantity', 'Cantidad', InputType::Choice, options: [
        ['100', '100 piezas'],
        ['500', '500 piezas'],
    ]);

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
    attachComponent($catalogProduct->productTemplate, 'quantity', 'Cantidad', InputType::Number);

    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 100, 'max_quantity' => 249, 'unit_price' => 1.50,
    ]);

    (new PriceCalculator)->calculate($catalogProduct->fresh(), ['quantity' => 50]);
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
    attachComponent($template, 'quantity', 'Cantidad', InputType::Number);
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
    attachComponent($template, 'quantity', 'Cantidad', InputType::Number);
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
    attachComponent($template, 'quantity', 'Cantidad', InputType::Number);
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
    attachComponent($template, 'quantity', 'Cantidad', InputType::Number);
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
    attachComponent($catalogProduct->productTemplate, 'quantity', 'Cantidad', InputType::Number);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    (new PriceCalculator)->calculate($catalogProduct->fresh(), []);
})->throws(QuoteCannotBeCalculatedException::class);

it('ignores an optional component that has no selection', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $template = $catalogProduct->productTemplate;
    attachComponent($template, 'quantity', 'Cantidad', InputType::Number);
    attachComponent($template, 'notes', 'Notas', InputType::Number, required: false);

    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 2,
    ]);

    $result = (new PriceCalculator)->calculate($catalogProduct->fresh(), ['quantity' => 10]);

    expect($result->total)->toBe(20.0);
});

it('throws when a choice selection does not match any configured option', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    attachComponent($catalogProduct->productTemplate, 'quantity', 'Cantidad', InputType::Choice, options: [
        ['100', '100 piezas'],
    ]);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1,
    ]);

    (new PriceCalculator)->calculate($catalogProduct->fresh(), ['quantity' => 'nonexistent']);
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
