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
                'nullable',
                'string',
                'max:50',
                Rule::unique('products')->ignore($this->product)
            ],
            'category_id' => 'exists:categories,id',
            'price' => 'required_without:variants|numeric|min:0',
            'description' => 'nullable|string',
            'image_thumnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'variants' => 'array|nullable',
            'variants.*.sku' => 'required_with:variants|string|max:50|distinct',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.variant_values' => 'required_with:variants|array',
            'variants.*.variant_values.*' => 'required|exists:variant_values,id'
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
            'price.required_without' => 'Giá sản phẩm là bắt buộc nếu không có biến thể',
            'price.numeric' => 'Giá sản phẩm phải là số',
            'price.min' => 'Giá sản phẩm phải lớn hơn 0',
            'image_thumnail.image' => 'File phải là ảnh',
            'image_thumnail.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif',
            'image_thumnail.max' => 'Kích thước ảnh tối đa là 2MB',
            'gallery.*.image' => 'File phải là ảnh',
            'gallery.*.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif',
            'gallery.*.max' => 'Kích thước ảnh tối đa là 2MB',
            'variants.*.sku.required_with' => 'Mã SKU là bắt buộc cho mỗi biến thể',
            'variants.*.sku.distinct' => 'Mã SKU không được trùng lặp',
            'variants.*.price.required_with' => 'Giá là bắt buộc cho mỗi biến thể',
            'variants.*.price.numeric' => 'Giá biến thể phải là số',
            'variants.*.price.min' => 'Giá biến thể phải lớn hơn 0',
            'variants.*.variant_values.required_with' => 'Giá trị biến thể là bắt buộc',
            'variants.*.variant_values.*.required' => 'Vui lòng chọn giá trị cho tất cả các biến thể',
            'variants.*.variant_values.*.exists' => 'Giá trị biến thể không tồn tại'
        ];
    }
} 