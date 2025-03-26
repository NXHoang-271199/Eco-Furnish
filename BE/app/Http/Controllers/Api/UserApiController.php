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
use Illuminate\Support\Facades\Mail;

class UserApiController extends Controller
{
    use TokenHandler;

    // 1. Lấy danh sách user (chỉ client)
    public function index()
    {
        $users = User::whereHas('role', function ($query) {
            $query->where('slug', 'client');
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

    // 2. Xem chi tiết user theo email
    public function show($id)
    {
        $user = User::whereHas('role', function ($query) {
            $query->where('slug', 'client');
        })
            ->where('id', $id)
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

        $clientRole = Role::where('slug', 'client')->first();

        $avatarPath = null;
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('uploads/avatars', 'public');
        }

        // Tạo verification token
        $verificationToken = Str::random(60);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $clientRole->id,
            'avatar' => $avatarPath,
            'is_active' => 0,  // Chưa active cho đến khi xác thực email
            'email_verification_token' => $verificationToken,
            'email_verified_at' => null
        ]);

        // Gửi email xác thực
        try {
            $frontendUrl = 'http://localhost:5173'; 
            $verificationUrl = $frontendUrl . '/auth/verify-email?' . http_build_query([
                'token' => $verificationToken,
                'email' => urlencode($user->email)
            ]);

            // Debug URL
            \Log::info('Verification URL: ' . $verificationUrl);

            Mail::send('emails.verify_email', [
                'user' => $user,
                'verificationUrl' => $verificationUrl
            ], function($message) use ($user) {
                $message->to($user->email);
                $message->subject('Xác thực tài khoản');
            });
        } catch (\Exception $e) {
            \Log::error('Email error: ' . $e->getMessage());
        }

        // Không trả về access_token ngay, phải xác thực email trước
        return response()->json([
            'status' => 'success',
            'message' => 'Đăng ký thành công. Vui lòng kiểm tra email để xác thực tài khoản.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ], 201);
    }

    // 3.5. Đăng nhập
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'nullable|in:true,false,0,1'
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

        // Kiểm tra email đã xác thực chưa
        if (!$user->email_verified_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng xác thực email trước khi đăng nhập',
                'verification_required' => true
            ], 403);
        }

        // Kiểm tra user và password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        // Chuyển đổi giá trị remember_me thành boolean
        $rememberMe = filter_var($request->remember_me, FILTER_VALIDATE_BOOLEAN);

        // Xử lý remember me
        if ($rememberMe) {
            $user->remember_me = true;
            $user->remember_me_expires_at = now()->addDays(30); // Lưu 30 ngày
        } else {
            $user->remember_me = false;
            $user->remember_me_expires_at = null;
        }
        $user->save();

        // Tạo token với thời hạn tương ứng
        $tokens = $this->generateTokens($user, $rememberMe);

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
                'remember_me' => $user->remember_me,
                'remember_me_expires_at' => $user->remember_me ? $user->remember_me_expires_at : null
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

        // Kiểm tra xem remember_me có còn hiệu lực không
        if ($user->remember_me && $user->remember_me_expires_at && now()->gt($user->remember_me_expires_at)) {
            $user->remember_me = false;
            $user->remember_me_expires_at = null;
            $user->save();
        }

        // Tạo token mới với thời hạn tương ứng
        $tokens = $this->generateTokens($user, $user->remember_me);

        return response()->json([
            'status' => 'success',
            'message' => 'Refresh token thành công',
            'data' => [
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'access_token_expires_at' => $tokens['access_token_expires_at'],
                'refresh_token_expires_at' => $tokens['refresh_token_expires_at'],
                'remember_me' => $user->remember_me,
                'remember_me_expires_at' => $user->remember_me ? $user->remember_me_expires_at : null
            ]
        ]);
    }

    // 4. Cập nhật thông tin cá nhân
    public function updateProfile(Request $request, $id)
    {
        $user = User::whereHas('role', function ($query) {
            $query->where('slug', 'client');
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
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Người dùng chưa đăng nhập'
            ], 401);
        }
        
        // Xóa token và remember_me
        $request->user()->currentAccessToken()->delete();
        $user->remember_me = false;
        $user->remember_me_expires_at = null;
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
        $resetUrl = config('app.frontend_url', 'http://localhost:5173') . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);

        // Gửi email với link reset password
        try {
            Mail::send('emails.reset_password', ['resetUrl' => $resetUrl, 'user' => $user], function ($message) use ($user) {
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
            'remember_token' => 'required|string',
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

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'verify_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)
                    ->where('email_verification_token', $request->verify_token)
                    ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token xác thực không hợp lệ hoặc đã hết hạn'
            ], 400);
        }

        // Thêm kiểm tra thời gian hết hạn (24 giờ)
        if (now()->diffInHours($user->created_at) > 24) {
            return response()->json([
                'status' => 'error',
                'message' => 'Link xác thực đã hết hạn. Vui lòng yêu cầu gửi lại email xác thực.',
                'expired' => true
            ], 400);
        }

        try {
            $user->email_verified_at = now();
            $user->email_verification_token = null;
            $user->is_active = 1;
            $user->save();

            // Tạo token sau khi xác thực thành công
            $tokens = $this->generateTokens($user);

            return response()->json([
                'status' => 'success',
                'message' => 'Xác thực email thành công',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token']
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Email verification error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi xác thực email'
            ], 500);
        }
    }

    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email này đã được xác thực'
            ], 400);
        }

        // Tạo token mới
        $verificationToken = Str::random(60);
        $user->email_verification_token = $verificationToken;
        $user->save();

        try {
            $frontendUrl = 'http://localhost:5173'; // Hardcode tạm thời để test
            $verificationUrl = $frontendUrl . '/auth/verify-email?' . http_build_query([
                'token' => $verificationToken,
                'email' => urlencode($user->email)
            ]);

            // Debug URL
            \Log::info('Verification URL: ' . $verificationUrl);

            Mail::send('emails.verify_email', [
                'user' => $user,
                'verificationUrl' => $verificationUrl
            ], function($message) use ($user) {
                $message->to($user->email);
                $message->subject('Xác thực tài khoản');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Đã gửi lại email xác thực'    
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể gửi email: ' . $e->getMessage()
            ], 500);
        }
    }
}
