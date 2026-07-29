<?php

namespace App\Http\Requests\Admin;

use App\Enums\ModifierType;
use App\Models\CatalogProduct;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreOptionPriceModifierRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $catalogProduct = $this->route('catalog_product');

        abort_unless($catalogProduct instanceof CatalogProduct, 404);

        return [
            'component_option_id' => [
                'required',
                'integer',
                'exists:component_options,id',
                Rule::unique('option_price_modifiers', 'component_option_id')
                    ->where(fn ($query) => $query->where('pricing_profile_id', $catalogProduct->pricingProfile->id)),
            ],
            'modifier_type' => ['required', new Enum(ModifierType::class)],
            'value' => ['required', 'numeric'],
        ];
    }
}
