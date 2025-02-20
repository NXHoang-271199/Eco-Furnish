@extends('layouts.admin')

@section('title', 'Chỉnh sửa sản phẩm')

@section('CSS')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gallery-preview {
            margin-top: 10px;
        }
        .gallery-preview-title {
            font-size: 14px;
            margin-bottom: 10px;
            color: #495057;
        }
        .gallery-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .gallery-item {
            width: 80px;
            height: 80px;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
            border: 1px solid #dee2e6;
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .gallery-item.add-photo {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px dashed #0d6efd;
            cursor: pointer;
            position: relative;
        }
        .gallery-item.add-photo::before,
        .gallery-item.add-photo::after {
            content: '';
            position: absolute;
            background: #0d6efd;
        }
        .gallery-item.add-photo::before {
            width: 2px;
            height: 20px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .gallery-item.add-photo::after {
            width: 20px;
            height: 2px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .gallery-item .remove-photo {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .gallery-item .remove-photo i {
            color: #dc3545;
            font-size: 12px;
            line-height: 1;
        }
        .gallery-item:hover .remove-photo {
            opacity: 1;
        }
        #thumbnailPreview {
            position: relative;
            display: inline-block;
        }
        #thumbnailPreview .remove-photo {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        #thumbnailPreview .remove-photo i {
            color: #dc3545;
            font-size: 12px;
            line-height: 1;
        }
        #thumbnailPreview:hover .remove-photo {
            opacity: 1;
        }
    </style>
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
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Initialize CKEditor
        ClassicEditor
            .create(document.querySelector('#description'))
            .catch(error => {
                console.error(error);
            });

        // Preview thumbnail image
        document.getElementById('image_thumnail').addEventListener('change', function(e) {
            const preview = document.getElementById('thumbnailPreview');
            const file = e.target.files[0];
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.querySelector('img').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });

        function removeThumbnail() {
            const preview = document.getElementById('thumbnailPreview');
            const input = document.getElementById('image_thumnail');
            preview.querySelector('img').src = '';
            input.value = '';
        }

        // Preview gallery images
        let galleryFiles = new DataTransfer(); // Biến lưu trữ tất cả files

        document.getElementById('gallery').addEventListener('change', function(e) {
            const preview = document.getElementById('galleryPreview');
            const files = Array.from(e.target.files);
            
            // Thêm các file mới vào galleryFiles
            files.forEach(file => {
                galleryFiles.items.add(file);
                
                // Tạo preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'gallery-item';
                    div.innerHTML = `
                        <img src="${e.target.result}" alt="Gallery preview">
                        <div class="remove-photo" onclick="removeGalleryItem(this)">
                            <i class="fas fa-times"></i>
                        </div>
                    `;
                    preview.insertBefore(div, preview.querySelector('.add-photo'));
                }
                reader.readAsDataURL(file);
            });
            
            // Cập nhật lại files cho input
            this.files = galleryFiles.files;
        });

        // Remove gallery item
        function removeGalleryItem(element) {
            const galleryInput = document.getElementById('gallery');
            const item = element.parentElement;
            const container = item.parentElement;
            const index = Array.from(container.children).indexOf(item) - 1; // Trừ 1 vì có nút add-photo
            
            // Remove preview
            item.remove();
            
            // Remove from galleryFiles
            const dt = new DataTransfer();
            const files = Array.from(galleryFiles.files);
            files.splice(index, 1);
            files.forEach(file => dt.items.add(file));
            galleryFiles = dt;
            galleryInput.files = galleryFiles.files;
        }

        $(document).ready(function() {
            const variantsContainer = $('#variants-container');
            const addVariantBtn = $('#add-variant');
            const form = $('#productForm');
            
            let variantCount = {{ $product->variants ? $product->variants->groupBy('sku')->count() : 0 }};

            // Template HTML cho biến thể mới
            function getVariantTemplate(index) {
                return `
                    <div class="variant-combination mb-3">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Màu sắc</label>
                                    <select class="form-control variant-select" name="variants[${index}][variant_values][1]" required>
                                        <option value="">Chọn màu sắc</option>
                                        @foreach($colorVariants as $value)
                                        <option value="{{ $value->id }}">{{ $value->value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Kích thước</label>
                                    <select class="form-control variant-select" name="variants[${index}][variant_values][2]" required>
                                        <option value="">Chọn kích thước</option>
                                        @foreach($capacityVariants as $value)
                                        <option value="{{ $value->id }}">{{ $value->value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="variants[${index}][sku]" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Giá <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="variants[${index}][price]" required min="0">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm mt-2 remove-variant">
                            <i class="fas fa-trash"></i> Xóa biến thể
                        </button>
                    </div>
                `;
            }

            // Thêm biến thể
            addVariantBtn.on('click', function() {
                const newVariant = getVariantTemplate(variantCount);
                variantsContainer.append(newVariant);
                variantCount++;
            });

            // Xóa biến thể
            $(document).on('click', '.remove-variant', function() {
                $(this).closest('.variant-combination').remove();
                updateVariantIndexes();
            });

            // Cập nhật lại index cho các biến thể
            function updateVariantIndexes() {
                $('.variant-combination').each(function(index) {
                    $(this).find('select, input').each(function() {
                        const name = $(this).attr('name');
                        if (name) {
                            const newName = name.replace(/variants\[\d+\]/, `variants[${index}]`);
                            $(this).attr('name', newName);
                        }
                    });
                });
            }

            // Xử lý submit form
            form.on('submit', function(e) {
                e.preventDefault();

                // Kiểm tra các trường bắt buộc
                const requiredFields = $(this).find('[required]');
                let hasEmptyField = false;

                requiredFields.each(function() {
                    if (!$(this).val()) {
                        $(this).addClass('is-invalid');
                        hasEmptyField = true;
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (hasEmptyField) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng điền đầy đủ thông tin bắt buộc!',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                // Kiểm tra biến thể trùng lặp
                const variants = $('.variant-combination');
                const variantCombinations = new Set();
                let hasDuplicate = false;

                variants.each(function() {
                    const values = $(this).find('.variant-select')
                        .map(function() { return $(this).val(); })
                        .get()
                        .join('-');
                    
                    if (variantCombinations.has(values)) {
                        hasDuplicate = true;
                    }
                    variantCombinations.add(values);
                });

                if (hasDuplicate) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Không được có biến thể trùng lặp!',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                this.submit();
            });
        });
    </script>
@endsection 