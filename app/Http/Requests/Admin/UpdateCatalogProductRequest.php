<?php

namespace App\Http\Requests\Admin;

use App\Enums\PricingStrategy;
use App\Models\CatalogProduct;
use App\Models\Shop;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCatalogProductRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $catalogProduct = $this->route('catalog_product');

        abort_unless($catalogProduct instanceof CatalogProduct, 404);

        $shop = Shop::current();

        $rules = [
            'name_override' => ['nullable', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('catalog_products', 'slug')
                    ->where(fn ($query) => $query->where('shop_id', $shop->id))
                    ->ignore($catalogProduct),
                Rule::unique('categories', 'slug')->where(fn ($query) => $query->where('shop_id', $shop->id)),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
            // HTML checkboxes submit "on" (or omit the field), see
            // StoreProductTemplateComponentRequest for why this isn't
            // the strict `boolean` rule.
            'is_active' => ['sometimes', 'in:on'],
            'is_featured' => ['sometimes', 'in:on'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => [
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->where('shop_id', $shop->id)),
            ],
        ];

        $strategy = $catalogProduct->productTemplate->pricing_strategy;

        if (in_array($strategy, [PricingStrategy::PerArea, PricingStrategy::PerAreaWithSetup], true)) {
            $rules['rate_per_sqm'] = ['required', 'numeric', 'min:0'];
        }

        if ($strategy === PricingStrategy::PerAreaWithSetup) {
            $rules['setup_fee'] = ['required', 'numeric', 'min:0'];
        }

        return $rules;
    }
}
