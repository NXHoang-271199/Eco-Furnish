<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserApiController extends Controller
{
    // 1. Lấy danh sách user (chỉ client)
    public function index()
    {
        $users = User::whereHas('role', function($query) {
            $query->where('name', 'Client');
        })
        ->where('is_active', 1) // Chỉ lấy user đang active
        ->get()
        ->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'slug' => Str::slug($user->name),
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $users
        ]);
    }

    // 2. Xem chi tiết user theo slug
    public function show($slug)
    {
        $name = str_replace('-', ' ', $slug);
        $user = User::whereHas('role', function($query) {
            $query->where('name', 'Client');
        })
        ->where('name', 'LIKE', $name)
        ->where('is_active', 1)
        ->first();
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người dùng'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'slug' => Str::slug($user->name),
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'joined_date' => $user->created_at->format('d/m/Y')
            ]
        ]);
    }

    // 3. Đăng ký tài khoản mới
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:5',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:15000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        // Lấy role Client
        $clientRole = Role::where('name', 'Client')->first();
        
        // Upload avatar nếu có
        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('uploads/avatars', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $clientRole->id,
            'avatar' => $avatarPath,
            'is_active' => 1
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng ký tài khoản thành công',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null
            ]
        ], 201);
    }

    // 4. Cập nhật thông tin cá nhân
    public function updateProfile(Request $request, $id)
    {
        $user = User::whereHas('role', function($query) {
            $query->where('name', 'Client');
        })->find($id);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người dùng'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:15000',
            'current_password' => 'required_with:new_password|string',
            'new_password' => 'string|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        // Kiểm tra mật khẩu cũ nếu muốn đổi mật khẩu
        if ($request->has('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mật khẩu hiện tại không đúng'
                ], 400);
            }
            $user->password = Hash::make($request->new_password);
        }

        // Cập nhật avatar nếu có
        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ nếu có
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $user->avatar = $request->file('avatar')->store('uploads/avatars', 'public');
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thông tin thành công',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null
            ]
        ]);
    }
} 