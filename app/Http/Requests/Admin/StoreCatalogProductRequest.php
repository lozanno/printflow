<?php

namespace App\Http\Requests\Admin;

use App\Models\Shop;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCatalogProductRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shop = Shop::current();

        return [
            'product_template_id' => [
                'required',
                'integer',
                'exists:product_templates,id',
                Rule::unique('catalog_products', 'product_template_id')
                    ->where(fn ($query) => $query->where('shop_id', $shop->id)),
            ],
            'name_override' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'in:on'],
        ];
    }
}
