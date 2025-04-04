<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuickOrderRequest extends FormRequest
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
            'product_id' => 'required|exists:products,id', // Validate product_id
            'quantity' => 'required|integer|min:1', // Validate quantity
            'product_variant_id' => 'nullable|exists:product_variants,id', // Validate product_variant_id (optional)
            'voucher_id' => 'nullable|exists:vouchers,id', // Validate voucher_id (optional)
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255',
            'user_phone' => 'required|string|max:20',
            'user_address' => 'required|string|max:500',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ];
    }
    public function messages()
    {
        return [
            'product_id.required' => 'Sản phẩm không thể để trống.',
            'product_id.exists' => 'Sản phẩm không tồn tại.',
            'quantity.required' => 'Số lượng không thể để trống.',
            'quantity.integer' => 'Số lượng phải là một số nguyên.',
            'quantity.min' => 'Số lượng phải lớn hơn hoặc bằng 1.',
            'product_variant_id.exists' => 'Biến thể sản phẩm không tồn tại.',
            'user_name.required' => 'Tên khách hàng không thể để trống.',
            'user_email.required' => 'Email khách hàng không thể để trống.',
            'user_phone.required' => 'Số điện thoại khách hàng không thể để trống.',
            'user_address.required' => 'Địa chỉ khách hàng không thể để trống.',
            'payment_method_id.required' => 'Phương thức thanh toán không thể để trống.',
        ];
    }
}
