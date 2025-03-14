<?php

namespace App\Http\Requests\Admin\Product;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'product_code' => 'nullable|string|max:50|unique:products',
            'category_id' => 'required|exists:categories,id',
            'price' => 'required_without:variants|required|numeric|min:0|max:999999999',
            'discount_price' => 'nullable|numeric|min:0|max:999999999',
            'description' => 'nullable|string', 
            'image_thumnail' => 'required|image|mimes:jpeg,png,jpg,gif|max:30720',
            'gallery.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:30720',
            'variants' => 'array|nullable',
            'variants.*.sku' => 'required_with:variants|string|max:50|distinct',
            'variants.*.price' => 'required_with:variants|numeric|min:0|max:999999999',
            'variants.*.quantity' => 'required_with:variants|numeric|min:0|max:999999999',
            'variants.*.values' => 'required_with:variants|array',
            'variants.*.values.*' => 'exists:variant_values,id'
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
            'price.required' => 'Giá sản phẩm là bắt buộc',
            'price.numeric' => 'Giá sản phẩm phải là số',
            'price.min' => 'Giá sản phẩm phải lớn hơn 0',
            'price.max' => 'Giá sản phẩm không được vượt quá 999,999,999 VNĐ',
            'discount_price.numeric' => 'Giá khuyến mãi phải là số',
            'discount_price.min' => 'Giá khuyến mãi phải lớn hơn 0',
            'discount_price.max' => 'Giá khuyến mãi không được vượt quá 999,999,999 VNĐ',
            'image_thumnail.required' => 'Ảnh đại diện là bắt buộc',
            'image_thumnail.image' => 'File phải là ảnh',
            'image_thumnail.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif',
            'image_thumnail.max' => 'Kích thước ảnh tối đa là 30MB',
            'gallery.*.image' => 'File phải là ảnh',
            'gallery.*.mimes' => 'Ảnh phải có định dạng: jpeg, png, jpg, gif',
            'gallery.*.max' => 'Kích thước ảnh tối đa là 30MB',
            'variants.*.sku.required_with' => 'Mã SKU là bắt buộc cho mỗi biến thể',
            'variants.*.sku.distinct' => 'Mã SKU không được trùng lặp',
            'variants.*.price.required_with' => 'Giá là bắt buộc cho mỗi biến thể',
            'variants.*.price.numeric' => 'Giá biến thể phải là số',
            'variants.*.price.min' => 'Giá biến thể phải lớn hơn 0',
            'variants.*.price.max' => 'Giá biến thể không được vượt quá 999,999,999 VNĐ',
            'variants.*.quantity.required_with' => 'Số lượng là bắt buộc cho mỗi biến thể',
            'variants.*.quantity.numeric' => 'Số lượng biến thể phải là số',
            'variants.*.quantity.min' => 'Số lượng biến thể không được nhỏ hơn 0',
            'variants.*.values.required_with' => 'Giá trị biến thể là bắt buộc',
            'variants.*.values.*.exists' => 'Giá trị biến thể không tồn tại'
        ];
    }
}
