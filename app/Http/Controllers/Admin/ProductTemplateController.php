<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductTemplateRequest;
use App\Http\Requests\Admin\UpdateProductTemplateRequest;
use App\Models\Component;
use App\Models\ProductTemplate;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ProductTemplateController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/product-templates/index', [
            'productTemplates' => ProductTemplate::query()
                ->withCount('components')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/product-templates/create');
    }

    public function store(StoreProductTemplateRequest $request): RedirectResponse
    {
        ProductTemplate::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plantilla de producto creada.')]);

        return to_route('admin.product-templates.index');
    }

    public function edit(ProductTemplate $productTemplate): Response
    {
        $attachedComponentIds = $productTemplate->components()->pluck('components.id');

        return Inertia::render('admin/product-templates/edit', [
            'productTemplate' => $productTemplate->load('components'),
            'availableComponents' => Component::query()
                ->whereNotIn('id', $attachedComponentIds)
                // "quantity" is reserved for the PricingTier-driven picker,
                // see StoreComponentRequest.
                ->where('code', '!=', 'quantity')
                ->orderBy('label')
                ->get(),
        ]);
    }

    public function update(UpdateProductTemplateRequest $request, ProductTemplate $productTemplate): RedirectResponse
    {
        $productTemplate->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plantilla de producto actualizada.')]);

        return to_route('admin.product-templates.edit', $productTemplate);
    }

    public function destroy(ProductTemplate $productTemplate): RedirectResponse
    {
        if ($productTemplate->catalogProducts()->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => __('No se puede eliminar: hay productos del catalogo usando esta plantilla.'),
            ]);

            return to_route('admin.product-templates.index');
        }

        $productTemplate->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Plantilla de producto eliminada.')]);

        return to_route('admin.product-templates.index');
    }
}
