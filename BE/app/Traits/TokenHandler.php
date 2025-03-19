<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait TokenHandler
{
    protected function generateTokens($user)
    {
        // Xóa tokens cũ
        $user->tokens()->delete();
        
        // Tạo access token (30 phút)
        $accessToken = $user->createToken('access_token', ['*'], now()->addMinutes(30))->plainTextToken;
        
        // Tạo refresh token (7 ngày)
        $refreshToken = $user->createToken('refresh_token', ['*'], now()->addDays(7))->plainTextToken;
        
        // Lưu tokens
        $user->access_token = $accessToken;
        $user->refresh_token = $refreshToken;
        $user->save();

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'access_token_expires_at' => now()->addMinutes(30),
            'refresh_token_expires_at' => now()->addDays(7)
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

        return $token->tokenable;
    }
} 