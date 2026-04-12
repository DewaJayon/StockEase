<?php

namespace App\Http\Requests\Sale;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class PosCheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'in:cash,qris'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'paid' => ['required_if:payment_method,cash', 'numeric', 'min:0', 'max:999999999999999'],
            'order_id' => ['nullable', 'string'],
        ];
    }
}
