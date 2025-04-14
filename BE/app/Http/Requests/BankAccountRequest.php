<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankAccountRequest extends FormRequest
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
            'bank_code' => 'required|string|max:10',
            'bank_name' => 'required|string|max:100',
            'bank_logo_url' => 'nullable|string|max:255',
            'account_holder_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'is_default' => 'boolean'
        ];
    }
    public function messages()
    {
        return [
            'bank_code.required' => 'Mã ngân hàng là bắt buộc.',
            'bank_name.required' => 'Tên ngân hàng là bắt buộc.',
            'account_holder_name.required' => 'Tên chủ tài khoản là bắt buộc.',
            'bank_account_number.required' => 'Số tài khoản ngân hàng là bắt buộc.',
        ];
    }
}
