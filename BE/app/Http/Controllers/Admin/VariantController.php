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
<<<<<<< HEAD
            $variants = Variant::with('values')->latest()->paginate(10);
=======
            $variants = Variant::with('values')->get();
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
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
            return redirect()->route('variants.index')
                ->with('success', 'Biến thể đã được tạo thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating variant: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi tạo biến thể: ' . $e->getMessage());
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
            return redirect()->route('variants.index')
                ->with('success', 'Biến thể đã được cập nhật thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating variant: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra khi cập nhật biến thể: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Variant $variant)
    {
        try {
            DB::beginTransaction();

            // Kiểm tra xem biến thể có đang được sử dụng không
            if ($variant->values()->exists()) {
                return back()->with('error', 'Không thể xóa biến thể này vì đang có giá trị biến thể được sử dụng.');
            }

            $variant->delete();

            DB::commit();
            return redirect()->route('variants.index')
                ->with('success', 'Biến thể đã được xóa thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting variant: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi xóa biến thể: ' . $e->getMessage());
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
