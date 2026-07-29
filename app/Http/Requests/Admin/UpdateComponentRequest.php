<?php

namespace App\Http\Requests\Admin;

use App\Enums\InputType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateComponentRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('components', 'code')->ignore($this->route('component')),
            ],
            'label' => ['required', 'string', 'max:255'],
            'input_type' => ['required', new Enum(InputType::class)],
        ];
    }
}
