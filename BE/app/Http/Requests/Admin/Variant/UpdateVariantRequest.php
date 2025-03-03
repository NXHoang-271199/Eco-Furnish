<?php

namespace App\Http\Requests\Admin\Variant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('variants', 'name')
                    ->where(function ($query) {
                        return $query->where('id', '!=', $this->route('variant')->id)
                            ->whereNull('deleted_at');
                    })
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên biến thể là bắt buộc',
            'name.max' => 'Tên biến thể không được vượt quá 100 ký tự',
            'name.unique' => 'Tên biến thể đã tồn tại'
        ];
    }
} 