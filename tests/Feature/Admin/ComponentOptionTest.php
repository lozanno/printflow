<?php

use App\Models\Component;
use App\Models\User;

function makeChoiceComponent(): Component
{
    return Component::create([
        'code' => 'finish-'.uniqid(),
        'label' => 'Acabado',
        'input_type' => 'CHOICE',
    ]);
}

it('redirects guests for every option route', function () {
    $component = makeChoiceComponent();
    $option = $component->options()->create(['value' => 'gloss', 'label' => 'Brillante', 'sort_order' => 0]);

    $this->post(route('admin.components.options.store', $component))->assertRedirect(route('login'));
    $this->put(route('admin.components.options.update', [$component, $option]))->assertRedirect(route('login'));
    $this->delete(route('admin.components.options.destroy', [$component, $option]))->assertRedirect(route('login'));
});

it('creates an option for a component', function () {
    $component = makeChoiceComponent();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.components.options.store', $component), [
            'value' => 'gloss',
            'label' => 'Laminado brillante',
        ])
        ->assertRedirect(route('admin.components.edit', $component));

    expect($component->options()->where('value', 'gloss')->exists())->toBeTrue();
});

it('updates an option in place instead of requiring delete and recreate', function () {
    $component = makeChoiceComponent();
    $option = $component->options()->create(['value' => 'gloss', 'label' => 'Brillante', 'sort_order' => 0]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.components.options.update', [$component, $option]), [
            'value' => 'matte',
            'label' => 'Mate',
        ])
        ->assertRedirect(route('admin.components.edit', $component));

    $option->refresh();

    expect($option->value)->toBe('matte')
        ->and($option->label)->toBe('Mate')
        ->and($component->options()->count())->toBe(1);
});

it('rejects a duplicate value within the same component on update', function () {
    $component = makeChoiceComponent();
    $component->options()->create(['value' => 'gloss', 'label' => 'Brillante', 'sort_order' => 0]);
    $matte = $component->options()->create(['value' => 'matte', 'label' => 'Mate', 'sort_order' => 1]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.components.options.update', [$component, $matte]), [
            'value' => 'gloss',
            'label' => 'Mate',
        ])
        ->assertSessionHasErrors('value');
});

it('deletes an option', function () {
    $component = makeChoiceComponent();
    $option = $component->options()->create(['value' => 'gloss', 'label' => 'Brillante', 'sort_order' => 0]);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.components.options.destroy', [$component, $option]))
        ->assertRedirect(route('admin.components.edit', $component));

    expect($component->options()->count())->toBe(0);
});

it('moves an option up by swapping sort_order with its previous sibling', function () {
    $component = makeChoiceComponent();
    $first = $component->options()->create(['value' => 'gloss', 'label' => 'Brillante', 'sort_order' => 1]);
    $second = $component->options()->create(['value' => 'matte', 'label' => 'Mate', 'sort_order' => 2]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.components.options.move', [$component, $second]), ['direction' => 'up'])
        ->assertRedirect(route('admin.components.edit', $component));

    expect($first->fresh()->sort_order)->toBe(2)
        ->and($second->fresh()->sort_order)->toBe(1);
});

it('does not move the first option further up', function () {
    $component = makeChoiceComponent();
    $first = $component->options()->create(['value' => 'gloss', 'label' => 'Brillante', 'sort_order' => 1]);
    $second = $component->options()->create(['value' => 'matte', 'label' => 'Mate', 'sort_order' => 2]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.components.options.move', [$component, $first]), ['direction' => 'up'])
        ->assertRedirect(route('admin.components.edit', $component));

    expect($first->fresh()->sort_order)->toBe(1)
        ->and($second->fresh()->sort_order)->toBe(2);
});

it('moves an option down by swapping sort_order with its next sibling', function () {
    $component = makeChoiceComponent();
    $first = $component->options()->create(['value' => 'gloss', 'label' => 'Brillante', 'sort_order' => 1]);
    $second = $component->options()->create(['value' => 'matte', 'label' => 'Mate', 'sort_order' => 2]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.components.options.move', [$component, $first]), ['direction' => 'down'])
        ->assertRedirect(route('admin.components.edit', $component));

    expect($first->fresh()->sort_order)->toBe(2)
        ->and($second->fresh()->sort_order)->toBe(1);
});
