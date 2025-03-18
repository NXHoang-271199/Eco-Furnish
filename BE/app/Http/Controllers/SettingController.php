<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SettingController extends Controller
{
    public function index()
    {
        return view('admins.settings.index');
    }
    
    public function smtp()
    {
        return view('admins.settings.smtp');
    }
    
    public function website()
    {
        return view('admins.settings.website');
    }
    
    public function updateSmtp(Request $request)
    {
        $validated = $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|numeric',
            'smtp_username' => 'required|string',
            'smtp_password' => 'required|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);
        
        // Cập nhật các biến môi trường
        $this->updateEnvironmentVariable('MAIL_HOST', $request->smtp_host);
        $this->updateEnvironmentVariable('MAIL_PORT', $request->smtp_port);
        $this->updateEnvironmentVariable('MAIL_USERNAME', $request->smtp_username);
        $this->updateEnvironmentVariable('MAIL_PASSWORD', $request->smtp_password);
        $this->updateEnvironmentVariable('MAIL_FROM_ADDRESS', $request->mail_from_address);
        $this->updateEnvironmentVariable('MAIL_FROM_NAME', '"' . $request->mail_from_name . '"');
        
        // Xử lý encryption
        $encryption = $request->has('smtp_encryption') ? 'tls' : null;
        $this->updateEnvironmentVariable('MAIL_ENCRYPTION', $encryption);
        
        // Xóa cache cấu hình
        Artisan::call('config:clear');
        
        return redirect()->back()->with('success', 'Cấu hình SMTP đã được cập nhật thành công');
    }
    
    public function updateWebsite(Request $request)
    {
        // Thêm logic để cập nhật thông tin website
        // Ví dụ: cập nhật tên website, logo, thông tin liên hệ, v.v.
        
        return redirect()->back()->with('success', 'Thông tin website đã được cập nhật thành công');
    }
    
    /**
     * Cập nhật biến môi trường trong file .env
     */
    private function updateEnvironmentVariable($key, $value)
    {
        $path = base_path('.env');
        
        if (file_exists($path)) {
            $content = file_get_contents($path);
            
            // Nếu giá trị null, thì thiết lập thành chuỗi rỗng
            if (is_null($value)) {
                $value = '';
            }
            
            // Nếu biến đã tồn tại, cập nhật nó
            if (preg_match("/^{$key}=/m", $content)) {
                $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
            } else {
                // Nếu không tồn tại, thêm mới
                $content .= "\n{$key}={$value}";
            }
            
            file_put_contents($path, $content);
        }
    }
}