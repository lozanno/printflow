<?php

namespace App\Http\Requests\Admin;

use App\Enums\InputType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreComponentRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:components,code'],
            'label' => ['required', 'string', 'max:255'],
            'input_type' => ['required', new Enum(InputType::class)],
        ];
    }
}
