<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Kiểm tra đăng nhập
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        $user = Auth::user();
        
        // Kiểm tra tài khoản có được kích hoạt không
        if ($user->is_active != 1) { 
            Auth::logout(); 
            return back()->withErrors([ 
                'email' => 'Tài khoản của bạn chưa được kích hoạt.', 
            ]); 
        }
        // Kiểm tra role admin/staff
        if (!in_array($user->role->slug, ['admin', 'staff'])) {
            abort(403, 'Bạn không có quyền truy cập vào trang này.');
        }

        return $next($request);
    }
}