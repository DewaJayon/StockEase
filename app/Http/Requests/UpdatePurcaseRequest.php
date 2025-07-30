<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UpdatePurcaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && in_array(Auth::user()->role, ['admin', 'warehouse']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "supplier_id"                   => "required|exists:suppliers,id",
            "date"                          => "required|date",
            "product_items"                 => "required|array|min:1",
            "product_items.*.product_id"    => "required|exists:products,id",
            "product_items.*.qty"           => "required|integer|min:1",
            "product_items.*.price"         => "required|numeric|min:1",
            "product_items.*.selling_price" => "required|numeric|min:1",
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has("date")) {
            $this->merge([
                "date" => Carbon::parse($this->date),
            ]);
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_items.*.product_id.required'   => 'A product is required',
            'product_items.*.qty.required'          => 'A quantity is required',
            'product_items.*.price.required'        => 'A price is required',
            'product_items.*.price.min'             => 'The price field must be at least 1.',
        ];
    }
}
