<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\QuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('home');

Route::get('productos/{catalog_product}', [CatalogController::class, 'show'])->name('catalog.show');

Route::post('productos/{catalog_product}/cotizar', [QuoteController::class, 'store'])->name('catalog.quote');
