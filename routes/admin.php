<?php

use App\Http\Controllers\Admin\ComponentController;
use App\Http\Controllers\Admin\ComponentOptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('components', ComponentController::class)->except(['show']);

    Route::post('components/{component}/options', [ComponentOptionController::class, 'store'])
        ->name('components.options.store');

    Route::put('components/{component}/options/{option}', [ComponentOptionController::class, 'update'])
        ->name('components.options.update');

    Route::delete('components/{component}/options/{option}', [ComponentOptionController::class, 'destroy'])
        ->name('components.options.destroy');
});
