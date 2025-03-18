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
use App\Traits\TokenHandler;

class UserApiController extends Controller
{
    use TokenHandler;

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
    public function show($email)
    {
        $user = User::whereHas('role', function($query) {
            $query->where('name', 'Client');
        })
        ->where('email', 'LIKE', $email)
        ->where('is_active', 1)
        ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người dùng'
            ], 404);
        }
        $tokens = $this->generateTokens($user);
        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'slug' => Str::slug($user->name),
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'joined_date' => $user->created_at->format('d/m/Y'),
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'access_token_expires_at' => $tokens['access_token_expires_at'],
                'refresh_token_expires_at' => $tokens['refresh_token_expires_at']
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
            'remember_me' => 'nullable|boolean'  // Sửa thành nullable|boolean
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

        // Kiểm tra user và password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        // Tạo token bình thường
        $tokens = $this->generateTokens($user);

        // Nếu user check "Nhớ tài khoản"
        if ($request->remember_me) {
            // Tạo remember_token để lưu thông tin đăng nhập
            $remember_token = encrypt([
                'email' => $request->email,
                'password' => $request->password // Mật khẩu gốc để login lại
            ]);
            
            $user->remember_token = $remember_token;
            $user->save();
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Đăng nhập thành công',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->name,
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'remember_me' => $request->remember_me ? true : false,
                'remember_token' => $request->remember_me ? $remember_token : null
            ]
        ]);
    }

    // Thêm method mới để refresh token
    public function refreshToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $user = $this->validateRefreshToken($request->refresh_token);
        
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Refresh token không hợp lệ hoặc đã hết hạn'
            ], 401);
        }

        $tokens = $this->generateTokens($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Refresh token thành công',
            'data' => [
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'access_token_expires_at' => $tokens['access_token_expires_at'],
                'refresh_token_expires_at' => $tokens['refresh_token_expires_at']
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
        $user = Auth::user();
        
        // Xóa tokens
        $user->tokens()->delete();
        
        // Xóa remember_token nếu có
        $user->remember_token = null;
        $user->save();
        
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

    public function autoLogin(Request $request)
    {
        try {
            // Giải mã remember_token
            $credentials = decrypt($request->remember_token);
            
            // Tự động đăng nhập với thông tin đã lưu
            return $this->login(new Request([
                'email' => $credentials['email'],
                'password' => $credentials['password'],
                'remember_me' => true
            ]));
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token không hợp lệ'
            ], 401);
        }
    }
}
