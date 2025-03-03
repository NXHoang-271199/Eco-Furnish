<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
        $userId = $this->route('user') ?? $this->route('id');
        return [
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'age' => 'required|integer|max:90',
            'password' => $userId ? 'nullable' : 'required',
            'role_id' => 'required|exists:roles,id',
            'address' => 'required|max:255',
            'avatar' => $this->hasFile('avatar') ? 'image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable',

        ];
    }
    public function messages()
    {
        return [
            'name.required' => 'Tên không được để trống!',
            'name.string' => 'Tên phải là chuỗi ký tự!',
            'name.max' => 'Tên không được dài hơn 255 ký tự!',

            'email.required' => 'Email không được để trống!',
            'email.string' => 'Email phải là chuỗi ký tự!',
            'email.email' => 'Email không hợp lệ!',
            'email.max' => 'Email không được dài hơn 255 ký tự!',
            'email.unique' => 'Email này đã tồn tại, vui lòng chọn email khác!',

            'age.required' => 'Tuổi không được để trống!',
            'age.integer' => 'Tuổi phải là số nguyên!',
            'age.max' => 'Tuổi không được lớn hơn 90!',

            'password.required' => 'Mật khâu không được để trống!',

            'role_id.required' => 'Vai trò không được để trống!',
            'role_id.exists' => 'Vai trò không hợp lệ!',

            'address.required' => 'Địa chỉ không được để trống!',
            'address.max' => 'Địa chỉ không được dài hơn 255 ký tự!',


            'avatar.image' => 'Ảnh đại diện phải là tệp hình ảnh!',
            'avatar.mimes' => 'Ảnh đại diện phải có định dạng jpeg, png, jpg, gif, webp!',
            'avatar.max' => 'Ảnh đại diện khó quá 20MB!',
        ];
    }
}
