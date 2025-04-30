<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateOAuthUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update-oauth';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cập nhật thông tin is_oauth cho các tài khoản đăng nhập bằng Google/Facebook';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Bắt đầu cập nhật thông tin tài khoản OAuth...');

        // Tìm tất cả các tài khoản có access_token từ social provider
        $users = User::whereNotNull('access_token')->get();

        $count = 0;
        foreach ($users as $user) {
            // Kiểm tra xem user hiện tại có phải là tài khoản OAuth không dựa vào dấu hiệu về mật khẩu
            // Tài khoản OAuth thường có mật khẩu ngẫu nhiên được tạo khi đăng ký qua OAuth
            $isOAuth = false;
            
            // Kiểm tra có phải tài khoản từ Google/Facebook không
            if ($user->access_token) {
                $isOAuth = true;
                
                // Phát hiện provider dựa vào thông tin có sẵn
                $provider = null;
                if (strpos($user->avatar, 'avatar_google_') !== false) {
                    $provider = 'google';
                } elseif (strpos($user->avatar, 'avatar_fb_') !== false) {
                    $provider = 'facebook';
                }
                
                // Cập nhật thông tin
                $user->is_oauth = $isOAuth;
                $user->provider = $provider;
                $user->save();
                
                $count++;
                $this->info("Đã cập nhật tài khoản: {$user->email} - Provider: " . ($provider ?: 'unknown'));
            }
        }

        $this->info("Hoàn thành! Đã cập nhật {$count} tài khoản OAuth.");
        return Command::SUCCESS;
    }
} 