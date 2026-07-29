<?php

namespace App\Http\Controllers;

use App\Models\CatalogProduct;
use App\Models\Component;
use App\Models\ComponentOption;
use App\Models\Shop;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(): Response
    {
        $shop = Shop::current();

        return Inertia::render('catalog/index', [
            'shopName' => $shop->name,
            'catalogProducts' => $shop->catalogProducts()
                ->where('is_active', true)
                ->with('productTemplate')
                ->orderBy('created_at')
                ->get()
                ->map(fn (CatalogProduct $catalogProduct) => [
                    'id' => $catalogProduct->id,
                    'name' => $catalogProduct->name_override ?? $catalogProduct->productTemplate->name,
                ]),
        ]);
    }

    public function show(CatalogProduct $catalogProduct): Response
    {
        $this->ensurePubliclyVisible($catalogProduct);

        $catalogProduct->load('productTemplate.components.options');

        return Inertia::render('catalog/show', [
            'catalogProduct' => [
                'id' => $catalogProduct->id,
                'name' => $catalogProduct->name_override ?? $catalogProduct->productTemplate->name,
                'components' => $catalogProduct->productTemplate->components
                    ->sortBy('pivot.sort_order')
                    ->values()
                    ->map(fn (Component $component) => [
                        'code' => $component->code,
                        'label' => $component->label,
                        'input_type' => $component->input_type,
                        'is_required' => $component->pivot->is_required,
                        'options' => $component->options->map(fn (ComponentOption $option) => [
                            'value' => $option->value,
                            'label' => $option->label,
                        ]),
                    ]),
            ],
        ]);
    }

    private function ensurePubliclyVisible(CatalogProduct $catalogProduct): void
    {
        abort_unless(
            $catalogProduct->is_active && $catalogProduct->shop_id === Shop::current()->id,
            404,
        );
    }
}
