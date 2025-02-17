<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'product_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products')->ignore($this->product)
            ],
            'category_id' => 'required|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'image_thumnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên sản phẩm là bắt buộc',
            'name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự',
            'product_code.max' => 'Mã sản phẩm không được vượt quá 50 ký tự',
            'product_code.unique' => 'Mã sản phẩm đã tồn tại',
            'category_id.required' => 'Danh mục là bắt buộc',
            'category_id.exists' => 'Danh mục không tồn tại',
            'price.required' => 'Giá sản phẩm là bắt buộc',
            'price.numeric' => 'Giá sản phẩm phải là số',
            'price.min' => 'Giá sản phẩm phải lớn hơn 0',
            'image_thumnail.image' => 'File phải là ảnh',
            'image_thumnail.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif',
            'image_thumnail.max' => 'Kích thước ảnh tối đa là 2MB',
            'images.*.image' => 'File phải là ảnh',
            'images.*.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif',
            'images.*.max' => 'Kích thước ảnh tối đa là 2MB'
        ];
    }
} 