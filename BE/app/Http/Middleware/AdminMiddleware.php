<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Kiểm tra xem người dùng đã đăng nhập chưa
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        // Kiểm tra xem người dùng có vai trò admin hoặc staff không
        if (!in_array(Auth::user()->role->slug, ['admin', 'staff'])) {
            return redirect()->route('admin.login')->with('error', 'Bạn không có quyền truy cập trang này.');
        }

        return $next($request);
    }
}
