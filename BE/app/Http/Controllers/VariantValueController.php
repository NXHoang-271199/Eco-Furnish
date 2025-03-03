<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VariantValue\StoreVariantValueRequest;
use App\Http\Requests\Admin\VariantValue\UpdateVariantValueRequest;
use App\Models\Variant;
use App\Models\VariantValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class VariantValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Variant $variant)
    {
        try {
            $values = $variant->values()->whereNull('deleted_at')->latest()->paginate(10);
            return view('admins.variant-values.index', compact('variant', 'values'));
        } catch (\Exception $e) {
            Log::error('Error in variant value index: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải danh sách giá trị biến thể');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Variant $variant)
    {
        try {
            return view('admins.variant-values.create', compact('variant'));
        } catch (\Exception $e) {
            Log::error('Error in variant value create: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang tạo giá trị biến thể');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Variant $variant)
    {
        try {
            $request->validate([
                'value' => 'required|string|max:255'
            ]);

            // Kiểm tra giá trị trùng lặp bao gồm cả trong thùng rác
            $existingValue = VariantValue::withTrashed()
                ->where('variant_id', $variant->id)
                ->where('value', $request->value)
                ->first();

            if ($existingValue) {
                if ($existingValue->trashed()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Giá trị biến thể này đã tồn tại và hiện đang ở trong thùng rác, xin vui lòng khôi phục lại',
                        'variant_value_in_trash' => true,
                        'variant_value_id' => $existingValue->id
                    ], 422);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Giá trị biến thể này đã tồn tại'
                    ], 422);
                }
            }

            $variant->values()->create([
                'value' => $request->value
            ]);

            session()->flash('success', 'Thêm giá trị biến thể ' . $request->value . ' thành công');

            return response()->json([
                'success' => true,
                'redirect' => route('variants.values.index', $variant)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error creating variant value: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thêm giá trị biến thể'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Variant $variant, VariantValue $value)
    {
        try {
            return view('admins.variant-values.edit', compact('variant', 'value'));
        } catch (\Exception $e) {
            Log::error('Error in variant value edit: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang chỉnh sửa giá trị biến thể');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Variant $variant, VariantValue $value)
    {
        try {
            $request->validate([
                'value' => 'required|string|max:255'
            ]);

            // Kiểm tra giá trị trùng lặp, loại trừ bản ghi hiện tại
            $exists = VariantValue::where('variant_id', $variant->id)
                ->where('value', $request->value)
                ->where('id', '!=', $value->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Giá trị biến thể này đã tồn tại'
                ], 422);
            }

            $value->update([
                'value' => $request->value
            ]);

            session()->flash('success', 'Cập nhật giá trị biến thể thành công');

            return response()->json([
                'success' => true,
                'redirect' => route('variants.values.index', $variant)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating variant value: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật giá trị biến thể'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Variant $variant, VariantValue $value)
    {
        try {
            DB::beginTransaction();

            $value->delete();

            DB::commit();

            session()->flash('success', 'Giá trị biến thể đã được xóa thành công');

            return response()->json([
                'success' => true,
                'redirect' => route('variants.values.index', $variant)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting variant value: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa giá trị biến thể: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Restore the specified variant value from trash.
     */
    public function restore($id)
    {
        try {
            DB::beginTransaction();

            $variantValue = VariantValue::withTrashed()->findOrFail($id);
            $variantValue->restore();

            DB::commit();

            return redirect()->route('variants.values.index', $variantValue->variant_id)
                ->with('success', 'Giá trị biến thể đã được khôi phục thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restoring variant value: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi khôi phục giá trị biến thể');
        }
    }
}
