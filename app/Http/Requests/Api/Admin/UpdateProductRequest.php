<?php
// --- Validation Rules for Updating an Existing Product ---
namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool { return true; }

    /**
     * Get the validation rules that apply to the request.
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'sub_category_id' => 'sometimes|required|exists:sub_categories,id',
            'product_name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'product_code' => ['sometimes', 'required', 'string', Rule::unique('products', 'product_code')->ignore($product->id)],
            'product_description' => 'nullable|string',
            'product_size' => 'nullable|string|max:100',
            'product_weight' => 'nullable|numeric|min:0',
            'product_price' => 'sometimes|required|numeric|min:0',
            'product_visibility' => 'sometimes|required|boolean',
            'product_similaritytags' => 'nullable|array',
            'product_similaritytags.*' => 'string|max:50',
            
            // File Upload Validations
            'product_mainimage' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'product_imagelist' => 'nullable|array',
            'product_imagelist.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',

            // Validates the list of existing images to keep and the remove flag
            'existing_imagelist_paths' => 'nullable|array',
            'existing_imagelist_paths.*' => 'string',
            'remove_main_image' => 'nullable|boolean',
        ];
    }
}