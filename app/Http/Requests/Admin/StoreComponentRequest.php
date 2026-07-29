<?php

namespace App\Http\Requests\Admin;

use App\Enums\InputType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreComponentRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // "quantity" is reserved: the quote engine treats it as the
            // PER_UNIT_TIERED quantity role, and the customer-facing
            // quantity picker is generated from PricingTier, not from a
            // Component, so a Component coded "quantity" would never
            // actually be used for anything.
            'code' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:components,code', Rule::notIn(['quantity'])],
            'label' => ['required', 'string', 'max:255'],
            'input_type' => ['required', new Enum(InputType::class)],
        ];
    }
}
