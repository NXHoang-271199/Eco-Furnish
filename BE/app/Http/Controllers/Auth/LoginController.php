<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    protected function authenticated(Request $request, $user)
    {
        if ($user->role === 'admin' || $user->id === 1 || $user->role_id === 1) {
            $token = $user->createToken('admin-token')->plainTextToken;
            session(['admin_token' => $token]);
        }
        return response()->json(['status' => 'success']);
    }
}
