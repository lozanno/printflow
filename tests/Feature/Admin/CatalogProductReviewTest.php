<?php

use App\Enums\PricingStrategy;
use App\Models\User;

it('redirects guests for every review route', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $review = $catalogProduct->reviews()->create(['author_name' => 'Ana', 'rating' => 5, 'comment' => 'Excelente', 'sort_order' => 0]);

    $this->post(route('admin.catalog-products.reviews.store', $catalogProduct))->assertRedirect(route('login'));
    $this->put(route('admin.catalog-products.reviews.update', [$catalogProduct, $review]))->assertRedirect(route('login'));
    $this->delete(route('admin.catalog-products.reviews.destroy', [$catalogProduct, $review]))->assertRedirect(route('login'));
});

it('creates a review for a catalog product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.catalog-products.reviews.store', $catalogProduct), [
            'author_name' => 'Maria G.',
            'rating' => 5,
            'comment' => 'Muy buen servicio.',
        ])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    expect($catalogProduct->reviews()->where('author_name', 'Maria G.')->exists())->toBeTrue();
});

it('updates a review in place', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $review = $catalogProduct->reviews()->create(['author_name' => 'Ana', 'rating' => 3, 'comment' => 'Ok', 'sort_order' => 0]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.catalog-products.reviews.update', [$catalogProduct, $review]), [
            'author_name' => 'Ana Lopez',
            'rating' => 5,
            'comment' => 'Excelente servicio',
        ])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    $review->refresh();

    expect($review->author_name)->toBe('Ana Lopez')
        ->and($review->rating)->toBe(5)
        ->and($catalogProduct->reviews()->count())->toBe(1);
});

it('deletes a review', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $review = $catalogProduct->reviews()->create(['author_name' => 'Ana', 'rating' => 5, 'comment' => 'Bien', 'sort_order' => 0]);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.catalog-products.reviews.destroy', [$catalogProduct, $review]))
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    expect($catalogProduct->reviews()->count())->toBe(0);
});

it('moves a review down by swapping sort_order with its next sibling', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $first = $catalogProduct->reviews()->create(['author_name' => 'Ana', 'rating' => 5, 'comment' => 'A', 'sort_order' => 1]);
    $second = $catalogProduct->reviews()->create(['author_name' => 'Beto', 'rating' => 4, 'comment' => 'B', 'sort_order' => 2]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.catalog-products.reviews.move', [$catalogProduct, $first]), ['direction' => 'down'])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    expect($first->fresh()->sort_order)->toBe(2)
        ->and($second->fresh()->sort_order)->toBe(1);
});

it('rejects a rating outside the 1 to 5 range', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.catalog-products.reviews.store', $catalogProduct), [
            'author_name' => 'Ana',
            'rating' => 6,
            'comment' => 'Bien',
        ])
        ->assertSessionHasErrors('rating');
});
