<?php

namespace App\Http\Controllers;

use App\Enums\InputType;
use App\Models\CatalogProduct;
use App\Models\Category;
use App\Models\Component;
use App\Models\PricingTier;
use App\Models\Shop;
use Illuminate\Support\Collection;
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
                    'slug' => $catalogProduct->slug,
                    'name' => $catalogProduct->name_override ?? $catalogProduct->productTemplate->name,
                    'image_url' => $catalogProduct->image_url,
                ]),
            'categories' => $shop->categories()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }

    public function show(string $slug): Response
    {
        $shop = Shop::current();

        $catalogProduct = CatalogProduct::query()
            ->where('shop_id', $shop->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if ($catalogProduct) {
            return $this->showProduct($catalogProduct);
        }

        $category = Category::query()
            ->where('shop_id', $shop->id)
            ->where('slug', $slug)
            ->first();

        abort_unless($category !== null, 404);

        return $this->showCategory($category);
    }

    private function showProduct(CatalogProduct $catalogProduct): Response
    {
        $catalogProduct->load('productTemplate.components.options', 'pricingProfile.tiers', 'shop');

        return Inertia::render('catalog/show', [
            'catalogProduct' => [
                'id' => $catalogProduct->id,
                'slug' => $catalogProduct->slug,
                'name' => $catalogProduct->name_override ?? $catalogProduct->productTemplate->name,
                'image_url' => $catalogProduct->image_url,
                'description' => $catalogProduct->description,
                'currency' => $catalogProduct->shop->currency,
                'pricing_strategy' => $catalogProduct->productTemplate->pricing_strategy,
                'components' => $this->serializeComponents($catalogProduct->productTemplate->components),
                'pricing_tiers' => $catalogProduct->pricingProfile?->tiers
                    ->map(fn (PricingTier $tier) => [
                        'min_quantity' => $tier->min_quantity,
                        'max_quantity' => $tier->max_quantity,
                        'unit_price' => (float) $tier->unit_price,
                    ])
                    ->all() ?? [],
            ],
        ]);
    }

    private function showCategory(Category $category): Response
    {
        return Inertia::render('catalog/category', [
            'category' => [
                'name' => $category->name,
            ],
            'catalogProducts' => $category->catalogProducts()
                ->where('is_active', true)
                ->with('productTemplate')
                ->orderBy('created_at')
                ->get()
                ->map(fn (CatalogProduct $catalogProduct) => [
                    'id' => $catalogProduct->id,
                    'slug' => $catalogProduct->slug,
                    'name' => $catalogProduct->name_override ?? $catalogProduct->productTemplate->name,
                    'image_url' => $catalogProduct->image_url,
                ]),
        ]);
    }

    /**
     * @param  Collection<int, Component>  $components
     * @return list<array{code: string, label: string, input_type: InputType, is_required: bool, options: list<array{value: string, label: string, image_url: string|null}>}>
     */
    private function serializeComponents(Collection $components): array
    {
        $serialized = [];

        foreach ($components->sortBy('pivot.sort_order') as $component) {
            $options = [];

            foreach ($component->options as $option) {
                $options[] = [
                    'value' => $option->value,
                    'label' => $option->label,
                    'image_url' => $option->image_url,
                ];
            }

            $serialized[] = [
                'code' => $component->code,
                'label' => $component->label,
                'input_type' => $component->input_type,
                'is_required' => $component->pivot->is_required,
                'options' => $options,
            ];
        }

        return $serialized;
    }
}
