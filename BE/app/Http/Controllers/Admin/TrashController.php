<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Variant;
use App\Models\VariantValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrashController extends Controller
{
    public function index()
    {
        $type = request()->segment(3);
        switch($type) {
            case 'trash-products':
                $items = Product::onlyTrashed()->latest()->paginate(10);
                return view('admins.trash.products', compact('items'));
            case 'trash-categories':
                $items = Category::onlyTrashed()->latest()->paginate(10);
                return view('admins.trash.categories', compact('items'));
            case 'trash-variants':
                $items = Variant::onlyTrashed()->latest()->paginate(10);
                return view('admins.trash.variants', compact('items'));
            case 'trash-variant-values':
                $items = VariantValue::onlyTrashed()->latest()->paginate(10);
                return view('admins.trash.variant-values', compact('items'));
            default:
                abort(404);
        }
    }

    public function update($id)
    {
        try {
            $type = request()->segment(3);
            switch($type) {
                case 'trash-products':
                    $item = Product::onlyTrashed()->findOrFail($id);
                    break;
                case 'trash-categories':
                    $item = Category::onlyTrashed()->findOrFail($id);
                    break;
                case 'trash-variants':
                    $item = Variant::onlyTrashed()->findOrFail($id);
                    break;
                case 'trash-variant-values':
                    $item = VariantValue::onlyTrashed()->findOrFail($id);
                    break;
                default:
                    abort(404);
            }

            $item->restore();
            
            // Xử lý URL redirect
            $redirectUrl = '/admin/trash/';
            switch($type) {
                case 'trash-products':
                    $redirectUrl .= 'trash-products';
                    break;
                case 'trash-categories':
                    $redirectUrl .= 'trash-categories';
                    break;
                case 'trash-variants':
                    $redirectUrl .= 'trash-variants';
                    break;
                case 'trash-variant-values':
                    $redirectUrl .= 'trash-variant-values';
                    break;
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Khôi phục thành công',
                'redirect' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi khôi phục'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $type = request()->segment(3);
            switch($type) {
                case 'trash-products':
                    $item = Product::onlyTrashed()->findOrFail($id);
                    if ($item->image_thumbnail) {
                        Storage::disk('public')->delete($item->image_thumbnail);
                    }
                    foreach ($item->gallery as $image) {
                        Storage::disk('public')->delete($image->image_url);
                    }
                    break;
                case 'trash-categories':
                    $item = Category::onlyTrashed()->findOrFail($id);
                    break;
                case 'trash-variants':
                    $item = Variant::onlyTrashed()->findOrFail($id);
                    break;
                case 'trash-variant-values':
                    $item = VariantValue::onlyTrashed()->findOrFail($id);
                    break;
                default:
                    abort(404);
            }

            $item->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => 'Xóa vĩnh viễn thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa vĩnh viễn'
            ], 500);
        }
    }
} 