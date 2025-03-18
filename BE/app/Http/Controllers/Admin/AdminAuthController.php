<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        // Nếu đã đăng nhập và là admin/staff thì chuyển đến dashboard
        if (Auth::check() && in_array(Auth::user()->role->slug, ['admin', 'staff'])) {
            return redirect()->route('admin.dashboard');
        }
        return view('admins.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Kiểm tra role
            if (!in_array($user->role->slug, ['admin', 'staff'])) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Bạn không có quyền truy cập vào trang quản trị.',
                ]);
            }

            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}