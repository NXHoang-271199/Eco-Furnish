<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $avatar = $user->avatar;
        if (!$avatar || !file_exists(public_path('storage/' . $avatar))) {
            $avatar = 'https://www.gravatar.com/avatar/' . md5(strtolower($user->email)) . '?s=200&d=mp';
        } else {
            $avatar = asset('storage/' . $avatar);
        }

        return view('admins.dashboard', [
            'user' => $user,
            'avatar' => $avatar
        ]);
    }
} 
