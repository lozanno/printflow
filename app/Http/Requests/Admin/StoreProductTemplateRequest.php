<?php

namespace App\Http\Requests\Admin;

use App\Enums\PricingStrategy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreProductTemplateRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:product_templates,code'],
            'name' => ['required', 'string', 'max:255'],
            'pricing_strategy' => ['required', new Enum(PricingStrategy::class)],
        ];
    }
}
