<?php

use App\Models\Component;
use App\Models\User;

it('rejects creating a component with the reserved quantity code', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.components.store'), [
            'code' => 'quantity',
            'label' => 'Cantidad',
            'input_type' => 'NUMBER',
        ])
        ->assertSessionHasErrors('code');

    expect(Component::where('code', 'quantity')->exists())->toBeFalse();
});

it('rejects renaming a component to the reserved quantity code', function () {
    $component = Component::create(['code' => 'finish-'.uniqid(), 'label' => 'Acabado', 'input_type' => 'CHOICE']);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.components.update', $component), [
            'code' => 'quantity',
            'label' => $component->label,
            'input_type' => 'CHOICE',
        ])
        ->assertSessionHasErrors('code');
});

it('creates a component with a normal code', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('admin.components.store'), [
            'code' => 'finish-'.uniqid(),
            'label' => 'Acabado',
            'input_type' => 'CHOICE',
        ])
        ->assertRedirect(route('admin.components.index'));
});
