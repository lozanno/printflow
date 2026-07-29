<?php

use App\Enums\InputType;
use App\Enums\PricingStrategy;
use App\Models\CatalogProduct;
use App\Models\Component;
use App\Models\ProductTemplate;
use App\Models\Shop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Creates the single Shop row the app expects (Shop::current() calls
 * sole()). Every test that touches shop-scoped data needs exactly one.
 */
function makeShop(): Shop
{
    return Shop::create([
        'name' => 'Test Shop',
        'slug' => 'test-shop-'.uniqid(),
        'currency' => 'MXN',
    ]);
}

/**
 * Creates a Shop, a ProductTemplate on the given strategy, a CatalogProduct
 * activating it, and an empty PricingProfile - the minimum fixture any
 * QuoteEngine or public catalog test needs to build on.
 */
function makeCatalogProduct(PricingStrategy $strategy): CatalogProduct
{
    $shop = makeShop();

    $template = ProductTemplate::create([
        'code' => 'template-'.uniqid(),
        'name' => 'Test Template',
        'pricing_strategy' => $strategy,
    ]);

    $catalogProduct = $shop->catalogProducts()->create([
        'product_template_id' => $template->id,
        'slug' => 'test-product-'.uniqid(),
        'is_active' => true,
    ]);

    $catalogProduct->pricingProfile()->create();

    return $catalogProduct->fresh();
}

/**
 * Creates a Component and attaches it to the given ProductTemplate.
 *
 * @param  list<array{0: string, 1: string}>  $options
 */
function attachComponent(
    ProductTemplate $template,
    string $code,
    string $label,
    InputType $type,
    bool $required = true,
    array $options = [],
): Component {
    $component = Component::create(['code' => $code, 'label' => $label, 'input_type' => $type]);

    $template->components()->attach($component->id, [
        'sort_order' => ($template->templateComponents()->max('sort_order') ?? 0) + 1,
        'is_required' => $required,
    ]);

    foreach ($options as $i => [$value, $optionLabel]) {
        $component->options()->create(['value' => $value, 'label' => $optionLabel, 'sort_order' => $i]);
    }

    return $component->fresh();
}
