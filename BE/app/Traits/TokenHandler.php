<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait TokenHandler
{
    protected function generateTokens($user, $remember = false)
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