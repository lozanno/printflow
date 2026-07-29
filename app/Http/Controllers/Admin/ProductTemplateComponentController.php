<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductTemplateComponentRequest;
use App\Models\Component;
use App\Models\ProductTemplate;
use App\Models\ProductTemplateComponent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
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

    public function move(Request $request, ProductTemplate $productTemplate, Component $component): RedirectResponse
    {
        $direction = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ])['direction'];

        $pivots = $productTemplate->templateComponents()->orderBy('sort_order')->get();
        $index = $pivots->search(fn (ProductTemplateComponent $pivot) => $pivot->component_id === $component->id);

        abort_if($index === false, 404);

        $swapWithIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapWithIndex >= 0 && $swapWithIndex < $pivots->count()) {
            $current = $pivots[$index];
            $swapWith = $pivots[$swapWithIndex];

            DB::transaction(function () use ($current, $swapWith): void {
                $currentOrder = $current->sort_order;
                $current->update(['sort_order' => $swapWith->sort_order]);
                $swapWith->update(['sort_order' => $currentOrder]);
            });
        }

        return to_route('admin.product-templates.edit', $productTemplate);
    }

    public function destroy(ProductTemplate $productTemplate, Component $component): RedirectResponse
    {
        $productTemplate->components()->detach($component->id);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Componente quitado de la plantilla.')]);

        return to_route('admin.product-templates.edit', $productTemplate);
    }
}
