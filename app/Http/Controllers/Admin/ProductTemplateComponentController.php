<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductTemplateComponentRequest;
use App\Models\Component;
use App\Models\ProductTemplate;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ProductTemplateComponentController extends Controller
{
    public function store(StoreProductTemplateComponentRequest $request, ProductTemplate $productTemplate): RedirectResponse
    {
        $nextSortOrder = ($productTemplate->templateComponents()->max('sort_order') ?? 0) + 1;

        $productTemplate->components()->attach($request->validated('component_id'), [
            'sort_order' => $nextSortOrder,
            'is_required' => $request->boolean('is_required'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Componente agregado a la plantilla.')]);

        return to_route('admin.product-templates.edit', $productTemplate);
    }

    public function destroy(ProductTemplate $productTemplate, Component $component): RedirectResponse
    {
        $productTemplate->components()->detach($component->id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Componente quitado de la plantilla.')]);

        return to_route('admin.product-templates.edit', $productTemplate);
    }
}
