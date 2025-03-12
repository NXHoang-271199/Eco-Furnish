<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoucherRequest extends FormRequest
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
            'code' => 'required|unique:vouchers,code,' . $this->voucher . '|max:255',
            'discount_percentage' => 'required|numeric|min:0|max:100',
            'max_discount_amount' => 'required|numeric|min:0',
            'min_order_value' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'usage_limit' => 'required|integer|min:1',
        ];
    }
    public function messages()
    {
        return [
            'code.required' => 'Mã voucher không được để trống!',
            'code.unique' => 'Mã voucher đã tồn tại!',
            'code.max' => 'Mã voucher không được quá 255 ký tự!',
            'discount_percentage.required' => 'Vui lòng nhập phần trăm giảm giá!',
            'discount_percentage.numeric' => 'Phần trăm giảm giá phải là số!',
            'discount_percentage.min' => 'Phần trăm giảm giá không được nhỏ hơn 0!',
            'discount_percentage.max' => 'Phần trăm giảm giá không được vượt quá 100!',
            'max_discount_amount.required' => 'Vui lòng nhập số tiền giảm tối đa!',
            'min_order_value.required' => 'Vui lòng nhập giá trị đơn hàng tối thiểu!',
            'start_date.required' => 'Vui lòng nhập ngày bắt đầu!',
            'end_date.required' => 'Vui lòng nhập ngày kết thúc!',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu!',
            'usage_limit.required' => 'Vui lòng nhập số lần sử dụng tối đa!',
            'usage_limit.integer' => 'Số lần sử dụng phải là số nguyên!',
            'usage_limit.min' => 'Số lần sử dụng tối thiểu là 1!',
        ];
    }
}
