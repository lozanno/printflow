<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOptionPriceModifierRequest;
use App\Models\CatalogProduct;
use App\Models\OptionPriceModifier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class OptionPriceModifierController extends Controller
{
    public function store(StoreOptionPriceModifierRequest $request, CatalogProduct $catalogProduct): RedirectResponse
    {
        $catalogProduct->pricingProfile->optionModifiers()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Modificador de precio agregado.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }

    public function destroy(CatalogProduct $catalogProduct, OptionPriceModifier $modifier): RedirectResponse
    {
        abort_if($modifier->pricing_profile_id !== $catalogProduct->pricingProfile->id, 404);

        $modifier->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Modificador de precio eliminado.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }
}
