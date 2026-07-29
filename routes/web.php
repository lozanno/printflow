<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/admin.php';

// Must be required last: routes/catalog.php ends with a catch-all
// GET /{slug} route that resolves against CatalogProduct/Category slugs.
require __DIR__.'/catalog.php';
