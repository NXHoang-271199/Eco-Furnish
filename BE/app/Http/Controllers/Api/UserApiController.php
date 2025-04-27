<?php

namespace App\Http\Controllers\Api;

use App\Models\Role;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Str;
use App\Traits\TokenHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;

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

    // 2. Lấy thông tin chi tiết một user (client)
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            Log::info('User avatar from DB: ' . $user->avatar);

            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'bio' => $user->bio,
                'joined_date' => $user->created_at->format('d/m/Y'),
                'avatar' => $user->avatar ? asset('storage/' . $user->avatar) : null,
            ];

            return response()->json([
                'data' => $userData
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi lấy thông tin người dùng: ' . $e->getMessage());
            return response()->json([
                'message' => 'Không tìm thấy người dùng',
                'error' => $e->getMessage()
            ], 404);
        }
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
            ], function ($message) use ($user) {
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
            'remember_me' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        // Tìm user theo email
        $user = User::where('email', $request->email)->first();

        // Kiểm tra trạng thái tài khoản
        if (!$user || $user->is_active == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tài khoản của bạn đã bị hủy kích hoạt.'
            ], 403);
        }

        // Kiểm tra email đã xác thực chưa
        if (!$user->email_verified_at) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vui lòng xác thực email trước khi đăng nhập',
                'verification_required' => true
            ], 403);
        }

        // Kiểm tra user và password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email hoặc mật khẩu không đúng'
            ], 401);
        }

        // Xử lý remember me
        $rememberMe = $request->remember_me ?? false;
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
            'phone' => 'nullable|string|max:15',
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

        if ($request->has('phone')) {
            $user->phone = $request->phone;
        }

        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thông tin thành công',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
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

        $user->tokens()->delete();
        // $user->refresh_token = null;
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
        $resetUrl = config('app.frontend_url', 'http://localhost:5173') . '/auth/reset-password?token=' . $token . '&email=' . urlencode($request->email);

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
            // 👉 Tạo ví nếu chưa có
            if (!$user->wallet) {
                $user->wallet()->create([
                    'balance' => 0,
                ]);
            }

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
            ], function ($message) use ($user) {
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

    /**
     * Upload avatar cho người dùng
     *
     * @param Request $request
     * @param int $id - ID của người dùng
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadAvatar(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            // Xác thực yêu cầu
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Xóa avatar cũ nếu có
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Log::info('Xóa avatar cũ: ' . $user->avatar);
                Storage::disk('public')->delete($user->avatar);
            }

            // Lưu avatar mới
            $path = $request->file('avatar')->store('uploads/avatar', 'public');
            Log::info('Đường dẫn avatar mới: ' . $path);

            // Cập nhật trường avatar của user
            $user->avatar = $path;
            $user->save();

            Log::info('Dữ liệu user sau khi lưu: ', $user->toArray());

            // Trả về thông tin avatar
            return response()->json([
                'message' => 'Avatar đã được cập nhật thành công',
                'avatar_url' => asset('storage/' . $path),
                'avatar_path' => $path,
                'data' => [
                    'avatar' => asset('storage/' . $path)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi upload avatar: ' . $e->getMessage());
            return response()->json([
                'error' => 'Không thể tải lên avatar: ' . $e->getMessage()
            ], 500);
        }
    }

    // Thêm các phương thức OAuth

    public function redirectToGoogle()
    {
        // Đảm bảo session được bắt đầu trước khi redirect
        // Session::start(); // Không cần start thủ công khi đã có middleware 'web'
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            // Sử dụng stateless() để không dựa vào session sau khi callback
            $socialUser = Socialite::driver('google')->stateless()->user();

            // Kiểm tra xem email này đã tồn tại trong DB chưa
            $user = User::where('email', $socialUser->getEmail())->first();

            // Nếu chưa có, tạo user mới
            if (!$user) {
                $clientRole = Role::where('slug', 'client')->first();

                // Xử lý avatar từ Google - Tải về và lưu vào storage
                $avatarPath = null;
                if ($socialUser->getAvatar()) {
                    try {
                        $avatarUrl = $socialUser->getAvatar();
                        $avatarContent = file_get_contents($avatarUrl);
                        if ($avatarContent) {
                            $avatarName = 'avatar_google_' . time() . '.jpg';
                            $avatarPath = 'uploads/avatars/' . $avatarName;
                            Storage::disk('public')->put($avatarPath, $avatarContent);

                            // Log để debug
                            Log::info('Google avatar saved successfully', [
                                'path' => $avatarPath,
                                'url' => $avatarUrl
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error saving Google avatar: ' . $e->getMessage());
                    }
                }

                $accessToken = $socialUser->token;
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(1234567), // Tạo password ngẫu nhiên
                    'role_id' => $clientRole->id,
                    'avatar' => $avatarPath,
                    'is_active' => 1, // Đã active sẵn
                    'email_verified_at' => now(), // Đã xác thực email
                    'access_token' => $accessToken, // Lưu access_token để đánh dấu tài khoản OAuth
                    'is_oauth' => true, // Đánh dấu là tài khoản OAuth
                    'provider' => 'google',
                    'provider_id' => $socialUser->getId()
                ]);
                
                // Tạo ví cho người dùng mới đăng ký qua Google
                if (!$user->wallet) {
                    $user->wallet()->create([
                        'balance' => 0,
                    ]);
                    Log::info('Wallet created for new Google user', ['user_id' => $user->id]);
                }
            } else {
                // Cập nhật avatar nếu user đã tồn tại
                if ($socialUser->getAvatar()) {
                    try {
                        $avatarUrl = $socialUser->getAvatar();
                        $avatarContent = file_get_contents($avatarUrl);
                        if ($avatarContent) {
                            // Xóa avatar cũ nếu có
                            if ($user->avatar) {
                                Storage::disk('public')->delete($user->avatar);
                            }

                            $avatarName = 'avatar_google_' . time() . '.jpg';
                            $avatarPath = 'uploads/avatars/' . $avatarName;
                            Storage::disk('public')->put($avatarPath, $avatarContent);

                            // Cập nhật avatar và access_token cho user
                            $accessToken = $socialUser->token;
                            $user->avatar = $avatarPath;
                            $user->access_token = $accessToken; // Cập nhật access_token nếu chưa có
                            $user->is_oauth = true; // Đánh dấu là tài khoản OAuth
                            $user->provider = 'google';
                            $user->provider_id = $socialUser->getId();
                            $user->save();

                            // Log để debug
                            Log::info('Google avatar updated successfully', [
                                'user_id' => $user->id,
                                'path' => $avatarPath,
                                'url' => $avatarUrl
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error updating Google avatar: ' . $e->getMessage());
                    }
                }
                
                // Kiểm tra và tạo ví nếu chưa có
                if (!$user->wallet) {
                    $user->wallet()->create([
                        'balance' => 0,
                    ]);
                    Log::info('Wallet created for existing Google user', ['user_id' => $user->id]);
                }
            }

            // Tạo token
            $token = $user->createToken('auth_token')->plainTextToken;
            $refreshToken = Str::random(60);

            // Lấy access token từ social user
            $accessToken = $socialUser->token;

            // Lưu refresh token
            $user->update([
                'refresh_token' => $refreshToken
            ]);

            // Đảm bảo URL avatar đầy đủ
            $avatarUrl = null;
            if ($user->avatar) {
                // Kiểm tra nếu avatar là URL đầy đủ (bắt đầu bằng http)
                if (strpos($user->avatar, 'http') === 0) {
                    $avatarUrl = $user->avatar;
                    Log::info('Using direct avatar URL', ['url' => $avatarUrl]);
                } else {
                    $avatarUrl = asset('storage/' . $user->avatar);
                    Log::info('Using storage avatar URL', ['url' => $avatarUrl]);
                }
                // Log URL avatar cuối cùng
                Log::info('Final avatar URL', ['url' => $avatarUrl]);
            }

            // Chuyển hướng về FE với token
            $redirectUrl = 'http://localhost:5173/oauth-callback?' . http_build_query([
                'token' => $token,
                'refresh_token' => $refreshToken,
                'user' => json_encode([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $avatarUrl // Sử dụng avatarUrl đã xử lý ở trên
                ])
            ]);

            return redirect($redirectUrl);
        } catch (\Exception $e) {
            Log::error('Google login error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            // Redirect về trang sign-in với tham số lỗi cụ thể
            return redirect('http://localhost:5173/sign-in?error=google_callback_failed');
        }
    }

    public function redirectToFacebook()
    {
        // Đảm bảo session được bắt đầu trước khi redirect
        // Session::start(); // Không cần start thủ công khi đã có middleware 'web'
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            // Sử dụng stateless() để không dựa vào session sau khi callback
            $socialUser = Socialite::driver('facebook')->stateless()->user();

            // Kiểm tra xem email này đã tồn tại trong DB chưa
            $user = User::where('email', $socialUser->getEmail())->first();

            // Tạo access token và refresh token
            $accessToken = $socialUser->token;
            $refreshToken = Str::random(60);

            // Xử lý avatar từ Facebook - Thêm kích thước ảnh lớn
            $avatarPath = null;
            $originalAvatarUrl = $socialUser->getAvatar(); // Lưu URL gốc từ Facebook

            // Đảm bảo URL Facebook có tham số kích thước
            if (!strpos($originalAvatarUrl, '?')) {
                $originalAvatarUrl .= '?width=500&height=500';
            } else if (!strpos($originalAvatarUrl, 'width=')) {
                $originalAvatarUrl .= '&width=500&height=500';
            }

            // Log URL đã được cải thiện
            Log::info('Enhanced Facebook avatar URL', [
                'enhanced_url' => $originalAvatarUrl
            ]);

            if ($socialUser->getAvatar()) {
                try {
                    // Sử dụng URL avatar Facebook có kích thước lớn hơn
                    $avatarUrl = $socialUser->getAvatar();

                    // Đảm bảo URL có dấu ? trước khi thêm tham số
                    if (strpos($avatarUrl, '?') === false) {
                        $avatarUrl .= '?width=500&height=500&access_token=' . $accessToken;
                    } else {
                        $avatarUrl .= '&width=500&height=500&access_token=' . $accessToken;
                    }

                    // Log URL avatar để debug
                    Log::info('Facebook avatar URL', [
                        'url' => $avatarUrl
                    ]);

                    $avatarContent = file_get_contents($avatarUrl);
                    if ($avatarContent) {
                        $avatarName = 'avatar_fb_' . time() . '.jpg';
                        $avatarPath = 'uploads/avatars/' . $avatarName;
                        Storage::disk('public')->put($avatarPath, $avatarContent);

                        // Log để debug
                        Log::info('Facebook avatar saved successfully', [
                            'path' => $avatarPath,
                            'url' => $avatarUrl
                        ]);
                    } else {
                        // Nếu không lấy được nội dung từ URL, thử lại với URL không có tham số
                        $simpleUrl = $socialUser->getAvatar();
                        Log::info('Trying simple Facebook avatar URL', [
                            'simple_url' => $simpleUrl
                        ]);

                        $simpleContent = file_get_contents($simpleUrl);
                        if ($simpleContent) {
                            $avatarName = 'avatar_fb_simple_' . time() . '.jpg';
                            $avatarPath = 'uploads/avatars/' . $avatarName;
                            Storage::disk('public')->put($avatarPath, $simpleContent);

                            Log::info('Facebook simple avatar saved successfully', [
                                'path' => $avatarPath,
                                'url' => $simpleUrl
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('Error saving Facebook avatar: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
                    // Sử dụng URL đã cải thiện làm dự phòng
                    $avatarPath = $originalAvatarUrl;
                    Log::info('Using enhanced Facebook avatar URL as fallback', [
                        'enhanced_url' => $originalAvatarUrl
                    ]);
                }
            }

            // Nếu chưa có, tạo user mới
            if (!$user) {
                $clientRole = Role::where('slug', 'client')->first();

                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(1234567),
                    'role_id' => $clientRole->id,
                    'avatar' => $avatarPath,
                    'is_active' => 1,
                    'email_verified_at' => now(),
                    'access_token' => $accessToken, // Lưu access_token để đánh dấu tài khoản OAuth
                    'is_oauth' => true, // Đánh dấu là tài khoản OAuth
                    'provider' => 'facebook',
                    'provider_id' => $socialUser->getId()
                ]);

                // Log thông tin user mới
                Log::info('New user created from Facebook', [
                    'user_id' => $user->id,
                    'avatar_path' => $avatarPath
                ]);
                
                // Tạo ví cho người dùng mới đăng ký qua Facebook
                if (!$user->wallet) {
                    $user->wallet()->create([
                        'balance' => 0,
                    ]);
                    Log::info('Wallet created for new Facebook user', ['user_id' => $user->id]);
                }
            } else {
                // Cập nhật thông tin cho user đã tồn tại
                $updateData = [
                    'access_token' => $accessToken,
                    'refresh_token' => $refreshToken
                ];

                // Chỉ cập nhật avatar nếu lấy được avatar mới từ Facebook
                if ($avatarPath) {
                    $updateData['avatar'] = $avatarPath;

                    // Xóa avatar cũ nếu có
                    if ($user->avatar) {
                        Storage::disk('public')->delete($user->avatar);
                    }
                }

                $user->update($updateData);

                // Log cập nhật user
                Log::info('Existing user updated from Facebook', [
                    'user_id' => $user->id,
                    'avatar_path' => $avatarPath
                ]);
                
                // Kiểm tra và tạo ví nếu chưa có
                if (!$user->wallet) {
                    $user->wallet()->create([
                        'balance' => 0,
                    ]);
                    Log::info('Wallet created for existing Facebook user', ['user_id' => $user->id]);
                }
            }

            // Tạo token cho authentication
            $token = $user->createToken('auth_token')->plainTextToken;

            // Đảm bảo URL avatar đầy đủ
            $avatarUrl = null;
            if ($user->avatar) {
                // Kiểm tra nếu avatar là URL đầy đủ (bắt đầu bằng http)
                if (strpos($user->avatar, 'http') === 0) {
                    $avatarUrl = $user->avatar;
                    Log::info('Using direct avatar URL', ['url' => $avatarUrl]);
                } else {
                    $avatarUrl = asset('storage/' . $user->avatar);
                    Log::info('Using storage avatar URL', ['url' => $avatarUrl]);
                }
                // Log URL avatar cuối cùng
                Log::info('Final avatar URL', ['url' => $avatarUrl]);
            }

            // Chuyển hướng về FE với token
            $redirectUrl = 'http://localhost:5173/oauth-callback?' . http_build_query([
                'token' => $token,
                'refresh_token' => $refreshToken,
                'user' => json_encode([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $avatarUrl // Sử dụng avatarUrl đã xử lý ở trên
                ])
            ]);

            return redirect($redirectUrl);
        } catch (\Exception $e) {
            Log::error('Facebook login error: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return redirect('http://localhost:5173/sign-in?error=facebook_callback_failed');
        }
    }

    /**
     * Kiểm tra người dùng có đăng nhập qua OAuth không
     * 
     * @param User $user
     * @return bool
     */
    private function isOAuthUser(User $user)
    {
        // Kiểm tra xem tài khoản được tạo bằng OAuth không
        // Các tài khoản OAuth thường sẽ có access_token từ social provider
        // và được đánh dấu trong cột is_oauth (nếu có) hoặc dựa vào provider_id

        // Cách 1: Kiểm tra trường is_oauth (nếu có)
        if (Schema::hasColumn('users', 'is_oauth')) {
            return (bool)$user->is_oauth;
        }

        // Cách 2: Kiểm tra provider_id hoặc provider_name (nếu có)
        if (Schema::hasColumn('users', 'provider') || Schema::hasColumn('users', 'provider_id')) {
            return !empty($user->provider) || !empty($user->provider_id);
        }

        // Cách 3: Nếu không có các trường trên, dựa vào access_token từ social provider
        // Lưu ý: Cách này có thể không chính xác nếu access_token được dùng cho mục đích khác
        return $user->access_token !== null && $user->password === Hash::make('Oauthlogin');
    }

    /**
     * Thiết lập mật khẩu cấp 2 cho người dùng
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setLevel2Password(Request $request)
    {
        $user = $request->user();
        $isOAuth = $this->isOAuthUser($user);

        // Validate dựa trên loại tài khoản
        if ($isOAuth) {
            $validator = Validator::make($request->all(), [
                'level2_password' => 'required|string|min:5',
                'confirm_level2_password' => 'required|string|same:level2_password'
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'level2_password' => 'required|string|min:5',
                'confirm_level2_password' => 'required|string|same:level2_password'
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        // Kiểm tra xem người dùng đã có mật khẩu cấp 2 chưa
        if ($user->has_level2_password) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn đã thiết lập mật khẩu cấp 2 trước đó. Vui lòng sử dụng chức năng cập nhật mật khẩu cấp 2.'
            ], 400);
        }

        // Nếu không phải tài khoản OAuth, kiểm tra mật khẩu hiện tại
        if (!$isOAuth) {
            // Kiểm tra mật khẩu hiện tại
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mật khẩu hiện tại không đúng'
                ], 400);
            }

            // Kiểm tra mật khẩu cấp 2 không được giống mật khẩu cấp 1
            if ($request->current_password === $request->level2_password) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mật khẩu cấp 2 không được giống mật khẩu cấp 1'
                ], 400);
            }
        }

        // Thiết lập mật khẩu cấp 2
        $user->level2_password = Hash::make($request->level2_password);
        $user->has_level2_password = true;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Thiết lập mật khẩu cấp 2 thành công',
            'data' => [
                'has_level2_password' => true
            ]
        ]);
    }

    /**
     * Xác thực mật khẩu cấp 2
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyLevel2Password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'level2_password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Kiểm tra xem người dùng đã thiết lập mật khẩu cấp 2 chưa
        if (!$user->has_level2_password) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn chưa thiết lập mật khẩu cấp 2'
            ], 400);
        }

        // Xác thực mật khẩu cấp 2
        if (!Hash::check($request->level2_password, $user->level2_password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mật khẩu cấp 2 không đúng'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Xác thực mật khẩu cấp 2 thành công'
        ]);
    }

    /**
     * Cập nhật mật khẩu cấp 2
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateLevel2Password(Request $request)
    {
        $user = $request->user();
        $isOAuth = $this->isOAuthUser($user);

        // Validate dựa trên loại tài khoản
        if ($isOAuth) {
            $validator = Validator::make($request->all(), [
                'current_level2_password' => 'required|string',
                'new_level2_password' => 'required|string|min:5',
                'confirm_level2_password' => 'required|string|same:new_level2_password'
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'current_level2_password' => 'required|string',
                'new_level2_password' => 'required|string|min:5',
                'confirm_level2_password' => 'required|string|same:new_level2_password'
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        // Kiểm tra xem người dùng đã thiết lập mật khẩu cấp 2 chưa
        if (!$user->has_level2_password) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn chưa thiết lập mật khẩu cấp 2'
            ], 400);
        }

        // Kiểm tra mật khẩu cấp 2 hiện tại
        if (!Hash::check($request->current_level2_password, $user->level2_password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mật khẩu cấp 2 hiện tại không đúng'
            ], 400);
        }

        // Nếu không phải tài khoản OAuth, kiểm tra mật khẩu cấp 2 mới không được giống mật khẩu cấp 1
        if (!$isOAuth && Hash::check($request->new_level2_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mật khẩu cấp 2 mới không được giống mật khẩu cấp 1'
            ], 400);
        }

        // Cập nhật mật khẩu cấp 2
        $user->level2_password = Hash::make($request->new_level2_password);
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật mật khẩu cấp 2 thành công'
        ]);
    }

    /**
     * Kiểm tra trạng thái mật khẩu cấp 2 và phương thức đăng nhập
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkLevel2PasswordStatus(Request $request)
    {
        $user = $request->user();
        $isOAuth = $this->isOAuthUser($user);

        return response()->json([
            'status' => 'success',
            'data' => [
                'has_level2_password' => (bool) $user->has_level2_password,
                'is_oauth_user' => $isOAuth
            ]
        ]);
    }

    /**
     * Quên mật khẩu cấp 2
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotLevel2Password(Request $request)
    {
        $user = $request->user();
        $isOAuth = $this->isOAuthUser($user);

        // Kiểm tra xem người dùng đã thiết lập mật khẩu cấp 2 chưa
        if (!$user->has_level2_password) {
            return response()->json([
                'status' => 'error',
                'message' => 'Bạn chưa thiết lập mật khẩu cấp 2'
            ], 400);
        }

        // Xử lý khác nhau dựa trên loại tài khoản
        if ($isOAuth || $request->has('is_oauth')) {
            // Xử lý cho tài khoản OAuth - không cần xác thực mật khẩu cấp 1
            Log::info('Xử lý quên mật khẩu cấp 2 cho tài khoản OAuth');
        } else {
            // Validate cho tài khoản thông thường
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422);
            }

            // Kiểm tra mật khẩu cấp 1 cho tài khoản thông thường
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mật khẩu cấp 1 không đúng'
                ], 400);
            }
        }

        // Tạo token đặt lại mật khẩu cấp 2
        $token = Str::random(60);
        $user->level2_password_reset_token = $token;
        $user->level2_password_reset_at = now();
        $user->save();

        // Tạo URL đặt lại mật khẩu cấp 2
        $resetUrl = config('app.frontend_url', 'http://localhost:5173') . '/account?tab=level2password&action=reset&token=' . $token;

        // Gửi email với link đặt lại mật khẩu cấp 2
        try {
            Mail::send('emails.reset_level2_password', ['resetUrl' => $resetUrl, 'user' => $user], function ($message) use ($user) {
                $message->to($user->email);
                $message->subject('Đặt lại mật khẩu cấp 2');
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Đã gửi email hướng dẫn đặt lại mật khẩu cấp 2'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi gửi email đặt lại mật khẩu cấp 2: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể gửi email: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đặt lại mật khẩu cấp 2
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetLevel2Password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required|string',
            'new_level2_password' => 'required|string|min:5',
            'confirm_level2_password' => 'required|string|same:new_level2_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Kiểm tra token
        if ($user->level2_password_reset_token !== $request->token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token không hợp lệ'
            ], 400);
        }

        // Kiểm tra thời gian token (hết hạn sau 60 phút)
        if (!$user->level2_password_reset_at || now()->diffInMinutes($user->level2_password_reset_at) > 60) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token đã hết hạn'
            ], 400);
        }

        // Kiểm tra mật khẩu cấp 2 mới không được giống mật khẩu cấp 1
        if (Hash::check($request->new_level2_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mật khẩu cấp 2 mới không được giống mật khẩu cấp 1'
            ], 400);
        }

        // Cập nhật mật khẩu cấp 2
        $user->level2_password = Hash::make($request->new_level2_password);
        $user->level2_password_reset_token = null;
        $user->level2_password_reset_at = null;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Đặt lại mật khẩu cấp 2 thành công'
        ]);
    }
}
