<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePageRequest;
use App\Http\Requests\Admin\UpdatePageRequest;
use App\Models\Page;
use App\Models\Shop;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function index(): Response
    {
        $shop = Shop::current();

        return Inertia::render('admin/pages/index', [
            'pages' => $shop->pages()
                ->orderBy('title')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/pages/create');
    }

    public function store(StorePageRequest $request): RedirectResponse
    {
        $shop = Shop::current();

        $page = $shop->pages()->create([
            'title' => $request->validated('title'),
            'slug' => $request->validated('slug'),
            'content' => $request->validated('content'),
            'is_published' => $request->boolean('is_published'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pagina creada.')]);

        return to_route('admin.pages.edit', $page);
    }

    public function edit(Page $page): Response
    {
        $this->ensureBelongsToCurrentShop($page);

        return Inertia::render('admin/pages/edit', [
            'page' => $page,
        ]);
    }

    public function update(UpdatePageRequest $request, Page $page): RedirectResponse
    {
        $this->ensureBelongsToCurrentShop($page);

        $page->update([
            'title' => $request->validated('title'),
            'slug' => $request->validated('slug'),
            'content' => $request->validated('content'),
            'is_published' => $request->boolean('is_published'),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pagina actualizada.')]);

        return to_route('admin.pages.edit', $page);
    }

    public function destroy(Page $page): RedirectResponse
    {
        $this->ensureBelongsToCurrentShop($page);

        $page->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pagina eliminada.')]);

        return to_route('admin.pages.index');
    }

    private function ensureBelongsToCurrentShop(Page $page): void
    {
        abort_unless($page->shop_id === Shop::current()->id, 404);
    }
}
