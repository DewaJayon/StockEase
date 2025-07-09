<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Enums\UnitEnum;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
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
            "category_id"       => "required|exists:categories,id",
            "name"              => "required|string|max:255",
            "sku"               => "required|string|max:255",
            "barcode"           => "required|string|max:255",
            'unit'              => ['required', 'string', Rule::in(UnitEnum::keys())],
            "stock"             => "required|numeric|min:0",
            "purchase_price"    => "required|numeric|min:0",
            "selling_price"     => "required|numeric|min:0",
            "alert_stock"       => "required|numeric|min:0",
            "image_path"        => "nullable|image|mimes:png,jpg,jpeg,webp|max:2048",
        ];
    }
}
