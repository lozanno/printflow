<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePricingTierRequest;
use App\Models\CatalogProduct;
use App\Models\PricingTier;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class PricingTierController extends Controller
{
    public function store(StorePricingTierRequest $request, CatalogProduct $catalogProduct): RedirectResponse
    {
        $catalogProduct->pricingProfile->tiers()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Rango de precio agregado.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }

    public function destroy(CatalogProduct $catalogProduct, PricingTier $tier): RedirectResponse
    {
        abort_if($tier->pricing_profile_id !== $catalogProduct->pricingProfile->id, 404);

        $tier->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Rango de precio eliminado.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }
}
