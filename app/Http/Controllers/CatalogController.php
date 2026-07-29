<?php

namespace App\Http\Controllers;

use App\Enums\InputType;
use App\Models\CatalogProduct;
use App\Models\Component;
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
                    'name' => $catalogProduct->name_override ?? $catalogProduct->productTemplate->name,
                    'image_url' => $catalogProduct->image_url,
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
                'image_url' => $catalogProduct->image_url,
                'description' => $catalogProduct->description,
                'components' => $this->serializeComponents($catalogProduct->productTemplate->components),
            ],
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

    private function ensurePubliclyVisible(CatalogProduct $catalogProduct): void
    {
        abort_unless(
            $catalogProduct->is_active && $catalogProduct->shop_id === Shop::current()->id,
            404,
        );
    }
}
