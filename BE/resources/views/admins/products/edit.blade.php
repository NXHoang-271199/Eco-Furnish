@extends('layouts.admin')

@section('title', 'Chỉnh sửa sản phẩm')

@section('CSS')
    @include('partials.products.edit_css')
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Chỉnh sửa sản phẩm</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                    <li class="breadcrumb-item active">Chỉnh sửa sản phẩm</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<form id="productForm" action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="row">
        <!-- Thông tin cơ bản -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông tin cơ bản</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="product_code">Mã sản phẩm</label>
                        <input type="text" class="form-control" id="product_code" name="product_code" 
                            value="{{ $product->product_code }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="name">Tên sản phẩm <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                            id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="category_id">Danh mục <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" 
                            id="category_id" name="category_id" required>
                            <option value="">Chọn danh mục</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" 
                                    {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="description">Mô tả</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                            id="description" name="description">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="price">Giá cơ bản <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                id="price" name="price" value="{{ old('price', $product->price) }}" required min="0">
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        @error('price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3" id="discountPriceSection">
                        <label class="form-label" for="discount_price">Giá khuyến mãi</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('discount_price') is-invalid @enderror" 
                                id="discount_price" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}" min="0">
                            <span class="input-group-text">VNĐ</span>
                        </div>
                        @error('discount_price')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Ảnh sản phẩm -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ảnh sản phẩm</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label" for="image_thumnail">Ảnh đại diện</label>
                        <input type="file" class="form-control @error('image_thumnail') is-invalid @enderror"
                            id="image_thumnail" name="image_thumnail" accept="image/*">
                        @error('image_thumnail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        @if($product->image_thumnail)
                            <div id="thumbnailPreview" class="mt-2">
                                <img src="{{ asset('storage/' . $product->image_thumnail) }}" alt="Thumbnail preview">
                                <div class="remove-photo" onclick="removeThumbnail()">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Thư viện ảnh</label>
                        <input type="file" class="form-control @error('gallery.*') is-invalid @enderror"
                            id="gallery" name="gallery[]" multiple accept="image/*" style="display: none;">
                        @error('gallery.*')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        
                        <div class="gallery-preview">
                            <div class="gallery-preview-container" id="galleryPreview">
                                @foreach($product->gallery as $image)
                                    <div class="gallery-item" data-id="{{ $image->id }}">
                                        <img src="{{ asset('storage/' . $image->image_url) }}" alt="Gallery image">
                                        <div class="remove-photo" onclick="removeGalleryItem(this)">
                                            <i class="fas fa-times"></i>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="gallery-item add-photo" onclick="document.getElementById('gallery').click()">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Biến thể sản phẩm -->
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Biến thể sản phẩm</h5>
                </div>
                <div class="card-body">
                    <div id="variants-container">
                        @if($product->variants && $product->variants->count() > 0)
                            @foreach($product->variants->groupBy('sku') as $sku => $variants)
                                <div class="variant-combination">
                                    <div class="row">
                                        @foreach($variants->groupBy('variant_id') as $variantId => $variantGroup)
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">{{ $variantGroup->first()->variant->name }} <span class="text-danger">*</span></label>
                                                    <select class="form-select variant-select" 
                                                        name="variants[{{ $loop->parent->index }}][variant_values][{{ $variantId }}]" required>
                                                        <option value="">Chọn {{ strtolower($variantGroup->first()->variant->name) }}</option>
                                                        @foreach($variants->first()->variant->values as $value)
                                                            <option value="{{ $value->id }}" 
                                                                {{ $variantGroup->first()->variant_value_id == $value->id ? 'selected' : '' }}>
                                                                {{ $value->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">SKU <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" 
                                                    name="variants[{{ $loop->index }}][sku]" 
                                                    value="{{ $sku }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Giá <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control" 
                                                        name="variants[{{ $loop->index }}][price]" 
                                                        value="{{ $variants->first()->price }}" required min="0">
                                                    <span class="input-group-text">VNĐ</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-danger btn-sm remove-variant">
                                        <i class="fas fa-trash"></i> Xóa biến thể
                                    </button>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <button type="button" class="btn btn-info" id="add-variant">
                        <i class="fas fa-plus"></i> Thêm biến thể
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="form-actions">
                <a href="{{ route('products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Lưu thay đổi
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('JS')
    @include('partials.products.edit_js')
@endsection 