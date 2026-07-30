<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCatalogProductReviewRequest;
use App\Http\Requests\Admin\UpdateCatalogProductReviewRequest;
use App\Models\CatalogProduct;
use App\Models\CatalogProductReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CatalogProductReviewController extends Controller
{
    public function store(StoreCatalogProductReviewRequest $request, CatalogProduct $catalogProduct): RedirectResponse
    {
        $catalogProduct->reviews()->create([
            'author_name' => $request->validated('author_name'),
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
            'sort_order' => ($catalogProduct->reviews()->max('sort_order') ?? 0) + 1,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Reseña agregada.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }

    public function update(UpdateCatalogProductReviewRequest $request, CatalogProduct $catalogProduct, CatalogProductReview $review): RedirectResponse
    {
        abort_if($review->catalog_product_id !== $catalogProduct->id, 404);

        $review->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Reseña actualizada.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }

    public function move(Request $request, CatalogProduct $catalogProduct, CatalogProductReview $review): RedirectResponse
    {
        abort_if($review->catalog_product_id !== $catalogProduct->id, 404);

        $direction = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ])['direction'];

        $reviews = $catalogProduct->reviews()->get();
        $index = $reviews->search(fn (CatalogProductReview $candidate) => $candidate->id === $review->id);

        abort_if($index === false, 404);

        $swapWithIndex = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapWithIndex >= 0 && $swapWithIndex < $reviews->count()) {
            $swapWith = $reviews[$swapWithIndex];

            DB::transaction(function () use ($review, $swapWith): void {
                $order = $review->sort_order;
                $review->update(['sort_order' => $swapWith->sort_order]);
                $swapWith->update(['sort_order' => $order]);
            });
        }

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }

    public function destroy(CatalogProduct $catalogProduct, CatalogProductReview $review): RedirectResponse
    {
        abort_if($review->catalog_product_id !== $catalogProduct->id, 404);

        $review->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Reseña eliminada.')]);

        return to_route('admin.catalog-products.edit', $catalogProduct);
    }
}
