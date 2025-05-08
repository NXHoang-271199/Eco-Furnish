<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckActiveStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Kiểm tra nếu người dùng đã đăng nhập
        if (Auth::check()) {
            $user = Auth::user();
            
            // Kiểm tra trạng thái tài khoản
            if (!$user->is_active) {
                // Đăng xuất người dùng
                Auth::logout();
                
                // Xóa token hiện tại nếu đang sử dụng Sanctum
                if ($user->currentAccessToken()) {
                    $user->currentAccessToken()->delete();
                }
                
                // Trả về response tương ứng
                if ($request->expectsJson()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Tài khoản của bạn đã bị vô hiệu hóa'
                    ], 403);
                }
                
                return redirect()->route('login')->withErrors([
                    'email' => 'Tài khoản của bạn đã bị vô hiệu hóa.'
                ]);
            }
        }

        return $next($request);
    }
} 