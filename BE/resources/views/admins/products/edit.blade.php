@extends('layouts.admin')

@section('title', 'Chỉnh sửa sản phẩm')

@section('CSS')
    @include('partials.products.edit_css')
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chỉnh sửa sản phẩm</h3>
                    <div class="card-tools">
                        <a href="{{ route('products.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="productForm">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="name">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                        id="name" name="name" value="{{ old('name', $product->name) }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="category_id">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-control @error('category_id') is-invalid @enderror" 
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
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="description">Mô tả</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="price">Giá cơ bản <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                        id="price" name="price" value="{{ old('price', $product->price) }}" required min="0">
                                    @error('price')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="image_thumnail">Ảnh đại diện</label>
                                    <input type="file" class="form-control @error('image_thumnail') is-invalid @enderror"
                                        id="image_thumnail" name="image_thumnail" accept="image/*">
                                    @error('image_thumnail')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="thumbnailPreview" class="mt-2">
                                        <img src="{{ asset('storage/' . $product->image_thumnail) }}" alt="Thumbnail preview" style="max-width: 200px; border-radius: 8px;">
                                        <div class="remove-photo" onclick="removeThumbnail()">
                                            <i class="fas fa-times"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="gallery">Thư viện ảnh</label>
                                    <input type="file" class="form-control @error('gallery.*') is-invalid @enderror"
                                        id="gallery" name="gallery[]" multiple accept="image/*" style="display: none;">
                                    @error('gallery.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    <div class="gallery-preview">
                                        <h6 class="gallery-preview-title">Ảnh phụ sản phẩm</h6>
                                        <div class="gallery-preview-container" id="galleryPreview">
                                            @foreach($product->gallery as $image)
                                                <div class="gallery-item">
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

                        <hr>
                        <h4>Biến thể sản phẩm</h4>
                        <div id="variants-container">
                            @if($product->variants && $product->variants->count() > 0)
                                @foreach($product->variants->groupBy('sku') as $sku => $variants)
                                    <div class="variant-combination mb-3">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Màu sắc</label>
                                                    <select class="form-control variant-select" 
                                                        name="variants[{{ $loop->index }}][variant_values][1]" 
                                                        required>
                                                        <option value="">Chọn màu sắc</option>
                                                        @foreach($colorVariants as $value)
                                                            <option value="{{ $value->id }}" 
                                                                {{ $variants->where('variant_id', 1)->first()?->variant_value_id == $value->id ? 'selected' : '' }}>
                                                                {{ $value->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Kích thước</label>
                                                    <select class="form-control variant-select" 
                                                        name="variants[{{ $loop->index }}][variant_values][2]" 
                                                        required>
                                                        <option value="">Chọn kích thước</option>
                                                        @foreach($capacityVariants as $value)
                                                            <option value="{{ $value->id }}" 
                                                                {{ $variants->where('variant_id', 2)->first()?->variant_value_id == $value->id ? 'selected' : '' }}>
                                                                {{ $value->value }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>SKU <span class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" 
                                                        name="variants[{{ $loop->index }}][sku]" 
                                                        value="{{ $sku }}"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label>Giá <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" 
                                                        name="variants[{{ $loop->index }}][price]" 
                                                        value="{{ $variants->first()->price }}"
                                                        required min="0">
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm mt-2 remove-variant">
                                            <i class="fas fa-trash"></i> Xóa biến thể
                                        </button>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <button type="button" class="btn btn-info mb-3" id="add-variant">
                            <i class="fas fa-plus"></i> Thêm biến thể
                        </button>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('JS')
    @include('partials.products.edit_js')
@endsection 