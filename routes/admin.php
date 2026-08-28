<?php

use App\Http\Controllers\Admin\CatalogProductController;
use App\Http\Controllers\Admin\CatalogProductFaqController;
use App\Http\Controllers\Admin\CatalogProductReviewController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ComponentController;
use App\Http\Controllers\Admin\ComponentOptionController;
use App\Http\Controllers\Admin\OptionPriceModifierController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PricingTierController;
use App\Http\Controllers\Admin\ProductTemplateComponentController;
use App\Http\Controllers\Admin\ProductTemplateController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    // Every role's job orbits around orders (ventas follows up, admin
    // reconciles payment, produccion/calidad act on them) - any assigned
    // role, not just admin, can see this, and everyone can leave a note.
    Route::middleware('role')->group(function () {
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/notes', [OrderController::class, 'storeNote'])->name('orders.notes.store');
    });

    // Only the people actually running the shop floor move an order
    // through production - not ventas, not calidad (calidad's own gate is
    // the quality-check route below), not administrativo.
    Route::middleware('role:ADMIN,PRODUCCION')->group(function () {
        Route::patch('orders/{order}/production-stage', [OrderController::class, 'updateProductionStage'])
            ->name('orders.production-stage.update');
    });

    // Calidad's sign-off - enforced in Order::advanceProductionStage(),
    // not just by who can reach this route.
    Route::middleware('role:ADMIN,CALIDAD')->group(function () {
        Route::patch('orders/{order}/quality-check', [OrderController::class, 'updateQualityCheck'])
            ->name('orders.quality-check.update');
    });

    // Product/catalog configuration, shop settings, and staff management
    // stay admin-only.
    Route::middleware('role:ADMIN')->group(function () {
        Route::get('settings', [ShopController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [ShopController::class, 'update'])->name('settings.update');

        Route::resource('users', UserController::class)->except(['show']);

        Route::resource('categories', CategoryController::class)->except(['show']);

        Route::resource('pages', PageController::class)->except(['show']);

        Route::resource('components', ComponentController::class)->except(['show']);

        Route::post('components/{component}/options', [ComponentOptionController::class, 'store'])
            ->name('components.options.store');

        Route::put('components/{component}/options/{option}', [ComponentOptionController::class, 'update'])
            ->name('components.options.update');

        Route::patch('components/{component}/options/{option}/move', [ComponentOptionController::class, 'move'])
            ->name('components.options.move');

        Route::delete('components/{component}/options/{option}', [ComponentOptionController::class, 'destroy'])
            ->name('components.options.destroy');

        Route::resource('product-templates', ProductTemplateController::class)->except(['show']);

        Route::post('product-templates/{product_template}/components', [ProductTemplateComponentController::class, 'store'])
            ->name('product-templates.components.store');

        Route::patch('product-templates/{product_template}/components/{component}/move', [ProductTemplateComponentController::class, 'move'])
            ->name('product-templates.components.move');

        Route::delete('product-templates/{product_template}/components/{component}', [ProductTemplateComponentController::class, 'destroy'])
            ->name('product-templates.components.destroy');

        Route::resource('catalog-products', CatalogProductController::class)->except(['show']);

        Route::post('catalog-products/{catalog_product}/pricing-tiers', [PricingTierController::class, 'store'])
            ->name('catalog-products.pricing-tiers.store');

        Route::put('catalog-products/{catalog_product}/pricing-tiers/{tier}', [PricingTierController::class, 'update'])
            ->name('catalog-products.pricing-tiers.update');

        Route::delete('catalog-products/{catalog_product}/pricing-tiers/{tier}', [PricingTierController::class, 'destroy'])
            ->name('catalog-products.pricing-tiers.destroy');

        Route::post('catalog-products/{catalog_product}/option-modifiers', [OptionPriceModifierController::class, 'store'])
            ->name('catalog-products.option-modifiers.store');

        Route::put('catalog-products/{catalog_product}/option-modifiers/{modifier}', [OptionPriceModifierController::class, 'update'])
            ->name('catalog-products.option-modifiers.update');

        Route::delete('catalog-products/{catalog_product}/option-modifiers/{modifier}', [OptionPriceModifierController::class, 'destroy'])
            ->name('catalog-products.option-modifiers.destroy');

        Route::put('catalog-products/{catalog_product}/details', [CatalogProductController::class, 'updateDetails'])
            ->name('catalog-products.details.update');

        Route::post('catalog-products/{catalog_product}/faqs', [CatalogProductFaqController::class, 'store'])
            ->name('catalog-products.faqs.store');

        Route::put('catalog-products/{catalog_product}/faqs/{faq}', [CatalogProductFaqController::class, 'update'])
            ->name('catalog-products.faqs.update');

        Route::patch('catalog-products/{catalog_product}/faqs/{faq}/move', [CatalogProductFaqController::class, 'move'])
            ->name('catalog-products.faqs.move');

        Route::delete('catalog-products/{catalog_product}/faqs/{faq}', [CatalogProductFaqController::class, 'destroy'])
            ->name('catalog-products.faqs.destroy');

        Route::post('catalog-products/{catalog_product}/reviews', [CatalogProductReviewController::class, 'store'])
            ->name('catalog-products.reviews.store');

        Route::put('catalog-products/{catalog_product}/reviews/{review}', [CatalogProductReviewController::class, 'update'])
            ->name('catalog-products.reviews.update');

        Route::patch('catalog-products/{catalog_product}/reviews/{review}/move', [CatalogProductReviewController::class, 'move'])
            ->name('catalog-products.reviews.move');

        Route::delete('catalog-products/{catalog_product}/reviews/{review}', [CatalogProductReviewController::class, 'destroy'])
            ->name('catalog-products.reviews.destroy');
    });
});
