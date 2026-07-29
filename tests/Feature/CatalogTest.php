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

    $this->get("/{$catalogProduct->slug}")
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

    $this->get("/{$catalogProduct->slug}")
        ->assertInertia(fn ($page) => $page
            ->component('catalog/show')
            ->missing('catalogProduct.pricing_profile')
            ->missing('catalogProduct.params')
        );
});

it('404s for an inactive product', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['is_active' => false]);

    $this->get("/{$catalogProduct->slug}")->assertNotFound();
});

it('404s for a product that does not exist', function () {
    $this->get('/does-not-exist')->assertNotFound();
});

it('shows the active products in a category', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['slug' => 'tarjetas-de-presentacion']);
    $category = $catalogProduct->shop->categories()->create([
        'name' => 'Impresos promocionales',
        'slug' => 'impresos-promocionales',
    ]);
    $category->catalogProducts()->attach($catalogProduct->id);

    $this->get('/impresos-promocionales')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('catalog/category')
            ->where('category.name', 'Impresos promocionales')
            ->has('catalogProducts', 1)
            ->where('catalogProducts.0.id', $catalogProduct->id)
        );
});

it('excludes inactive products from a category page', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['slug' => 'tarjetas-de-presentacion', 'is_active' => false]);
    $category = $catalogProduct->shop->categories()->create([
        'name' => 'Impresos promocionales',
        'slug' => 'impresos-promocionales',
    ]);
    $category->catalogProducts()->attach($catalogProduct->id);

    $this->get('/impresos-promocionales')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('catalog/category')
            ->has('catalogProducts', 0)
        );
});

it('404s for a category that does not exist', function () {
    makeShop();

    $this->get('/does-not-exist')->assertNotFound();
});

it('lets a product belong to more than one category at once', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['slug' => 'tarjetas-de-presentacion']);
    $shop = $catalogProduct->shop;

    $promos = $shop->categories()->create(['name' => 'Promos', 'slug' => 'promos']);
    $impresos = $shop->categories()->create(['name' => 'Impresos promocionales', 'slug' => 'impresos-promocionales']);
    $catalogProduct->categories()->attach([$promos->id, $impresos->id]);

    $this->get('/promos')->assertInertia(fn ($page) => $page->has('catalogProducts', 1));
    $this->get('/impresos-promocionales')->assertInertia(fn ($page) => $page->has('catalogProducts', 1));
});

it('does not let the catch-all slug route shadow the reserved application routes', function () {
    $this->get(route('login'))->assertOk();
    $this->get(route('register'))->assertOk();
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});
