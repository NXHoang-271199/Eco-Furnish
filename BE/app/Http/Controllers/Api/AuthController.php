<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function verifyToken(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            // Load roles của user
            $user->load('roles');

            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            \Log::error('Token verification error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xác thực: ' . $e->getMessage()
            ], 401);
        }
    }
}
