<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Nếu chưa đăng nhập hoặc không phải admin/staff
        if (!Auth::check() || !in_array(Auth::user()->role->slug, ['admin', 'staff'])) {
            Auth::logout(); // Đảm bảo logout nếu user không có quyền
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Bạn không có quyền truy cập vào trang quản trị.']);
        }
        return $next($request);
    }
}