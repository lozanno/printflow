<?php

namespace App\Http\Requests\Admin;

use App\Enums\PricingStrategy;
use App\Models\CatalogProduct;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCatalogProductRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $catalogProduct = $this->route('catalog_product');

        abort_unless($catalogProduct instanceof CatalogProduct, 404);

        $rules = [
            'name_override' => ['nullable', 'string', 'max:255'],
            // HTML checkboxes submit "on" (or omit the field), see
            // StoreProductTemplateComponentRequest for why this isn't
            // the strict `boolean` rule.
            'is_active' => ['sometimes', 'in:on'],
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
