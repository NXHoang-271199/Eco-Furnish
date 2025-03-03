<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodRequest extends FormRequest
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
            'name' => 'required|max:255',
            'image' => 'nullable|image|mimes:png,jpg,jpeg,gif'
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'Tên phương thức bắt buộc điền',
            'name.max' => 'Tên phương thức không được quá 255 kí tự',
            'image.image' => 'Hình ảnh không hợp lệ'
        ];
    }
}
