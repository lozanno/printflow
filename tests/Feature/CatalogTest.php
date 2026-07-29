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
    attachComponent($catalogProduct->productTemplate, 'finish', 'Acabado', InputType::Choice, options: [
        ['gloss', 'Laminado brillante'],
    ]);

    $this->get("/{$catalogProduct->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('catalog/show')
            ->where('catalogProduct.id', $catalogProduct->id)
            ->has('catalogProduct.components', 1)
            ->where('catalogProduct.components.0.code', 'finish')
            ->has('catalogProduct.components.0.options', 1)
        );
});

it('builds the quantity table from pricing tiers instead of a component', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->createMany([
        ['min_quantity' => 100, 'max_quantity' => 249, 'unit_price' => 1.50],
        ['min_quantity' => 250, 'max_quantity' => null, 'unit_price' => 1.10],
    ]);

    $this->get("/{$catalogProduct->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('catalog/show')
            ->where('catalogProduct.pricing_strategy', 'PER_UNIT_TIERED')
            ->has('catalogProduct.pricing_tiers', 2)
            ->where('catalogProduct.pricing_tiers.0.min_quantity', 100)
            ->where('catalogProduct.pricing_tiers.0.unit_price', 1.5)
            ->where('catalogProduct.components', [])
        );
});

it('bakes adjustment_percent into the tier price without ever exposing the raw field', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->pricingProfile->tiers()->create([
        'min_quantity' => 100, 'max_quantity' => null, 'unit_price' => 1.00, 'adjustment_percent' => -10,
    ]);

    $this->get("/{$catalogProduct->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('catalog/show')
            ->where('catalogProduct.pricing_tiers.0.unit_price', 0.9)
            ->where('catalogProduct.pricing_tiers.0.total', 90)
            ->missing('catalogProduct.pricing_tiers.0.adjustment_percent')
        );
});

it('includes size presets for a DIMENSIONS component just like CHOICE options', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerArea);
    $catalogProduct->pricingProfile->update(['params' => ['rate_per_sqm' => 180]]);
    attachComponent($catalogProduct->productTemplate, 'dimensions', 'Tamano', InputType::Dimensions, options: [
        ['420x594', '420 x 594 mm (A2)'],
        ['841x594', '594 x 841 mm (A1)'],
    ]);

    $this->get("/{$catalogProduct->slug}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('catalog/show')
            ->where('catalogProduct.components.0.code', 'dimensions')
            ->has('catalogProduct.components.0.options', 2)
            ->where('catalogProduct.components.0.options.0.value', '420x594')
            ->where('catalogProduct.components.0.options.0.label', '420 x 594 mm (A2)')
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

it('shows a published static page at its friendly url', function () {
    $shop = makeShop();
    $shop->pages()->create([
        'title' => 'Quienes somos',
        'slug' => 'quienes-somos',
        'content' => '<p>Somos una imprenta familiar.</p>',
        'is_published' => true,
    ]);

    $this->get('/quienes-somos')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('pages/show')
            ->where('page.title', 'Quienes somos')
            ->where('page.content', '<p>Somos una imprenta familiar.</p>')
        );
});

it('404s for an unpublished page', function () {
    $shop = makeShop();
    $shop->pages()->create([
        'title' => 'Borrador',
        'slug' => 'borrador',
        'is_published' => false,
    ]);

    $this->get('/borrador')->assertNotFound();
});

it('resolves a product before a page when slugs would otherwise collide', function () {
    $catalogProduct = makeCatalogProduct(PricingStrategy::PerUnitTiered);
    $catalogProduct->update(['slug' => 'quienes-somos']);
    // Bypasses the admin cross-table slug validation on purpose, to prove
    // the controller's resolution order (product, then category, then
    // page) defensively holds even if a collision ever slipped through.
    $catalogProduct->shop->pages()->create([
        'title' => 'Quienes somos',
        'slug' => 'quienes-somos',
        'is_published' => true,
    ]);

    $this->get('/quienes-somos')
        ->assertInertia(fn ($page) => $page->component('catalog/show'));
});
