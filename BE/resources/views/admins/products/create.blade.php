@extends('layouts.admin')

@section('title', 'Tạo sản phẩm mới')

@section('CSS')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Thêm container wrapper để thu nhỏ form */
        .form-container {
            transform: scale(0.75);
            transform-origin: top left;
            width: 133.33%; /* Bù lại kích thước để tránh lệch layout */
            padding-bottom: 2rem;
        }
        
        .variant-section {
            display: none;
            transition: all 0.3s ease;
        }
        .variant-section.show {
            display: block;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .card-header {
            background: linear-gradient(45deg, #3b7ddd, #2f69b8);
            border-radius: 12px 12px 0 0 !important;
            padding: 20px;
        }
        .card-header h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        .card-body {
            padding: 24px;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #344767;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3b7ddd;
            box-shadow: 0 0 0 0.2rem rgba(59, 125, 221, 0.1);
        }
        .switch-container {
            background: rgba(255,255,255,0.1);
            padding: 6px 16px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .form-switch {
            padding-left: 3.5em;
            margin-bottom: 0;
        }
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            background-color: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.3);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba(255,255,255,1)'/%3e%3c/svg%3e");
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .form-switch .form-check-input:checked {
            background-color: #00d084;
            border-color: #00d084;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
            box-shadow: 0 0 8px rgba(0, 208, 132, 0.5);
        }
        .form-switch .form-check-input:focus {
            border-color: rgba(255,255,255,0.5);
            box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.2);
        }
        .form-switch .form-check-input:checked:focus {
            border-color: #00d084;
            box-shadow: 0 0 0 0.2rem rgba(0, 208, 132, 0.3);
        }
        .form-check-label {
            font-weight: 500;
            cursor: pointer;
            font-size: 0.95rem;
            user-select: none;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: linear-gradient(45deg, #3b7ddd, #2f69b8);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #2f69b8, #3b7ddd);
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #f8f9fa;
            color: #344767;
            border: 1px solid #e9ecef;
        }
        .btn-secondary:hover {
            background: #e9ecef;
            border-color: #dde1e5;
        }
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
            background: #f8f9fa;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            max-width: fit-content;
        }
      
        #thumbnailPreview img {
            max-width: 150px;
            max-height: 150px;
            object-fit: contain;
            border-radius: 4px;
            display: block;
            margin: 0;
        }
        #thumbnailPreview .remove-photo {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 1;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #dee2e6;
        }
        #thumbnailPreview .remove-photo i {
            color: #dc3545;
            font-size: 12px;
            line-height: 1;
        }
        .main-image-preview {
            position: relative;
            display: inline-block;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
    </style>
@endsection

@section('content')
<div class="form-container">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 text-primary">Tạo sản phẩm mới</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}" class="text-primary">Sản phẩm</a></li>
                        <li class="breadcrumb-item active">Tạo sản phẩm mới</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <form id="productForm" action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
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
                                        id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
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
                                                id="price" name="price" value="{{ old('price') }}" required min="0">
                                            <span class="input-group-text">VNĐ</span>
                                        </div>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-3" id="discountPriceSection">
                                        <label for="discount_price" class="form-label">Giá khuyến mãi</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control @error('discount_price') is-invalid @enderror"
                                                id="discount_price" name="discount_price" value="{{ old('discount_price') }}" min="0">
                                            <span class="input-group-text">VNĐ</span>
                                        </div>
                                        @error('discount_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">Mô tả</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                id="image_thumnail" name="image_thumnail" required accept="image/*">
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
                        <!-- Form thêm biến thể -->
                        <div id="variantForm" class="border rounded p-4 mb-4" style="background: #f8f9fa;">
                            <div class="row">
                                @foreach($variants as $variant)
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{ $variant->name }} <span class="text-danger">*</span></label>
                                        <select class="form-select variant-select" data-variant-id="{{ $variant->id }}">
                                            <option value="">Chọn {{ strtolower($variant->name) }}</option>
                                            @foreach($variant->values as $value)
                                                <option value="{{ $value->id }}">{{ $value->value }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endforeach
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="variant-sku">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Giá của biến thể <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="variant-price" min="0">
                                        <span class="input-group-text">VNĐ</span>
                                    </div>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="button" class="btn btn-primary w-100" id="add-variant-btn">
                                        <i class="fas fa-plus me-2"></i>Thêm biến thể
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
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary me-2">
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
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    
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
                    preview.style.display = 'block';
                    preview.querySelector('img').src = e.target.result;
                }
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });

        function removeThumbnail() {
            const preview = document.getElementById('thumbnailPreview');
            const input = document.getElementById('image_thumnail');
            preview.style.display = 'none';
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
            const index = Array.from(container.children).indexOf(item);
            
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

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('productForm');
            const variantToggle = document.getElementById('variantToggle');
            const variantSection = document.getElementById('variantSection');
            const hasVariantsInput = document.getElementById('hasVariants');
            const discountPriceSection = document.getElementById('discountPriceSection');
            const priceInput = document.getElementById('price');
            const discountPriceInput = document.getElementById('discount_price');
            let selectedVariants = [];

            // Đảm bảo phần biến thể bị ẩn khi tải trang
            variantSection.classList.remove('show');
            hasVariantsInput.value = '0';

            // Xử lý toggle biến thể
            variantToggle.addEventListener('change', function() {
                variantSection.classList.toggle('show');
                hasVariantsInput.value = this.checked ? '1' : '0';
                
                // Ẩn/hiện phần giá khuyến mãi
                if (this.checked) {
                    discountPriceSection.style.display = 'none';
                    discountPriceInput.value = '';
                    discountPriceInput.required = false;
                } else {
                    discountPriceSection.style.display = 'block';
                }
            });

            // Thêm biến thể
            document.getElementById('add-variant-btn').addEventListener('click', function () {
                const variantSelects = document.querySelectorAll('.variant-select');
                const values = [];
                let isValid = true;

                variantSelects.forEach(select => {
                    const valueId = select.value;
                    if (!valueId) {
                        isValid = false;
                        Swal.fire({
                            title: 'Lỗi!',
                            text: 'Vui lòng chọn đầy đủ các biến thể',
                            icon: 'error',
                            confirmButtonText: 'Đóng'
                        });
                        return;
                    }
                    values.push(valueId);
                });

                if (!isValid) return;

                const sku = document.getElementById('variant-sku').value;
                const price = document.getElementById('variant-price').value;

                if (!sku || !price) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng điền đầy đủ thông tin SKU và giá',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                const variant = {
                    sku: sku,
                    price: price,
                    values: values
                };

                selectedVariants.push(variant);
                displayVariant(variant);

                // Reset form
                document.getElementById('variant-sku').value = '';
                document.getElementById('variant-price').value = '';
                variantSelects.forEach(select => select.value = '');
            });

            // Hiển thị biến thể
            function displayVariant(variant) {
                const variantElement = document.createElement('div');
                variantElement.className = 'variant-item mb-4 border rounded p-3';
                
                const variantId = 'variant_' + Date.now();
                variantElement.dataset.variantId = variantId;

                const variantSelects = document.querySelectorAll('.variant-select');
                const variantDisplay = Array.from(variantSelects).map((select, index) => {
                    const variantName = select.previousElementSibling.textContent.replace(' *', '');
                    const selectedOption = select.querySelector(`option[value="${variant.values[index]}"]`);
                    const variantValue = selectedOption ? selectedOption.textContent : '';
                    return `${variantName}: ${variantValue}`;
                }).join(' - ');

                variantElement.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Biến thể #${selectedVariants.length}</h6>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeVariant('${variantId}')">
                            <i class="bi bi-trash me-2"></i>Xóa
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="form-label">SKU</label>
                            <p class="mb-0">${variant.sku}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Giá</label>
                            <p class="mb-0">${parseInt(variant.price).toLocaleString('vi-VN')} VNĐ</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Biến thể</label>
                            <p class="mb-0">${variantDisplay}</p>
                        </div>
                    </div>
                    <input type="hidden" name="variants[${selectedVariants.length - 1}][sku]" value="${variant.sku}">
                    <input type="hidden" name="variants[${selectedVariants.length - 1}][price]" value="${variant.price}">
                    ${variant.values.map((value, index) => `
                        <input type="hidden" name="variants[${selectedVariants.length - 1}][values][]" value="${value}">
                    `).join('')}
                `;

                document.getElementById('variants-container').appendChild(variantElement);
            }

            // Xóa biến thể
            window.removeVariant = function(variantId) {
                const variantElement = document.querySelector(`[data-variant-id="${variantId}"]`);
                if (variantElement) {
                    const index = Array.from(variantElement.parentElement.children).indexOf(variantElement);
                    selectedVariants.splice(index, 1);
                    variantElement.remove();
                    
                    // Cập nhật lại số thứ tự và name của các input hidden
                    const variantItems = document.querySelectorAll('.variant-item');
                    variantItems.forEach((item, idx) => {
                        item.querySelector('h6').textContent = `Biến thể #${idx + 1}`;
                        const inputs = item.querySelectorAll('input[type="hidden"]');
                        inputs.forEach(input => {
                            const name = input.getAttribute('name');
                            input.setAttribute('name', name.replace(/\[\d+\]/, `[${idx}]`));
                        });
                    });
                }
            };

            // Xử lý submit form
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (variantToggle.checked && selectedVariants.length === 0) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng thêm ít nhất một biến thể cho sản phẩm',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';

                fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        throw new Error(data.message || 'Có lỗi xảy ra khi thêm sản phẩm');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Lỗi!',
                        text: error.message || 'Có lỗi xảy ra khi thêm sản phẩm',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
        });
    </script>
@endsection

<meta name="csrf-token" content="{{ csrf_token() }}">