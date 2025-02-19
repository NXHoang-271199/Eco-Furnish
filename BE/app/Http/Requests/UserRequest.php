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
        $userId = $this->route('id');
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255', Rule::unique('users', 'email')->ignore($userId),
            'age' => 'required|integer',
            'role_id' => 'required|exists:roles,id',
            'address' => 'nullable|string|max:255',
            'password' => 'required|string|min:3', // Cần thay đổi cho trường hợp update (nếu không cập nhật password thì có thể bỏ)
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
