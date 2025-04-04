<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Variant;
use Illuminate\Http\Request;

class VariantApiController extends Controller
{
    /**
     * Hiển thị danh sách tất cả biến thể
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $variants = Variant::with('values')
                ->orderBy('name')
                ->get()
                ->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'values' => $variant->values->map(function ($value) {
                            return [
                                'id' => $value->id,
                                'value' => $value->value
                            ];
                        })
                    ];
                });
                
            return response()->json([
                'status' => 'success',
                'data' => $variants
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Có lỗi xảy ra khi lấy danh sách biến thể: ' . $e->getMessage()
            ], 500);
        }
    }
} 