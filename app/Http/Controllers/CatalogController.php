<?php

namespace App\Http\Controllers;

use App\Enums\InputType;
use App\Models\CatalogProduct;
use App\Models\CatalogProductFaq;
use App\Models\CatalogProductReview;
use App\Models\Category;
use App\Models\Component;
use App\Models\Page;
use App\Models\PricingTier;
use App\Models\Shop;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(): Response
    {
        $shop = Shop::current();

        return Inertia::render('catalog/index', [
            'shopName' => $shop->name,
            'catalogProducts' => $shop->catalogProducts()
                ->where('is_active', true)
                ->with('productTemplate')
                ->orderBy('created_at')
                ->get()
                ->map(fn (CatalogProduct $catalogProduct) => [
                    'id' => $catalogProduct->id,
                    'slug' => $catalogProduct->slug,
                    'name' => $catalogProduct->name_override ?? $catalogProduct->productTemplate->name,
                    'image_url' => $catalogProduct->image_url,
                ]),
            'categories' => $shop->categories()
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }

    public function show(string $slug): Response
    {
        $shop = Shop::current();

        $catalogProduct = CatalogProduct::query()
            ->where('shop_id', $shop->id)
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if ($catalogProduct) {
            return $this->showProduct($catalogProduct);
        }

        $category = Category::query()
            ->where('shop_id', $shop->id)
            ->where('slug', $slug)
            ->first();

        if ($category) {
            return $this->showCategory($category);
        }

        $page = Page::query()
            ->where('shop_id', $shop->id)
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        abort_unless($page !== null, 404);

        return $this->showPage($page);
    }

    private function showProduct(CatalogProduct $catalogProduct): Response
    {
        $catalogProduct->load(
            'productTemplate.components.options',
            'pricingProfile.tiers',
            'shop',
            'faqs',
            'reviews',
        );

        return Inertia::render('catalog/show', [
            'catalogProduct' => [
                'id' => $catalogProduct->id,
                'slug' => $catalogProduct->slug,
                'name' => $catalogProduct->name_override ?? $catalogProduct->productTemplate->name,
                'image_url' => $catalogProduct->image_url,
                'description' => $catalogProduct->description,
                'details_content' => $catalogProduct->details_content,
                'currency' => $catalogProduct->shop->currency,
                'pricing_strategy' => $catalogProduct->productTemplate->pricing_strategy,
                'components' => $this->serializeComponents($catalogProduct->productTemplate->components),
                'pricing_tiers' => $catalogProduct->pricingProfile?->tiers
                    ->map(fn (PricingTier $tier) => [
                        'min_quantity' => $tier->min_quantity,
                        'max_quantity' => $tier->max_quantity,
                        // adjustment_percent itself never leaves the server -
                        // only its effect on the price does.
                        'unit_price' => $tier->effectiveUnitPrice(),
                        'total' => round($tier->effectiveUnitPrice() * $tier->min_quantity, 2),
                    ])
                    ->all() ?? [],
                'faqs' => $catalogProduct->faqs
                    ->map(fn (CatalogProductFaq $faq) => [
                        'question' => $faq->question,
                        'answer' => $faq->answer,
                    ]),
                'reviews' => $catalogProduct->reviews
                    ->map(fn (CatalogProductReview $review) => [
                        'author_name' => $review->author_name,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                    ]),
            ],
            'featuredProducts' => CatalogProduct::query()
                ->where('shop_id', $catalogProduct->shop_id)
                ->where('is_active', true)
                ->where('is_featured', true)
                ->where('id', '!=', $catalogProduct->id)
                ->with('productTemplate')
                ->orderBy('created_at')
                ->get()
                ->map(fn (CatalogProduct $featured) => [
                    'id' => $featured->id,
                    'slug' => $featured->slug,
                    'name' => $featured->name_override ?? $featured->productTemplate->name,
                    'image_url' => $featured->image_url,
                ]),
        ]);
    }

    private function showCategory(Category $category): Response
    {
        return Inertia::render('catalog/category', [
            'category' => [
                'name' => $category->name,
            ],
            'catalogProducts' => $category->catalogProducts()
                ->where('is_active', true)
                ->with('productTemplate')
                ->orderBy('created_at')
                ->get()
                ->map(fn (CatalogProduct $catalogProduct) => [
                    'id' => $catalogProduct->id,
                    'slug' => $catalogProduct->slug,
                    'name' => $catalogProduct->name_override ?? $catalogProduct->productTemplate->name,
                    'image_url' => $catalogProduct->image_url,
                ]),
        ]);
    }

    private function showPage(Page $page): Response
    {
        return Inertia::render('pages/show', [
            'page' => [
                'title' => $page->title,
                'content' => $page->content,
            ],
        ]);
    }

    /**
     * @param  Collection<int, Component>  $components
     * @return list<array{code: string, label: string, input_type: InputType, is_required: bool, options: list<array{value: string, label: string, image_url: string|null}>}>
     */
    private function serializeComponents(Collection $components): array
    {
        $serialized = [];

        foreach ($components->sortBy('pivot.sort_order') as $component) {
            $options = [];

            foreach ($component->options as $option) {
                $options[] = [
                    'value' => $option->value,
                    'label' => $option->label,
                    'image_url' => $option->image_url,
                ];
            }

            $serialized[] = [
                'code' => $component->code,
                'label' => $component->label,
                'input_type' => $component->input_type,
                'is_required' => $component->pivot->is_required,
                'options' => $options,
            ];
        }

        return $serialized;
    }
}
