<?php

use App\Enums\InputType;
use App\Enums\PricingStrategy;
use App\Models\ProductTemplate;
use App\Models\User;

function makeTemplate(): ProductTemplate
{
    return ProductTemplate::create([
        'code' => 'template-'.uniqid(),
        'name' => 'Test Template',
        'pricing_strategy' => PricingStrategy::PerUnitTiered,
    ]);
}

it('redirects guests for the move route', function () {
    $template = makeTemplate();
    $component = attachComponent($template, 'finish', 'Acabado', InputType::Number);

    $this->patch(route('admin.product-templates.components.move', [$template, $component]))
        ->assertRedirect(route('login'));
});

it('moves a component up by swapping sort_order with its previous sibling', function () {
    $template = makeTemplate();
    $first = attachComponent($template, 'finish', 'Acabado', InputType::Number);
    $second = attachComponent($template, 'notes', 'Notas', InputType::Number);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.product-templates.components.move', [$template, $second]), ['direction' => 'up'])
        ->assertRedirect(route('admin.product-templates.edit', $template));

    $firstPivot = $template->templateComponents()->where('component_id', $first->id)->first();
    $secondPivot = $template->templateComponents()->where('component_id', $second->id)->first();

    expect($secondPivot->sort_order)->toBeLessThan($firstPivot->sort_order);
});

it('does not move the first component further up', function () {
    $template = makeTemplate();
    $first = attachComponent($template, 'finish', 'Acabado', InputType::Number);
    attachComponent($template, 'notes', 'Notas', InputType::Number);

    $beforePivot = $template->templateComponents()->where('component_id', $first->id)->first();

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.product-templates.components.move', [$template, $first]), ['direction' => 'up'])
        ->assertRedirect(route('admin.product-templates.edit', $template));

    $afterPivot = $template->templateComponents()->where('component_id', $first->id)->first();

    expect($afterPivot->sort_order)->toBe($beforePivot->sort_order);
});

it('moves a component down by swapping sort_order with its next sibling', function () {
    $template = makeTemplate();
    $first = attachComponent($template, 'finish', 'Acabado', InputType::Number);
    $second = attachComponent($template, 'notes', 'Notas', InputType::Number);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.product-templates.components.move', [$template, $first]), ['direction' => 'down'])
        ->assertRedirect(route('admin.product-templates.edit', $template));

    $firstPivot = $template->templateComponents()->where('component_id', $first->id)->first();
    $secondPivot = $template->templateComponents()->where('component_id', $second->id)->first();

    expect($firstPivot->sort_order)->toBeGreaterThan($secondPivot->sort_order);
});
