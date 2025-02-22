<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VariantValue\StoreVariantValueRequest;
use App\Http\Requests\Admin\VariantValue\UpdateVariantValueRequest;
use App\Models\Variant;
use App\Models\VariantValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VariantValueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Variant $variant)
    {
        try {
            $values = $variant->values()->latest()->paginate(10);
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
    public function store(StoreVariantValueRequest $request, Variant $variant)
    {
        try {
            DB::beginTransaction();

            $variant->values()->create($request->validated());

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Giá trị biến thể đã được tạo thành công',
                'redirect' => route('variants.values.index', $variant)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating variant value: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo giá trị biến thể: ' . $e->getMessage()
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
    public function update(UpdateVariantValueRequest $request, Variant $variant, VariantValue $value)
    {
        try {
            DB::beginTransaction();

            $value->update($request->validated());

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Giá trị biến thể đã được cập nhật thành công',
                'redirect' => route('variants.values.index', $variant)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating variant value: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật giá trị biến thể: ' . $e->getMessage()
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

            // Kiểm tra xem giá trị biến thể có đang được sử dụng không
            if ($value->products()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa giá trị biến thể này vì đang được sử dụng trong sản phẩm.'
                ]);
            }

            $value->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Giá trị biến thể đã được xóa thành công.'
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
}
