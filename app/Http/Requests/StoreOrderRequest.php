<?php

namespace App\Http\Requests;

use App\Enums\DeliveryType;
use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer.name' => ['required', 'string', 'max:255'],
            'customer.email' => ['required', 'email', 'max:255'],
            'customer.phone' => ['nullable', 'string', 'max:30'],

            'delivery_type' => ['required', Rule::enum(DeliveryType::class)],

            'shipping.recipient_name' => ['required_if:delivery_type,SHIP', 'string', 'max:255'],
            'shipping.phone' => ['required_if:delivery_type,SHIP', 'string', 'max:30'],
            'shipping.line1' => ['required_if:delivery_type,SHIP', 'string', 'max:255'],
            'shipping.line2' => ['nullable', 'string', 'max:255'],
            'shipping.city' => ['required_if:delivery_type,SHIP', 'string', 'max:255'],
            'shipping.state' => ['required_if:delivery_type,SHIP', 'string', 'max:255'],
            'shipping.postal_code' => ['required_if:delivery_type,SHIP', 'string', 'max:20'],

            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],

            'selections' => ['required', 'array'],
        ];
    }
}
