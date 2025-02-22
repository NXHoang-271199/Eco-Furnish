<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Variant\StoreVariantRequest;
use App\Http\Requests\Admin\Variant\UpdateVariantRequest;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VariantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $variants = Variant::with('values')->latest()->paginate(10);
            return view('admins.variants.index', compact('variants'));
        } catch (\Exception $e) {
            Log::error('Error in variant index: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải danh sách biến thể');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return view('admins.variants.create');
        } catch (\Exception $e) {
            Log::error('Error in variant create: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang tạo biến thể');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVariantRequest $request)
    {
        try {
            DB::beginTransaction();

            Variant::create($request->validated());

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Biến thể đã được tạo thành công',
                'redirect' => route('variants.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating variant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo biến thể: ' . $e->getMessage()
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
    public function edit(Variant $variant)
    {
        try {
            return view('admins.variants.edit', compact('variant'));
        } catch (\Exception $e) {
            Log::error('Error in variant edit: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải trang chỉnh sửa biến thể');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVariantRequest $request, Variant $variant)
    {
        try {
            DB::beginTransaction();

            $variant->update($request->validated());

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Biến thể đã được cập nhật thành công',
                'redirect' => route('variants.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating variant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật biến thể: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Variant $variant)
    {
        try {
            DB::beginTransaction();

            $variant->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Biến thể đã được xóa thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting variant: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa biến thể: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getValues(Variant $variant)
    {
        try {
            $values = $variant->values;
            return view('admins.variant-values.index', compact('variant', 'values'));
        } catch (\Exception $e) {
            Log::error('Error getting variant values: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tải danh sách giá trị biến thể');
        }
    }
}
