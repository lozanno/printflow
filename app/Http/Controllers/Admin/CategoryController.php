<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $shop = Shop::current();

        return Inertia::render('admin/categories/index', [
            'categories' => $shop->categories()
                ->withCount('catalogProducts')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/categories/create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $shop = Shop::current();

        $shop->categories()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Categoria creada.')]);

        return to_route('admin.categories.index');
    }

    public function edit(Category $category): Response
    {
        $this->ensureBelongsToCurrentShop($category);

        return Inertia::render('admin/categories/edit', [
            'category' => $category,
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->ensureBelongsToCurrentShop($category);

        $category->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Categoria actualizada.')]);

        return to_route('admin.categories.edit', $category);
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->ensureBelongsToCurrentShop($category);

        $category->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Categoria eliminada.')]);

        return to_route('admin.categories.index');
    }

    private function ensureBelongsToCurrentShop(Category $category): void
    {
        abort_unless($category->shop_id === Shop::current()->id, 404);
    }
}
