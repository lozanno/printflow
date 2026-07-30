<?php

use App\Enums\PricingStrategy;
use App\Models\User;

it('redirects guests for the details route', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    $this->put(route('admin.catalog-products.details.update', $catalogProduct))->assertRedirect(route('login'));
});

it('updates and sanitizes the free-form details content', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.catalog-products.details.update', $catalogProduct), [
            'details_content' => '<details><summary>Especificaciones</summary><p>300gr</p></details><script>alert(1)</script>',
        ])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    $catalogProduct->refresh();

    expect($catalogProduct->details_content)
        ->toContain('<details>')
        ->toContain('<summary>Especificaciones</summary>')
        ->not->toContain('<script>');
});

it('turns a product featured on and off from the general update form', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    expect($catalogProduct->is_featured)->toBeFalse();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.catalog-products.update', $catalogProduct), [
            'slug' => $catalogProduct->slug,
            'is_featured' => 'on',
        ])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    expect($catalogProduct->fresh()->is_featured)->toBeTrue();

    $this->actingAs(User::factory()->create())
        ->put(route('admin.catalog-products.update', $catalogProduct), [
            'slug' => $catalogProduct->slug,
        ])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    expect($catalogProduct->fresh()->is_featured)->toBeFalse();
});
