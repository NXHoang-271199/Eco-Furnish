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
        $products = Product::with('category')->latest()->paginate(3);
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
                                'quantity' => $variant['quantity'],
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
                'redirect' => route('products.index')
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
        $product->load([
            'gallery', 
            'category',
            'variants' => function($query) {
                $query->whereNull('deleted_at');
            },
            'variants.variant',
            'variants.variantValue'
        ]);
        return view('admins.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        try {
            // Load categories
            $categories = Category::all();

            // Load variants with their values
            $variants = Variant::with(['values' => function($query) {
                $query->select('id', 'variant_id', 'value')
                    ->orderBy('value');
            }])
            ->select('id', 'name')
            ->orderBy('id')
            ->get();

            // Group variants by name
            $groupedVariants = $variants->groupBy(function($variant) {
                return Str::slug($variant->name);
            });
            
            // Load product with related data, excluding soft deleted variants
            $product->load([
                'gallery',
                'variants' => function($query) {
                    $query->select('id', 'product_id', 'variant_id', 'variant_value_id', 'sku', 'price', 'quantity')
                        ->whereNull('deleted_at');
                },
                'variants.variant:id,name',
                'variants.variantValue:id,value'
            ]);

            // Log data for debugging
            \Log::info('Product data:', [
                'product' => $product->toArray(),
                'variants' => $groupedVariants->toArray()
            ]);

            return view('admins.products.edit', compact('product', 'categories', 'variants', 'groupedVariants'));
        } catch (\Exception $e) {
            \Log::error('Error loading product edit page: ' . $e->getMessage());
            return redirect()->route('products.index')
                ->with('error', 'Có lỗi xảy ra khi tải trang chỉnh sửa sản phẩm');
        }
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
            if ($request->has('removed_images')) {
                // Chuyển chuỗi JSON thành mảng
                $removedImages = json_decode($request->removed_images, true);
                
                // Xóa các ảnh đã được đánh dấu để xóa
                foreach ($removedImages as $imageId) {
                    $image = GalleryImage::find($imageId);
                    if ($image) {
                        Storage::disk('public')->delete($image->image_url);
                        $image->delete();
                    }
                }
            }

            // Thêm ảnh mới vào gallery nếu có
            if ($request->hasFile('gallery')) {
                foreach ($request->file('gallery') as $image) {
                    $imagePath = $image->store('products/gallery', 'public');
                    $product->gallery()->create([
                        'image_url' => $imagePath
                    ]);
                }
            }

            // Xử lý biến thể nếu có
            if ($request->has('variants')) {
                // Lấy danh sách variant_value_id từ request
                $requestVariantValues = collect();
                $usedCombinations = [];
                $duplicateFound = false;
                $duplicateVariants = [];

                foreach ($request->variants as $variant) {
                    // Tạo một key duy nhất cho mỗi tổ hợp biến thể
                    $variantKey = [];
                    foreach ($variant['variant_values'] as $variantId => $valueId) {
                        $variantKey[] = $variantId . '-' . $valueId;
                    }
                    sort($variantKey);
                    $combinationKey = implode('_', $variantKey);

                    // Kiểm tra xem tổ hợp này đã tồn tại chưa
                    if (in_array($combinationKey, $usedCombinations)) {
                        $duplicateFound = true;
                        // Lấy thông tin biến thể bị trùng để hiển thị trong thông báo lỗi
                        $variantInfo = [];
                        foreach ($variant['variant_values'] as $variantId => $valueId) {
                            $variantValue = DB::table('variant_values')
                                ->join('variants', 'variants.id', '=', 'variant_values.variant_id')
                                ->where('variant_values.id', $valueId)
                                ->select('variants.name', 'variant_values.value')
                                ->first();
                            if ($variantValue) {
                                $variantInfo[] = $variantValue->name . ': ' . $variantValue->value;
                            }
                        }
                        $duplicateVariants[] = implode(' - ', $variantInfo);
                        continue;
                    }

                    $usedCombinations[] = $combinationKey;

                    // Thu thập thông tin biến thể
                    foreach ($variant['variant_values'] as $variantId => $valueId) {
                        $requestVariantValues->push([
                            'variant_id' => $variantId,
                            'variant_value_id' => $valueId,
                            'sku' => $variant['sku'],
                            'price' => $variant['price'],
                            'quantity' => $variant['quantity']
                        ]);
                    }
                }

                // Nếu phát hiện biến thể trùng lặp, trả về lỗi
                if ($duplicateFound) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Phát hiện biến thể trùng lặp: ' . implode(', ', array_unique($duplicateVariants))
                    ], 422);
                }

                // Xóa các biến thể không còn trong request
                $existingVariantIds = $product->variants()
                    ->whereNull('deleted_at')
                    ->pluck('id')
                    ->toArray();

                // Cập nhật hoặc tạo mới các biến thể
                foreach ($requestVariantValues as $variantData) {
                    // Tìm biến thể hiện có
                    $existingVariant = $product->variants()
                        ->whereNull('deleted_at')
                        ->where('variant_id', $variantData['variant_id'])
                        ->where('variant_value_id', $variantData['variant_value_id'])
                        ->first();

                    if ($existingVariant) {
                        // Cập nhật biến thể hiện có
                        $existingVariant->update([
                            'sku' => $variantData['sku'],
                            'price' => $variantData['price'],
                            'quantity' => $variantData['quantity'],
                            'status' => 1
                        ]);
                        // Loại bỏ ID này khỏi danh sách cần xóa
                        $existingVariantIds = array_diff($existingVariantIds, [$existingVariant->id]);
                    } else {
                        // Tạo mới biến thể
                        $product->variants()->create([
                            'variant_id' => $variantData['variant_id'],
                            'variant_value_id' => $variantData['variant_value_id'],
                            'sku' => $variantData['sku'],
                            'price' => $variantData['price'],
                            'quantity' => $variantData['quantity'],
                            'status' => 1
                        ]);
                    }
                }

                // Xóa các biến thể không còn trong request
                if (!empty($existingVariantIds)) {
                    $product->variants()
                        ->whereIn('id', $existingVariantIds)
                        ->delete();
                }
            } else {
                // Nếu không có biến thể nào được gửi lên, xóa tất cả biến thể cũ
                $product->variants()->delete();
            }

            // Cập nhật thông tin sản phẩm
            $product->update($data);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sản phẩm thành công',
                'redirect' => route('products.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật sản phẩm: ' . $e->getMessage()
            ], 500);
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
            return response()->json([
                'success' => true,
                'message' => 'Xóa sản phẩm thành công',
                'redirect' => route('products.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa sản phẩm: ' . $e->getMessage()
            ], 500);
        }
    }
}
