<?php

use App\Enums\PricingStrategy;
use App\Models\Category;
use App\Models\User;

it('redirects guests to login for every category route', function () {
    $shop = makeShop();
    $category = $shop->categories()->create(['name' => 'Promos', 'slug' => 'promos']);

    $this->get(route('admin.categories.index'))->assertRedirect(route('login'));
    $this->get(route('admin.categories.create'))->assertRedirect(route('login'));
    $this->get(route('admin.categories.edit', $category))->assertRedirect(route('login'));
    $this->post(route('admin.categories.store'))->assertRedirect(route('login'));
    $this->put(route('admin.categories.update', $category))->assertRedirect(route('login'));
    $this->delete(route('admin.categories.destroy', $category))->assertRedirect(route('login'));
});

it('lists the shop categories', function () {
    $shop = makeShop();
    $shop->categories()->create(['name' => 'Impresos promocionales', 'slug' => 'impresos-promocionales']);

    $this->actingAs(User::factory()->create())
        ->get(route('admin.categories.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('admin/categories/index')
            ->has('categories', 1)
            ->where('categories.0.name', 'Impresos promocionales')
        );
});

it('creates a category', function () {
    $shop = makeShop();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.categories.store'), [
            'name' => 'Impresos promocionales',
            'slug' => 'impresos-promocionales',
        ])
        ->assertRedirect(route('admin.categories.index'));

    expect($shop->categories()->where('slug', 'impresos-promocionales')->exists())->toBeTrue();
});

it('rejects a duplicate slug within the same shop', function () {
    $shop = makeShop();
    $shop->categories()->create(['name' => 'Promos', 'slug' => 'promos']);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.categories.store'), ['name' => 'Otras promos', 'slug' => 'promos'])
        ->assertSessionHasErrors('slug');
});

it('rejects a category slug that collides with an existing catalog product slug', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['slug' => 'tarjetas-de-presentacion']);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.categories.store'), [
            'name' => 'Tarjetas de presentacion',
            'slug' => 'tarjetas-de-presentacion',
        ])
        ->assertSessionHasErrors('slug');
});

it('updates a category', function () {
    $shop = makeShop();
    $category = $shop->categories()->create(['name' => 'Promos', 'slug' => 'promos']);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.categories.update', $category), [
            'name' => 'Ofertas',
            'slug' => 'ofertas',
        ])
        ->assertRedirect(route('admin.categories.edit', $category));

    expect($category->fresh()->slug)->toBe('ofertas');
});

it('deletes a category', function () {
    $shop = makeShop();
    $category = $shop->categories()->create(['name' => 'Promos', 'slug' => 'promos']);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.categories.destroy', $category))
        ->assertRedirect(route('admin.categories.index'));

    expect(Category::find($category->id))->toBeNull();
});
