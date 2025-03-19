<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class BannerController extends Controller
{
    /**
     * Lấy danh sách banner
     */
    public function index()
    {
        try {
            $banners = Banner::orderBy('position', 'asc')->get();
            
            // Thêm URL đầy đủ cho ảnh
            $bannersWithFullUrl = $banners->map(function($banner) {
                $banner->image_url = URL::to('/').'/'.$banner->image;
                return $banner;
            });
            
            Log::info('API Banners được gọi', ['count' => $banners->count(), 'banners' => $bannersWithFullUrl->toArray()]);
            
            return response()->json([
                'success' => true,
                'data' => $bannersWithFullUrl
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi API Banners', ['error' => $e->getMessage()]);
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }
}
