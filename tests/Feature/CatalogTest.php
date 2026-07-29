<?php

use App\Enums\InputType;
use App\Enums\PricingStrategy;
use App\Models\ProductTemplate;

it('only lists active catalog products', function () {
    $active = makeCatalogProduct(PricingStrategy::PerUnitTiered);

    $inactiveTemplate = ProductTemplate::create([
        'code' => 'inactive-template-'.uniqid(),
        'name' => 'Inactive Template',
        'pricing_strategy' => PricingStrategy::PerUnitTiered,
    ]);
    $inactive = $active->shop->catalogProducts()->create([
        'product_template_id' => $inactiveTemplate->id,
        'is_active' => false,
    ]);
    $inactive->pricingProfile()->create();

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('catalog/index')
            ->has('catalogProducts', 1)
            ->where('catalogProducts.0.id', $active->id)
        );
});

it('shows the component schema for an active product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    attachComponent($catalogProduct->productTemplate, 'quantity', 'Cantidad', InputType::Choice, options: [
        ['100', '100 piezas'],
    ]);

    $this->get("/productos/{$catalogProduct->id}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('catalog/show')
            ->where('catalogProduct.id', $catalogProduct->id)
            ->has('catalogProduct.components', 1)
            ->where('catalogProduct.components.0.code', 'quantity')
            ->has('catalogProduct.components.0.options', 1)
        );
});

it('does not expose pricing internals on the show page', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerAreaWithSetup);
    $catalogProduct->pricingProfile->update(['params' => ['rate_per_sqm' => 180, 'setup_fee' => 50]]);

    $this->get("/productos/{$catalogProduct->id}")
        ->assertInertia(fn ($page) => $page
            ->component('catalog/show')
            ->missing('catalogProduct.pricing_profile')
            ->missing('catalogProduct.params')
        );
});

it('404s for an inactive product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['is_active' => false]);

    $this->get("/productos/{$catalogProduct->id}")->assertNotFound();
});

it('404s for a product that does not exist', function () {
    $this->get('/productos/999999')->assertNotFound();
});
