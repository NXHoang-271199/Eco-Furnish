<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Support\Str;

class AdminForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('admins.auth.forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Tìm user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Không tìm thấy địa chỉ email này.']);
        }

        // Tạo token
        $token = Str::random(60);

        // Lưu token vào database
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            [
                'token' => bcrypt($token),
                'created_at' => now()
            ]
        );

        try {
            // Gửi email
            Mail::to($user->email)->send(new ResetPasswordMail($user, $token));

            return back()->with('status', 'Chúng tôi đã gửi email khôi phục mật khẩu của bạn!');
        } catch (\Exception $e) {
            \Log::error('Reset password email error: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Không thể gửi email reset password.']);
        }
    }

    protected function broker()
    {
        return Password::broker('users');
    }
}