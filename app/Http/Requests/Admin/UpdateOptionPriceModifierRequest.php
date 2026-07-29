<?php

namespace App\Http\Requests\Admin;

use App\Enums\ModifierType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOptionPriceModifierRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'modifier_type' => ['required', new Enum(ModifierType::class)],
            'value' => ['required', 'numeric'],
        ];
    }
}
