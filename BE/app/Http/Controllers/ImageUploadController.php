<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('upload')) {
            try {
                $file = $request->file('upload');
                
                // Validate file
                if (!$file->isValid() || !in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    throw new \Exception('File không hợp lệ');
                }

                // Tạo thư mục theo ngày nếu chưa tồn tại
                $folder = 'uploads/posts/' . date('Y/m/d');
                $path = public_path($folder);
                if (!file_exists($path)) {
                    mkdir($path, 0755, true);
                }

                // Tạo tên file ngẫu nhiên
                $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                
                // Di chuyển file
                $file->move($path, $fileName);
                
                // Tạo URL
                $url = asset($folder . '/' . $fileName);

                return response()->json([
                    'uploaded' => true,
                    'url' => $url
                ]);

            } catch (\Exception $e) {
                return response()->json([
                    'uploaded' => false,
                    'error' => [
                        'message' => $e->getMessage()
                    ]
                ]);
            }
        }

        return response()->json([
            'uploaded' => false,
            'error' => [
                'message' => 'Không tìm thấy file upload'
            ]
        ]);
    }
}
