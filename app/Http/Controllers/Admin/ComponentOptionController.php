<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreComponentOptionRequest;
use App\Http\Requests\Admin\UpdateComponentOptionRequest;
use App\Models\Component;
use App\Models\ComponentOption;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ComponentOptionController extends Controller
{
    public function store(StoreComponentOptionRequest $request, Component $component): RedirectResponse
    {
        $component->options()->create([
            ...$request->validated(),
            'sort_order' => ($component->options()->max('sort_order') ?? 0) + 1,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Opcion agregada.')]);

        return to_route('admin.components.edit', $component);
    }

    public function update(UpdateComponentOptionRequest $request, Component $component, ComponentOption $option): RedirectResponse
    {
        abort_if($option->component_id !== $component->id, 404);

        $option->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Opcion actualizada.')]);

        return to_route('admin.components.edit', $component);
    }

    public function destroy(Component $component, ComponentOption $option): RedirectResponse
    {
        abort_if($option->component_id !== $component->id, 404);

        $option->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Opcion eliminada.')]);

        return to_route('admin.components.edit', $component);
    }
}
