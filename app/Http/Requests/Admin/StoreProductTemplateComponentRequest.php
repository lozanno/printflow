<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductTemplate;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductTemplateComponentRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productTemplate = $this->route('product_template');

        abort_unless($productTemplate instanceof ProductTemplate, 404);

        return [
            'component_id' => [
                'required',
                'integer',
                'exists:components,id',
                Rule::unique('product_template_components', 'component_id')
                    ->where(fn ($query) => $query->where('product_template_id', $productTemplate->id)),
            ],
            // HTML checkboxes submit "on" (or omit the field entirely), not a
            // strict boolean, so this is read via $request->boolean() in the
            // controller rather than validated() here.
            'is_required' => ['sometimes', 'in:on'],
        ];
    }
}
