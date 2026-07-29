<?php

use App\Http\Controllers\Admin\CatalogProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ComponentController;
use App\Http\Controllers\Admin\ComponentOptionController;
use App\Http\Controllers\Admin\OptionPriceModifierController;
use App\Http\Controllers\Admin\PricingTierController;
use App\Http\Controllers\Admin\ProductTemplateComponentController;
use App\Http\Controllers\Admin\ProductTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::resource('components', ComponentController::class)->except(['show']);

    Route::post('components/{component}/options', [ComponentOptionController::class, 'store'])
        ->name('components.options.store');

    Route::put('components/{component}/options/{option}', [ComponentOptionController::class, 'update'])
        ->name('components.options.update');

    Route::delete('components/{component}/options/{option}', [ComponentOptionController::class, 'destroy'])
        ->name('components.options.destroy');

    Route::resource('product-templates', ProductTemplateController::class)->except(['show']);

    Route::post('product-templates/{product_template}/components', [ProductTemplateComponentController::class, 'store'])
        ->name('product-templates.components.store');

    Route::delete('product-templates/{product_template}/components/{component}', [ProductTemplateComponentController::class, 'destroy'])
        ->name('product-templates.components.destroy');

    Route::resource('catalog-products', CatalogProductController::class)->except(['show']);

    Route::post('catalog-products/{catalog_product}/pricing-tiers', [PricingTierController::class, 'store'])
        ->name('catalog-products.pricing-tiers.store');

    Route::delete('catalog-products/{catalog_product}/pricing-tiers/{tier}', [PricingTierController::class, 'destroy'])
        ->name('catalog-products.pricing-tiers.destroy');

    Route::post('catalog-products/{catalog_product}/option-modifiers', [OptionPriceModifierController::class, 'store'])
        ->name('catalog-products.option-modifiers.store');

    Route::put('catalog-products/{catalog_product}/option-modifiers/{modifier}', [OptionPriceModifierController::class, 'update'])
        ->name('catalog-products.option-modifiers.update');

    Route::delete('catalog-products/{catalog_product}/option-modifiers/{modifier}', [OptionPriceModifierController::class, 'destroy'])
        ->name('catalog-products.option-modifiers.destroy');
});
