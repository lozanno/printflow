<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\QuoteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('home');

Route::post('{catalogProduct:slug}/cotizar', [QuoteController::class, 'store'])->name('catalog.quote');

Route::post('{catalogProduct:slug}/rangos-precio', [QuoteController::class, 'tierTable'])->name('catalog.tier-table');

Route::get('{catalogProduct:slug}/pedido', [CheckoutController::class, 'show'])->name('catalog.checkout');

Route::post('{catalogProduct:slug}/pedido', [OrderController::class, 'store'])->name('catalog.orders.store');

Route::get('pedidos/{order}/confirmacion', [OrderController::class, 'confirmation'])->name('orders.confirmation');

// Reserved top-level segments used by other route files (auth, admin,
// dashboard, settings...) must never be swallowed by this catch-all, since
// it resolves against the CatalogProduct/Category slug columns instead of
// route model binding. Registering this route last is not enough on its
// own to guarantee that - package routes (Fortify) may register during a
// later boot phase - so the reserved words are excluded here too.
Route::get('{slug}', [CatalogController::class, 'show'])
    ->where('slug', '^(?!admin$|dashboard$|login$|logout$|register$|forgot-password$|reset-password$|settings$|storage$|up$|user$)[a-z0-9-]+$')
    ->name('catalog.show');
