<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
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
    public function rules()
    {
        return [
            'user_name' => 'required|string|max:255',
            'user_email' => 'required|email|max:255',
            'user_phone' => 'required|string|max:20',
            'user_address' => 'required|string|max:500',
            'payment_method_id' => 'required|exists:payment_methods,id',
            'voucher_id' => 'nullable|exists:vouchers,id',
        ];
    }

    public function messages()
    {
        return [
            'user_name.required' => 'Tên người dùng là bắt buộc.',
            'user_email.required' => 'Email là bắt buộc.',
            'user_email.email' => 'Email không hợp lệ.',
            'user_phone.required' => 'Số điện thoại là bắt buộc.',
            'user_address.required' => 'Địa chỉ là bắt buộc.',
            'payment_method_id.required' => 'Phương thức thanh toán là bắt buộc.',
            'payment_method_id.exists' => 'Phương thức thanh toán không hợp lệ.',
            'voucher_id.exists' => 'Voucher không hợp lệ.',
        ];
    }
}
