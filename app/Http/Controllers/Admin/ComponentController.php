<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreComponentRequest;
use App\Http\Requests\Admin\UpdateComponentRequest;
use App\Models\Component;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ComponentController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('admin/components/index', [
            'components' => Component::query()
                ->withCount('options')
                ->orderBy('label')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/components/create');
    }

    public function store(StoreComponentRequest $request): RedirectResponse
    {
        Component::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Componente creado.')]);

        return to_route('admin.components.index');
    }

    public function edit(Component $component): Response
    {
        return Inertia::render('admin/components/edit', [
            'component' => $component->load('options'),
        ]);
    }

    public function update(UpdateComponentRequest $request, Component $component): RedirectResponse
    {
        $component->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Componente actualizado.')]);

        return to_route('admin.components.edit', $component);
    }

    public function destroy(Component $component): RedirectResponse
    {
        $component->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Componente eliminado.')]);

        return to_route('admin.components.index');
    }
}
