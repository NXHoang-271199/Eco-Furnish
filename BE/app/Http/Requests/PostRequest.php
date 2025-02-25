<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PostRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:category_posts,id', 
            'image_thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'status' => 'required|in:0,1',
        ];
    }

    public function messages()
{
    return [
        'title.required' => 'Tiêu đề không được để trống!',
        'title.string' => 'Tiêu đề phải là chuỗi ký tự!',
        'title.max' => 'Tiêu đề không được dài hơn 255 ký tự!',

        'category_id.required' => 'Danh mục không được để trống!',
        'category_id.exists' => 'Danh mục không hợp lệ!',

        'content.required' => 'Nội dung không được để trống!',

        'status.required' => 'Trạng thái không được để trống!',

    
    ];
}
}
