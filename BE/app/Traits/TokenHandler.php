<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait TokenHandler
{
    protected function generateTokens($user, $remember = false)
    {
        // Xóa tokens cũ
        $user->tokens()->delete();

        // Tạo access token mới và lấy thời gian hết hạn
        $accessTokenExpiresAt = now()->addHours(24);
        $accessTokenInstance = $user->createToken('access_token', ['*'], $accessTokenExpiresAt);
        $accessToken = $accessTokenInstance->plainTextToken;

        // Tạo refresh token mới và lấy thời gian hết hạn
        $refreshTokenExpiresAt = now()->addDays(7);
        $refreshTokenInstance = $user->createToken('refresh_token', ['*'], $refreshTokenExpiresAt);
        $refreshToken = $refreshTokenInstance->plainTextToken;

        // Lưu tokens (không cần thiết vì Sanctum đã lưu trong DB)
        // $user->access_token = $accessToken;
        // $user->refresh_token = $refreshToken;
        // $user->save(); // Không cần save lại ở đây

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
