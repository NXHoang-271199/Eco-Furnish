<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Http\Controllers\CategoryController;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Lấy danh sách keys hợp lệ từ Controller hoặc config
        $validSpaceKeys = array_keys(app(CategoryController::class)->getSpaceTypes());

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->whereNull('deleted_at')
            ],
            // Validation cho mảng spaces
            'spaces' => [
                'nullable', // Cho phép không chọn không gian nào
                'array' // Phải là một mảng
            ],
            'spaces.*' => [
                'string', // Mỗi phần tử trong mảng phải là string
                Rule::in($validSpaceKeys) // Mỗi phần tử phải là một space key hợp lệ
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên danh mục là bắt buộc',
            'name.max' => 'Tên danh mục không được vượt quá 255 ký tự',
            'name.unique' => 'Tên danh mục đã tồn tại',
            // Xóa message cho space_type.in
            'spaces.array' => 'Định dạng không gian không hợp lệ.',
            'spaces.*.string' => 'Mã không gian không hợp lệ.',
            'spaces.*.in' => 'Không gian được chọn không hợp lệ.'
        ];
    }
} 