<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductionStage;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateOrderProductionStageRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'production_stage' => ['required', new Enum(ProductionStage::class)],
        ];
    }
}
