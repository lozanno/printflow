<?php

use App\Enums\PricingStrategy;
use App\Models\Page;
use App\Models\User;

it('redirects guests for every page route', function () {
    $shop = makeShop();
    $page = $shop->pages()->create(['title' => 'Quienes somos', 'slug' => 'quienes-somos']);

    $this->get(route('admin.pages.index'))->assertRedirect(route('login'));
    $this->get(route('admin.pages.create'))->assertRedirect(route('login'));
    $this->get(route('admin.pages.edit', $page))->assertRedirect(route('login'));
    $this->post(route('admin.pages.store'))->assertRedirect(route('login'));
    $this->put(route('admin.pages.update', $page))->assertRedirect(route('login'));
    $this->delete(route('admin.pages.destroy', $page))->assertRedirect(route('login'));
});

it('creates a page', function () {
    $shop = makeShop();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.pages.store'), [
            'title' => 'Quienes somos',
            'slug' => 'quienes-somos',
            'content' => '<p>Hola</p>',
            'is_published' => 'on',
        ])
        ->assertRedirect();

    $page = $shop->pages()->where('slug', 'quienes-somos')->first();

    expect($page)->not->toBeNull()
        ->and($page->is_published)->toBeTrue();
});

it('sanitizes disallowed html out of the page content', function () {
    $shop = makeShop();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.pages.store'), [
            'title' => 'Quienes somos',
            'slug' => 'quienes-somos',
            'content' => '<p>Hola</p><script>alert("xss")</script><p onclick="evil()">Mundo</p>',
        ]);

    $page = $shop->pages()->where('slug', 'quienes-somos')->first();

    expect($page->content)->not->toContain('<script')
        ->and($page->content)->not->toContain('onclick')
        ->and($page->content)->toContain('Hola')
        ->and($page->content)->toContain('Mundo');
});

it('updates a page', function () {
    $shop = makeShop();
    $page = $shop->pages()->create(['title' => 'Quienes somos', 'slug' => 'quienes-somos']);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.pages.update', $page), [
            'title' => 'Sobre nosotros',
            'slug' => 'sobre-nosotros',
            'is_published' => 'on',
        ])
        ->assertRedirect(route('admin.pages.edit', $page));

    $page->refresh();

    expect($page->title)->toBe('Sobre nosotros')
        ->and($page->slug)->toBe('sobre-nosotros')
        ->and($page->is_published)->toBeTrue();
});

it('rejects a page slug that collides with an existing category slug', function () {
    $shop = makeShop();
    $shop->categories()->create(['name' => 'Promos', 'slug' => 'promos']);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.pages.store'), [
            'title' => 'Promos',
            'slug' => 'promos',
        ])
        ->assertSessionHasErrors('slug');
});

it('rejects a page slug that collides with an existing catalog product slug', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['slug' => 'tarjetas-de-presentacion']);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.pages.store'), [
            'title' => 'Tarjetas',
            'slug' => 'tarjetas-de-presentacion',
        ])
        ->assertSessionHasErrors('slug');
});

it('deletes a page', function () {
    $shop = makeShop();
    $page = $shop->pages()->create(['title' => 'Quienes somos', 'slug' => 'quienes-somos']);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.pages.destroy', $page))
        ->assertRedirect(route('admin.pages.index'));

    expect(Page::find($page->id))->toBeNull();
});
