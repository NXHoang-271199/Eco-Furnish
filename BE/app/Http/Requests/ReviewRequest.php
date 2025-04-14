<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'order_id' => 'required|exists:orders,id',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
    public function messages()
    {
        return [
            'order_id.required' => 'Đơn hàng không thể để trống.',
            'product_id.required' => 'Sản phẩm không thể để trống.',
            'rating.required' => 'Đánh giá sao không thể để trống.',
            'rating.integer' => 'Đánh giá phải là một số nguyên.',
            'rating.min' => 'Đánh giá phải ít nhất 1 sao.',
            'rating.max' => 'Đánh giá không thể quá 5 sao.',
            'images.*.image' => 'Ảnh phải là một tệp hình ảnh.',
            'images.*.mimes' => 'Ảnh phải có định dạng jpeg, png, jpg, hoặc gif.',
            'images.*.max' => 'Ảnh không được lớn hơn 2MB.',
        ];
    }
}
