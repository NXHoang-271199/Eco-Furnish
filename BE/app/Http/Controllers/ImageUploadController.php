<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageUploadController extends Controller
{
    public function upload(Request $request)
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . Str::random(10) . '.' . $extension;
            
            // Lưu file vào thư mục storage/app/public/uploads
            $path = $file->storeAs('uploads', $fileName, 'public');
            
            // Trả về URL của ảnh để chèn vào editor
            return response()->json([
                'success' => true,
                'url' => Storage::url($path)
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy file'
        ], 400);
    }
}
