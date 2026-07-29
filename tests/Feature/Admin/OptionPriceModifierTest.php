<?php

use App\Enums\InputType;
use App\Enums\PricingStrategy;
use App\Models\User;

it('creates a price modifier for an option', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $finish = attachComponent($catalogProduct->productTemplate, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);
    $option = $finish->options->first();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.catalog-products.option-modifiers.store', $catalogProduct), [
            'component_option_id' => $option->id,
            'modifier_type' => 'FIXED_ADD',
            'value' => 25,
        ])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    expect($catalogProduct->pricingProfile->optionModifiers()->where('component_option_id', $option->id)->exists())->toBeTrue();
});

it('updates a price modifier value in place instead of requiring delete and recreate', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $finish = attachComponent($catalogProduct->productTemplate, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);
    $option = $finish->options->first();
    $modifier = $catalogProduct->pricingProfile->optionModifiers()->create([
        'component_option_id' => $option->id,
        'modifier_type' => 'FIXED_ADD',
        'value' => 25,
    ]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.catalog-products.option-modifiers.update', [$catalogProduct, $modifier]), [
            'modifier_type' => 'PERCENT_MULTIPLY',
            'value' => 0.1,
        ])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    $modifier->refresh();

    expect((float) $modifier->value)->toBe(0.1)
        ->and($modifier->modifier_type->value)->toBe('PERCENT_MULTIPLY')
        ->and($catalogProduct->pricingProfile->optionModifiers()->count())->toBe(1);
});

it('deletes a price modifier', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $finish = attachComponent($catalogProduct->productTemplate, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);
    $option = $finish->options->first();
    $modifier = $catalogProduct->pricingProfile->optionModifiers()->create([
        'component_option_id' => $option->id,
        'modifier_type' => 'FIXED_ADD',
        'value' => 25,
    ]);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.catalog-products.option-modifiers.destroy', [$catalogProduct, $modifier]))
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    expect($catalogProduct->pricingProfile->optionModifiers()->count())->toBe(0);
});
