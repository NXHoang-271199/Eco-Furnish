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
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

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

            // Tạo sản phẩm
            $product = Product::create($data);

            // Xử lý ảnh đại diện
            if ($request->hasFile('image_thumnail')) {
                // Tạo thư mục cho sản phẩm
                $productFolder = 'products/' . $product->id;
                Storage::disk('public')->makeDirectory($productFolder);

                // Xử lý ảnh và lưu trữ
                $image = $request->file('image_thumnail');
                $imageName = 'thumbnail.webp';
                $imagePath = $productFolder . '/' . $imageName;
                
                // Chuyển đổi ảnh sang WebP
                $this->convertToWebP($image, $imagePath);
                
                // Lưu đường dẫn vào cơ sở dữ liệu
                $product->image_thumnail = $imagePath;
                $product->save();
            }

            // Xử lý gallery images nếu có
            if ($request->hasFile('gallery')) {
                // Tạo thư mục gallery bên trong thư mục sản phẩm
                $galleryFolder = 'products/' . $product->id . '/gallery';
                Storage::disk('public')->makeDirectory($galleryFolder);

                foreach ($request->file('gallery') as $index => $image) {
                    // Xử lý ảnh và lưu trữ
                    $imageName = 'gallery_' . ($index + 1) . '.webp';
                    $imagePath = $galleryFolder . '/' . $imageName;
                    
                    // Chuyển đổi ảnh sang WebP
                    $this->convertToWebP($image, $imagePath);
                    
                    // Lưu thông tin vào cơ sở dữ liệu
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
                
                // Tạo thư mục cho sản phẩm nếu chưa tồn tại
                $productFolder = 'products/' . $product->id;
                Storage::disk('public')->makeDirectory($productFolder);

                // Xử lý ảnh và lưu trữ
                $image = $request->file('image_thumnail');
                $imageName = 'thumbnail.webp';
                $imagePath = $productFolder . '/' . $imageName;
                
                // Chuyển đổi ảnh sang WebP
                $this->convertToWebP($image, $imagePath);
                
                // Lưu đường dẫn vào dữ liệu
                $data['image_thumnail'] = $imagePath;
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
                // Tạo thư mục gallery bên trong thư mục sản phẩm nếu chưa tồn tại
                $galleryFolder = 'products/' . $product->id . '/gallery';
                Storage::disk('public')->makeDirectory($galleryFolder);
                
                // Đếm số ảnh hiện có trong gallery
                $currentGalleryCount = $product->gallery()->count();

                foreach ($request->file('gallery') as $index => $image) {
                    // Xử lý ảnh và lưu trữ
                    $imageName = 'gallery_' . ($currentGalleryCount + $index + 1) . '.webp';
                    $imagePath = $galleryFolder . '/' . $imageName;
                    
                    // Chuyển đổi ảnh sang WebP
                    $this->convertToWebP($image, $imagePath);
                    
                    // Lưu thông tin vào cơ sở dữ liệu
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

    /**
     * Generate product variants based on selected attributes
     */
    public function generateVariants(Request $request)
    {
        try {
            // Xử lý dữ liệu từ FormData
            $jsonData = $request->input('data');
            
            // Log dữ liệu nhận được để debug
            \Log::info('Received data for variant generation:', [
                'raw_data' => $request->all(),
                'json_data' => $jsonData
            ]);
            
            if (empty($jsonData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không nhận được dữ liệu'
                ], 400);
            }
            
            // Parse JSON data
            $data = json_decode($jsonData, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dữ liệu JSON không hợp lệ: ' . json_last_error_msg()
                ], 400);
            }
            
            $productId = $data['product_id'] ?? null;
            $variantAttributes = $data['variant_attributes'] ?? [];
            
            if (empty($variantAttributes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vui lòng chọn ít nhất một thuộc tính biến thể'
                ], 400);
            }
            
            // Lấy thông tin sản phẩm nếu có
            $product = null;
            if ($productId) {
                $product = Product::find($productId);
                if (!$product) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không tìm thấy sản phẩm'
                    ], 404);
                }
            }
            
            // Lấy thông tin các thuộc tính và giá trị
            $attributeValues = [];
            foreach ($variantAttributes as $variantId => $valueIds) {
                if (empty($valueIds)) continue;
                
                $variant = Variant::find($variantId);
                if (!$variant) continue;
                
                $values = VariantValue::whereIn('id', $valueIds)
                    ->where('variant_id', $variantId)
                    ->get();
                
                if ($values->isEmpty()) continue;
                
                $attributeValues[$variantId] = [
                    'name' => $variant->name,
                    'values' => $values->map(function($value) {
                        return [
                            'id' => $value->id,
                            'value' => $value->value
                        ];
                    })->toArray()
                ];
            }
            
            if (empty($attributeValues)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy giá trị thuộc tính hợp lệ'
                ], 400);
            }
            
            // Tạo tất cả các tổ hợp có thể
            $combinations = $this->generateCombinations($attributeValues);
            
            // Kiểm tra các biến thể đã tồn tại nếu đang chỉnh sửa sản phẩm
            $existingVariants = [];
            if ($product) {
                $existingVariants = $product->variants()
                    ->with(['variant:id,name', 'variantValue:id,value'])
                    ->get()
                    ->map(function($variant) {
                        return [
                            'id' => $variant->id,
                            'variant_id' => $variant->variant_id,
                            'variant_name' => $variant->variant->name,
                            'variant_value_id' => $variant->variant_value_id,
                            'variant_value' => $variant->variantValue->value,
                            'sku' => $variant->sku,
                            'price' => $variant->price,
                            'quantity' => $variant->quantity,
                            'status' => $variant->status
                        ];
                    })
                    ->toArray();
            }
            
            // Chuẩn bị dữ liệu phản hồi
            $variants = [];
            $basePrice = $product ? $product->price : 0;
            
            foreach ($combinations as $combination) {
                $variantData = [
                    'attributes' => $combination,
                    'sku' => $product ? $product->product_code . '-' : 'SKU-',
                    'price' => $basePrice,
                    'quantity' => 0,
                    'status' => 1
                ];
                
                // Tạo SKU dựa trên tổ hợp thuộc tính
                foreach ($combination as $attr) {
                    $variantData['sku'] .= strtoupper(substr($attr['value'], 0, 2));
                }
                
                $variants[] = $variantData;
            }
            
            return response()->json([
                'success' => true,
                'variants' => $variants,
                'existing_variants' => $existingVariants
            ]);
        } catch (\Exception $e) {
            \Log::error('Error generating variants: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            // Xử lý lỗi mã hóa UTF-8
            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'Malformed UTF-8 characters') !== false) {
                $errorMessage = 'Lỗi mã hóa ký tự Unicode. Vui lòng kiểm tra lại các giá trị thuộc tính có chứa ký tự đặc biệt.';
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo biến thể: ' . $errorMessage,
                'error_type' => get_class($e)
            ], 500);
        }
    }
    
    /**
     * Generate all possible combinations of attribute values
     */
    private function generateCombinations($attributeValues)
    {
        $result = [[]];
        
        foreach ($attributeValues as $variantId => $attribute) {
            $append = [];
            
            foreach ($result as $product) {
                foreach ($attribute['values'] as $value) {
                    $product[] = [
                        'variant_id' => $variantId,
                        'variant_name' => $attribute['name'],
                        'value_id' => $value['id'],
                        'value' => $value['value']
                    ];
                    $append[] = $product;
                }
            }
            
            $result = $append;
        }
        
        return $result;
    }

    private function convertToWebP($image, $imagePath)
    {
        // Đọc ảnh gốc
        $sourceImage = null;
        $extension = strtolower($image->getClientOriginalExtension());
        
        // Tạo hình ảnh từ file dựa trên định dạng
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                $sourceImage = imagecreatefromjpeg($image->getPathname());
                break;
            case 'png':
                $sourceImage = imagecreatefrompng($image->getPathname());
                break;
            case 'gif':
                $sourceImage = imagecreatefromgif($image->getPathname());
                break;
            default:
                // Nếu không phải định dạng hỗ trợ, lưu trực tiếp
                Storage::disk('public')->putFileAs(dirname($imagePath), $image, basename($imagePath));
                return;
        }
        
        if (!$sourceImage) {
            // Nếu không đọc được ảnh, lưu trực tiếp
            Storage::disk('public')->putFileAs(dirname($imagePath), $image, basename($imagePath));
            return;
        }
        
        // Lưu ảnh dưới dạng webp
        ob_start();
        imagewebp($sourceImage, null, 80);
        $webpData = ob_get_contents();
        ob_end_clean();
        
        // Giải phóng bộ nhớ
        imagedestroy($sourceImage);
        
        // Lưu ảnh WebP vào storage
        Storage::disk('public')->put($imagePath, $webpData);
    }
}
