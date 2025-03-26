<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait TokenHandler
{
    protected function generateTokens($user, $remember = false)
    {
        // Xóa tokens cũ
        $user->tokens()->delete();
        
        if ($remember) {
            // Thời gian token dài hơn nếu remember_me = true
            $accessTokenExpiry = now()->addDays(7);    // 7 ngày
            $refreshTokenExpiry = now()->addDays(30);  // 30 ngày
        } else {
            // Thời gian token mặc định
            $accessTokenExpiry = now()->addSeconds(20); // 30 phút
            $refreshTokenExpiry = now()->addDays(7);    // 7 ngày
        }
        
        // Tạo access token
        $accessToken = $user->createToken('access_token', ['*'], $accessTokenExpiry)->plainTextToken;
        
        // Tạo refresh token
        $refreshToken = $user->createToken('refresh_token', ['*'], $refreshTokenExpiry)->plainTextToken;
        
        // Lưu tokens
        $user->access_token = $accessToken;
        $user->refresh_token = $refreshToken;
        $user->save();

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'access_token_expires_at' => $accessTokenExpiry,
            'refresh_token_expires_at' => $refreshTokenExpiry
        ];
    }

    protected function validateRefreshToken($refreshToken)
    {
        $tokenId = explode('|', $refreshToken)[1] ?? null;
        if (!$tokenId) {
            return null;
        }

        $token = \Laravel\Sanctum\PersonalAccessToken::findToken($tokenId);
        if (!$token || $token->name !== 'refresh_token' || $token->expires_at < now()) {
            return null;
        }

        // Kiểm tra thêm remember_me_expires_at
        $user = $token->tokenable;
        if ($user->remember_me && $user->remember_me_expires_at && now()->gt($user->remember_me_expires_at)) {
            // Reset remember_me nếu đã hết hạn
            $user->remember_me = false;
            $user->remember_me_expires_at = null;
            $user->save();
        }

        return $user;
    }
} 