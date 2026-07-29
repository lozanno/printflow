<?php

namespace App\Http\Requests\Admin;

use App\Enums\InputType;
use App\Models\Component;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreComponentOptionRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $component = $this->route('component');

        abort_unless($component instanceof Component, 404);

        $valueRules = [
            'required',
            'string',
            'max:255',
            Rule::unique('component_options', 'value')
                ->where(fn ($query) => $query->where('component_id', $component->id)),
        ];

        // For a size-preset option on a DIMENSIONS component, the value
        // encodes "widthxheight" in millimeters (e.g. "420x594") so the
        // frontend can parse it without a schema change.
        if ($component->input_type === InputType::Dimensions) {
            $valueRules[] = 'regex:/^\d+x\d+$/';
        }

        return [
            'value' => $valueRules,
            'label' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,webp', 'max:4096'],
        ];
    }
}
