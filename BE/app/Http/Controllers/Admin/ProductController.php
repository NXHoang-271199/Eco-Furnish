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
use App\Models\VariantValue;
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
        return view('admins.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::all();
        $variants = Variant::with('values')->get();
        return view('admins.products.create', compact('categories', 'variants'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Tự sinh mã sản phẩm
            $latestProduct = Product::latest()->first();
            $nextId = $latestProduct ? $latestProduct->id + 1 : 1;
            $data['product_code'] = 'SP' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            // Xử lý ảnh đại diện
            if ($request->hasFile('image_thumnail')) {
                $data['image_thumnail'] = $request->file('image_thumnail')->store('products', 'public');
            }

            // Tạo sản phẩm
            $product = Product::create($data);

            // Xử lý gallery images nếu có
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    $imagePath = $image->store('products/gallery', 'public');
                    GalleryImage::create([
                        'product_id' => $product->id,
                        'image_url' => $imagePath
                    ]);
                }
            }

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

            // Thêm thông báo vào session flash
            session()->flash('success', 'Thêm sản phẩm thành công');

            return response()->json([
                'success' => true,
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
        $product->load(['gallery', 'category', 'variants.variant', 'variants.variantValue']);
        return view('admins.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $categories = Category::all();
        $variants = Variant::with('values')->get();
        $colorVariants = VariantValue::where('variant_id', 1)->get();
        $capacityVariants = VariantValue::where('variant_id', 2)->get();
        $product->load(['gallery', 'variants.variant', 'variants.variantValue']);
        return view('admins.products.edit', compact('product', 'categories', 'variants', 'colorVariants', 'capacityVariants'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            DB::beginTransaction();
            
            $data = $request->validated();

            // Xử lý ảnh đại diện
            if ($request->hasFile('image_thumnail')) {
                // Xóa ảnh cũ nếu có
                if ($product->image_thumnail) {
                    Storage::disk('public')->delete($product->image_thumnail);
                }
                $data['image_thumnail'] = $request->file('image_thumnail')->store('products', 'public');
            }

            // Xử lý gallery images
            if ($request->hasFile('gallery')) {
                // Xóa gallery images cũ
                foreach ($product->gallery as $image) {
                    Storage::disk('public')->delete($image->image_url);
                }
                $product->gallery()->delete();

                // Thêm gallery images mới
                foreach ($request->file('gallery') as $image) {
                    $imagePath = $image->store('products/gallery', 'public');
                    $product->gallery()->create([
                        'image_url' => $imagePath
                    ]);
                }
            }

            // Xử lý biến thể nếu có
            if ($request->has('variants')) {
                // Xóa các biến thể cũ
                $product->variants()->delete();

                // Thêm biến thể mới
                foreach ($request->variants as $variant) {
                    foreach ($variant['variant_values'] as $variantId => $valueId) {
                        $product->variants()->create([
                            'variant_id' => $variantId,
                            'variant_value_id' => $valueId,
                            'sku' => $variant['sku'],
                            'price' => $variant['price'],
                            'status' => 1
                        ]);
                    }
                }
            }

            // Cập nhật thông tin sản phẩm
            $product->update($data);

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Cập nhật sản phẩm thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating product: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Có lỗi xảy ra khi cập nhật sản phẩm: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            DB::beginTransaction();

            // Xóa ảnh thumbnail
            if ($product->image_thumnail) {
                Storage::disk('public')->delete($product->image_thumnail);
            }

            // Xóa gallery images
            foreach ($product->gallery as $image) {
                Storage::disk('public')->delete($image->image_url);
            }
            $product->gallery()->delete();

            // Xóa product variants
            $product->variants()->delete();

            // Xóa sản phẩm
            $product->delete();

            DB::commit();
            return redirect()->route('admin.products.index')->with('success', 'Xóa sản phẩm thành công');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Có lỗi xảy ra khi xóa sản phẩm');
        }
    }
}
