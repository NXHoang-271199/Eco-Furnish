<?php

namespace App\Http\Requests\Admin\VariantValue;

use Illuminate\Foundation\Http\FormRequest;

class StoreVariantValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'value' => 'required|string|max:100'
        ];
    }

    public function messages(): array
    {
        return [
            'value.required' => 'Giá trị biến thể là bắt buộc',
            'value.max' => 'Giá trị biến thể không được vượt quá 100 ký tự'
        ];
    }
} 