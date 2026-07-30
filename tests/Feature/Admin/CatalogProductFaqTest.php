<?php

use App\Enums\PricingStrategy;
use App\Models\User;

it('redirects guests for every faq route', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $faq = $catalogProduct->faqs()->create(['question' => '¿Envian a todo el pais?', 'answer' => 'Si.', 'sort_order' => 0]);

    $this->post(route('admin.catalog-products.faqs.store', $catalogProduct))->assertRedirect(route('login'));
    $this->put(route('admin.catalog-products.faqs.update', [$catalogProduct, $faq]))->assertRedirect(route('login'));
    $this->delete(route('admin.catalog-products.faqs.destroy', [$catalogProduct, $faq]))->assertRedirect(route('login'));
});

it('creates a faq for a catalog product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.catalog-products.faqs.store', $catalogProduct), [
            'question' => '¿Cuanto tarda el pedido?',
            'answer' => '24 a 48 horas.',
        ])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    expect($catalogProduct->faqs()->where('question', '¿Cuanto tarda el pedido?')->exists())->toBeTrue();
});

it('updates a faq in place', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $faq = $catalogProduct->faqs()->create(['question' => 'Vieja pregunta', 'answer' => 'Vieja respuesta', 'sort_order' => 0]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.catalog-products.faqs.update', [$catalogProduct, $faq]), [
            'question' => 'Nueva pregunta',
            'answer' => 'Nueva respuesta',
        ])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    $faq->refresh();

    expect($faq->question)->toBe('Nueva pregunta')
        ->and($faq->answer)->toBe('Nueva respuesta')
        ->and($catalogProduct->faqs()->count())->toBe(1);
});

it('deletes a faq', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $faq = $catalogProduct->faqs()->create(['question' => 'Pregunta', 'answer' => 'Respuesta', 'sort_order' => 0]);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.catalog-products.faqs.destroy', [$catalogProduct, $faq]))
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    expect($catalogProduct->faqs()->count())->toBe(0);
});

it('moves a faq up by swapping sort_order with its previous sibling', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $first = $catalogProduct->faqs()->create(['question' => 'Primera', 'answer' => 'A', 'sort_order' => 1]);
    $second = $catalogProduct->faqs()->create(['question' => 'Segunda', 'answer' => 'B', 'sort_order' => 2]);

    $this->actingAs(User::factory()->create())
        ->patch(route('admin.catalog-products.faqs.move', [$catalogProduct, $second]), ['direction' => 'up'])
        ->assertRedirect(route('admin.catalog-products.edit', $catalogProduct));

    expect($first->fresh()->sort_order)->toBe(2)
        ->and($second->fresh()->sort_order)->toBe(1);
});

it('rejects a faq without a question or answer', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.catalog-products.faqs.store', $catalogProduct), [
            'question' => '',
            'answer' => '',
        ])
        ->assertSessionHasErrors(['question', 'answer']);
});
