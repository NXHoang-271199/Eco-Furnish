<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Product;
use App\Models\GalleryImage;
use App\Models\ProductVariant;
use App\Models\Variant;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);
        $categories = Category::all();
        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $variants = Variant::with('values')->get();
        return view('admin.products.create', compact('categories', 'variants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Tự sinh mã sản phẩm cho tất cả sản phẩm
            $latestProduct = Product::latest()->first();
            $nextId = $latestProduct ? $latestProduct->id + 1 : 1;
            $data['product_code'] = 'SP' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            // Đặt giá mặc định là 0 cho sản phẩm có biến thể
            if ($request->has('variants')) {
                $data['price'] = 0;
            }

            if ($request->hasFile('image_thumnail')) {
                $data['image_thumnail'] = $request->file('image_thumnail')->store('products', 'public');
            }

            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $images[] = $image->store('products', 'public');
                }
                $data['images'] = json_encode($images);
            }

            // Tạo sản phẩm
            $product = Product::create($data);

            // Xử lý biến thể nếu có
            if ($request->has('variants')) {
                foreach ($request->variants as $variant) {
                    foreach ($variant['values'] as $valueId) {
                        // Lấy thông tin variant_id từ variant_value
                        $variantValue = DB::table('variant_values')
                            ->where('id', $valueId)
                            ->first();

                        if ($variantValue) {
                            // Tạo product variant
                            ProductVariant::create([
                                'product_id' => $product->id,
                                'variant_id' => $variantValue->variant_id,
                                'variant_value_id' => $valueId,
                                'sku' => $variant['sku'],
                                'price' => $variant['price'],
                                'status' => 1
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thêm sản phẩm thành công',
                'redirect' => route('admin.products.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi thêm sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('image_thumnail')) {
                if ($product->image_thumnail) {
                    Storage::disk('public')->delete($product->image_thumnail);
                }
                $data['image_thumnail'] = $request->file('image_thumnail')->store('products', 'public');
            }

            if ($request->hasFile('images')) {
                if ($product->images) {
                    foreach (json_decode($product->images) as $image) {
                        Storage::disk('public')->delete($image);
                    }
                }
                $images = [];
                foreach ($request->file('images') as $image) {
                    $images[] = $image->store('products', 'public');
                }
                $data['images'] = json_encode($images);
            }

            $product->update($data);

            return redirect()->route('admin.products.index')->with('success', 'Cập nhật sản phẩm thành công');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi cập nhật sản phẩm');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            if ($product->image_thumnail) {
                Storage::disk('public')->delete($product->image_thumnail);
            }

            if ($product->images) {
                foreach (json_decode($product->images) as $image) {
                    Storage::disk('public')->delete($image);
                }
            }

            $product->delete();

            return redirect()->route('admin.products.index')->with('success', 'Xóa sản phẩm thành công');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi xóa sản phẩm');
        }
    }
}
