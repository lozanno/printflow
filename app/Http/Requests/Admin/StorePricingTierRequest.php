<?php

namespace App\Http\Requests\Admin;

use App\Enums\PricingStrategy;
use App\Models\CatalogProduct;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePricingTierRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $catalogProduct = $this->route('catalog_product');

        abort_unless($catalogProduct instanceof CatalogProduct, 404);
        abort_unless($catalogProduct->productTemplate->pricing_strategy === PricingStrategy::PerUnitTiered, 422);

        return [
            'min_quantity' => ['required', 'integer', 'min:0'],
            'max_quantity' => ['nullable', 'integer', 'gt:min_quantity'],
            'unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
