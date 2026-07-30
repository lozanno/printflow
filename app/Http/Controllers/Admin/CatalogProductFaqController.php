<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCatalogProductFaqRequest;
use App\Http\Requests\Admin\UpdateCatalogProductFaqRequest;
use App\Models\CatalogProduct;
use App\Models\CatalogProductFaq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CatalogProductFaqController extends Controller
{
    public function store(StoreCatalogProductFaqRequest $request, CatalogProduct $catalogProduct): RedirectResponse
    {
        $catalogProduct->faqs()->create([
            'question' => $request->validated('question'),
            'answer' => $request->validated('answer'),
            'sort_order' => ($catalogProduct->faqs()->max('sort_order') ?? 0) + 1,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pregunta agregada.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }

    public function update(UpdateCatalogProductFaqRequest $request, CatalogProduct $catalogProduct, CatalogProductFaq $faq): RedirectResponse
    {
        abort_if($faq->catalog_product_id !== $catalogProduct->id, 404);

        $faq->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pregunta actualizada.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }

    public function move(Request $request, CatalogProduct $catalogProduct, CatalogProductFaq $faq): RedirectResponse
    {
        abort_if($faq->catalog_product_id !== $catalogProduct->id, 404);

        $direction = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ])['direction'];

        $faqs = $catalogProduct->faqs()->get();
        $index = $faqs->search(fn (CatalogProductFaq $candidate) => $candidate->id === $faq->id);

        abort_if($index === false, 404);

        $swapWithIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapWithIndex >= 0 && $swapWithIndex < $faqs->count()) {
            $swapWith = $faqs[$swapWithIndex];

            DB::transaction(function () use ($faq, $swapWith): void {
                $order = $faq->sort_order;
                $faq->update(['sort_order' => $swapWith->sort_order]);
                $swapWith->update(['sort_order' => $order]);
            });
        }

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }

    public function destroy(CatalogProduct $catalogProduct, CatalogProductFaq $faq): RedirectResponse
    {
        abort_if($faq->catalog_product_id !== $catalogProduct->id, 404);

        $faq->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pregunta eliminada.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }
}
