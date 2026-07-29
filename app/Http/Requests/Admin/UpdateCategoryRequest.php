<?php

namespace App\Http\Requests\Admin;

use App\Models\Shop;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $shop = Shop::current();

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('categories', 'slug')
                    ->where(fn ($query) => $query->where('shop_id', $shop->id))
                    ->ignore($this->route('category')),
                Rule::unique('catalog_products', 'slug')->where(fn ($query) => $query->where('shop_id', $shop->id)),
            ],
        ];
    }
}
