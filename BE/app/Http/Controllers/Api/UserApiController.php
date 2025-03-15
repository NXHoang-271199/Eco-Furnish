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
use Illuminate\Support\Facades\Auth;

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
                'email' => $user->email,
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
            'is_active' => 1,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->access_token = $token;
        $user->save();
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

    // 3.5. Đăng nhập
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        // Tìm user theo email
        $user = User::where('email', $request->email)
                    ->where('is_active', 1)
                    ->first();

        // Kiểm tra user tồn tại
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        // Kiểm tra mật khẩu đúng
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }
        
        // Xóa token cũ nếu có
        $user->tokens()->delete();
        
        // Tạo token mới
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // Lưu token vào user
        $user->access_token = $token;
        $user->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đăng nhập thành công',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'access_token' => $token
            ]
        ]);
    }

    // 4. Cập nhật thông tin cá nhân
    public function updateProfile(Request $request, $id)
    {
        $user = User::find($id);

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

    /**
     * API đăng xuất người dùng
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiLogout(Request $request)
    {
        // Lấy user hiện tại
        $user = Auth::user();
        
        // Nếu sử dụng token authentication (Sanctum/Passport)
        if ($request->bearerToken()) {
            // Chỉ xóa token hiện tại
            $request->user()->currentAccessToken()->delete();
            // Hoặc xóa tất cả token: $user->tokens()->delete();
        } else {
            // Nếu sử dụng session-based authentication
            Auth::logout();
            
            // Chỉ thao tác với session khi có session
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Đăng xuất thành công'
        ]);
    }

    /**
     * Gửi email đặt lại mật khẩu
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();
        
        // Tạo token reset password
        $token = Str::random(60);
        
        // Lưu token vào trường remember_token của user
        $user->remember_token = $token;
        $user->updated_at = now(); // Cập nhật thời gian để theo dõi thời hạn token
        $user->save();
        
        // Tạo URL đặt lại mật khẩu
        $resetUrl = config('app.frontend_url', 'http://localhost:3000') . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);
        
        // Gửi email với link reset password
        try {
            \Mail::send('emails.reset_password', ['resetUrl' => $resetUrl, 'user' => $user], function($message) use ($user) {
                $message->to($user->email);
                $message->subject('Đặt lại mật khẩu');
            });
            
            return response()->json([
                'status' => 'success',
                'message' => 'Đã gửi email hướng dẫn đặt lại mật khẩu'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể gửi email: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Đặt lại mật khẩu
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:5',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }
        
        // Tìm user theo email
        $user = User::where('email', $request->email)->first();
        
        // Kiểm tra token
        if ($user->remember_token !== $request->token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token không hợp lệ'
            ], 400);
        }
        
        // Kiểm tra thời gian token (hết hạn sau 60 phút)
        if (now()->diffInMinutes($user->updated_at) > 60) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token đã hết hạn'
            ], 400);
        }
        
        // Cập nhật mật khẩu
        $user->password = Hash::make($request->password);
        $user->remember_token = null; // Xóa token sau khi sử dụng
        $user->save();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Đặt lại mật khẩu thành công'
        ]);
    }
}
