<?php
// --- Validation Rules for Storing a New Product (Simplified Tags) ---
namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'sub_category_id' => 'required|exists:sub_categories,id',
            'product_name' => 'required|string|max:255|unique:products,product_name',
            'product_code' => 'required|string|unique:products,product_code',
            'product_description' => 'nullable|string',
            'product_size' => 'nullable|string|max:100',
            'product_weight' => 'nullable|numeric|min:0',
            'product_price' => 'required|numeric|min:0',
            'product_visibility' => 'required|boolean',
            
            // --- THE FIX IS HERE: 'tags' is now validated as a string ---
            'tags' => 'nullable|string|max:255',

            'product_mainimage' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'product_imagelist' => 'nullable|array',
            'product_imagelist.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }
}