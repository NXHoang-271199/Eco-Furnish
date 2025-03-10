
@extends('layouts.admin')

@section('title', 'Tạo sản phẩm mới')

@section('CSS')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.products.create_css')
@endsection

@section('content')
<div class="form-container">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 text-primary">Tạo sản phẩm mới</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}" class="text-primary">Sản phẩm</a></li>
                        <li class="breadcrumb-item active">Tạo sản phẩm mới</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <form id="productForm" action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" data-ajax="false">
        @csrf
        <input type="hidden" name="has_variants" value="0" id="hasVariants">

        <div class="row">
            <!-- Thông tin cơ bản -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="text-white mb-0">Thông tin cơ bản</h5>
                        <div class="switch-container">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="variantToggle">
                                <label class="form-check-label text-white" for="variantToggle">Thêm biến thể</label>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        id="name" name="name" value="{{ old('name') }}" >
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror"
                                        id="category_id" name="category_id">
                                        <option value="">Chọn danh mục</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div id="basicPriceSection">
                                    <div class="form-group mb-3">
                                        <label for="price" class="form-label">Giá gốc <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control @error('price') is-invalid @enderror"
                                                id="price" name="price" value="{{ old('price') }}" min="0">
                                            <span class="input-group-text">VNĐ</span>
                                            @error('price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group mb-3" id="discountPriceSection">
                                        <label for="discount_price" class="form-label">Giá khuyến mãi</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control @error('discount_price') is-invalid @enderror"
                                                id="discount_price" name="discount_price" value="{{ old('discount_price') }}" min="0">
                                            <span class="input-group-text">VNĐ</span>
                                            @error('discount_price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">Mô tả</label>
                                    <textarea class="form-control"
                                        id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ảnh sản phẩm -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="text-white mb-0">Ảnh sản phẩm</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-4">
                            <label for="image_thumnail" class="form-label">Ảnh đại diện <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('image_thumnail') is-invalid @enderror"
                                id="image_thumnail" name="image_thumnail" accept="image/*">
                            @error('image_thumnail')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div id="thumbnailPreview" class="mt-2 main-image-preview" style="display: none;">
                                <img src="" alt="Thumbnail preview">
                                <div class="remove-photo" onclick="removeThumbnail()">
                                    <i class="fas fa-times"></i>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="gallery" class="form-label">Thư viện ảnh</label>
                            <input type="file" class="form-control @error('gallery.*') is-invalid @enderror"
                                id="gallery" name="gallery[]" multiple accept="image/*" style="display: none;">
                            @error('gallery.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="gallery-preview">
                                <div class="gallery-preview-container" id="galleryPreview">
                                    <div class="gallery-item add-photo" onclick="document.getElementById('gallery').click()">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Phần biến thể -->
            <div class="col-12">
                <div class="card variant-section" id="variantSection">
                    <div class="card-header">
                        <h5 class="text-white mb-0">Biến thể sản phẩm</h5>
                    </div>
                    <div class="card-body">
                        <!-- Form chọn biến thể -->
                        <div class="variant-selector mb-4">
                            <div class="row align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Chọn thuộc tính</label>
                                    <select class="form-select" id="variantTypeSelect">
                                        <option value="">Chọn thuộc tính biến thể</option>
                                        @foreach($variants as $variant)
                                            <option value="{{ $variant->id }}" data-values='@json($variant->values)'>{{ $variant->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-primary w-100" id="addVariantTypeBtn">
                                        <i class="fas fa-plus me-2"></i>Thêm
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Danh sách các biến thể đã chọn -->
                        <div id="selectedVariantTypes" class="mb-4">
                            <!-- Các biến thể đã chọn sẽ được thêm vào đây bằng JavaScript -->
                        </div>

                        <!-- Form thêm biến thể -->
                        <div id="variantForm" class="border rounded p-4 mb-4 d-none" style="background: #f8f9fa;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="variant-sku">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Giá <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="variant-price" min="0">
                                        <span class="input-group-text">VNĐ</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="variant-quantity" min="0">
                                </div>
                            </div>
                            <button type="button" class="btn btn-primary" id="add-variant-btn">
                                <i class="fas fa-plus me-2"></i>Thêm biến thể
                            </button>
                        </div>

                        <!-- Nút tạo biến thể tự động -->
                        <div id="generate-variants-container" class="mb-4" style="display: none;">
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-info-circle fa-2x"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">Tạo biến thể tự động</h5>
                                        <p class="mb-0">Hệ thống sẽ tạo tất cả các tổ hợp biến thể có thể từ các thuộc tính và giá trị bạn đã chọn.</p>
                                    </div>
                                </div>
                                <div class="text-end mt-3">
                                    <button type="button" class="btn btn-info" id="generate-variants-btn" style="display: none;">
                                        <i class="fas fa-magic me-2"></i>Tạo biến thể tự động
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Danh sách biến thể đã thêm -->
                        <div id="variants-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="text-end">
                            <a href="{{ route('products.index') }}" class="btn btn-secondary me-2">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Lưu sản phẩm
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('JS')
    @include('partials.products.create_js')
@endsection
