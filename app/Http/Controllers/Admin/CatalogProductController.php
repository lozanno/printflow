<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InputType;
use App\Enums\PricingStrategy;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCatalogProductRequest;
use App\Http\Requests\Admin\UpdateCatalogProductRequest;
use App\Models\CatalogProduct;
use App\Models\ProductTemplate;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CatalogProductController extends Controller
{
    public function index(): Response
    {
        $shop = Shop::current();

        return Inertia::render('admin/catalog-products/index', [
            'catalogProducts' => $shop->catalogProducts()
                ->with(['productTemplate', 'pricingProfile.tiers'])
                ->orderBy('created_at')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        $shop = Shop::current();

        return Inertia::render('admin/catalog-products/create', [
            'availableProductTemplates' => ProductTemplate::query()
                ->whereNotIn('id', $shop->catalogProducts()->pluck('product_template_id'))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreCatalogProductRequest $request): RedirectResponse
    {
        $shop = Shop::current();

        $catalogProduct = DB::transaction(function () use ($request, $shop): CatalogProduct {
            $catalogProduct = $shop->catalogProducts()->create([
                'product_template_id' => $request->validated('product_template_id'),
                'name_override' => $request->validated('name_override'),
                'is_active' => $request->boolean('is_active'),
            ]);

            $catalogProduct->pricingProfile()->create();

            return $catalogProduct;
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Producto de catalogo creado.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }

    public function edit(CatalogProduct $catalogProduct): Response
    {
        $this->ensureBelongsToCurrentShop($catalogProduct);

        $catalogProduct->load([
            'productTemplate.components.options',
            'pricingProfile.tiers',
            'pricingProfile.optionModifiers.componentOption.component',
        ]);

        $configuredOptionIds = $catalogProduct->pricingProfile->optionModifiers->pluck('component_option_id');

        /** @var list<array{id: int, label: string, value: string, component_label: string}> $availableOptions */
        $availableOptions = [];

        foreach ($catalogProduct->productTemplate->components as $component) {
            if ($component->input_type !== InputType::Choice) {
                continue;
            }

            foreach ($component->options as $option) {
                if ($configuredOptionIds->contains($option->id)) {
                    continue;
                }

                $availableOptions[] = [
                    'id' => $option->id,
                    'label' => $option->label,
                    'value' => $option->value,
                    'component_label' => $component->label,
                ];
            }
        }

        return Inertia::render('admin/catalog-products/edit', [
            'catalogProduct' => $catalogProduct,
            'availableOptions' => $availableOptions,
        ]);
    }

    public function update(UpdateCatalogProductRequest $request, CatalogProduct $catalogProduct): RedirectResponse
    {
        $this->ensureBelongsToCurrentShop($catalogProduct);

        $catalogProduct->update([
            'name_override' => $request->validated('name_override'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $strategy = $catalogProduct->productTemplate->pricing_strategy;

        $params = match ($strategy) {
            PricingStrategy::PerArea => ['rate_per_sqm' => $request->validated('rate_per_sqm')],
            PricingStrategy::PerAreaWithSetup => [
                'rate_per_sqm' => $request->validated('rate_per_sqm'),
                'setup_fee' => $request->validated('setup_fee'),
            ],
            PricingStrategy::PerUnitTiered => null,
        };

        $catalogProduct->pricingProfile->update(['params' => $params]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Producto de catalogo actualizado.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }

    public function destroy(CatalogProduct $catalogProduct): RedirectResponse
    {
        $this->ensureBelongsToCurrentShop($catalogProduct);

        if ($catalogProduct->orderItems()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar: hay pedidos que usan este producto.'),
            ]);

            return to_route('admin.catalog-products.index');
        }

        $catalogProduct->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Producto de catalogo eliminado.')]);

        return to_route('admin.catalog-products.index');
    }

    private function ensureBelongsToCurrentShop(CatalogProduct $catalogProduct): void
    {
        abort_unless($catalogProduct->shop_id === Shop::current()->id, 404);
    }
}
