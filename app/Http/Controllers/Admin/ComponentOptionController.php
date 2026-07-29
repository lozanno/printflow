<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreComponentOptionRequest;
use App\Http\Requests\Admin\UpdateComponentOptionRequest;
use App\Models\Component;
use App\Models\ComponentOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ComponentOptionController extends Controller
{
    public function store(StoreComponentOptionRequest $request, Component $component): RedirectResponse
    {
        $component->options()->create([
            'value' => $request->validated('value'),
            'label' => $request->validated('label'),
            'sort_order' => ($component->options()->max('sort_order') ?? 0) + 1,
            'image_path' => $request->hasFile('image')
                ? $request->file('image')->store('component-options', 'public')
                : null,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Opcion agregada.')]);

        return to_route('admin.components.edit', $component);
    }

    public function update(UpdateComponentOptionRequest $request, Component $component, ComponentOption $option): RedirectResponse
    {
        abort_if($option->component_id !== $component->id, 404);

        $attributes = [
            'value' => $request->validated('value'),
            'label' => $request->validated('label'),
        ];

        if ($request->hasFile('image')) {
            if ($option->image_path) {
                Storage::disk('public')->delete($option->image_path);
            }

            $attributes['image_path'] = $request->file('image')->store('component-options', 'public');
        }

        $option->update($attributes);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Opcion actualizada.')]);

        return to_route('admin.components.edit', $component);
    }

    public function destroy(Component $component, ComponentOption $option): RedirectResponse
    {
        abort_if($option->component_id !== $component->id, 404);

        if ($option->image_path) {
            Storage::disk('public')->delete($option->image_path);
        }

        $option->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Opcion eliminada.')]);

        return to_route('admin.components.edit', $component);
    }
}
