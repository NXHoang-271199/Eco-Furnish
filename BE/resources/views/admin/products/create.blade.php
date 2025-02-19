@extends('layouts.admin')

@section('title', 'Tạo sản phẩm mới')

@section('css')
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

<div class="container">
    <div class="row">
        <div class="col-12 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal"
                        data-bs-target="#simpleProductModal">
                        <i class="fas fa-plus"></i> Thêm sản phẩm thường
                    </button>
                    <button type="button" class="btn btn-success me-2" data-bs-toggle="modal"
                        data-bs-target="#variantProductModal">
                        <i class="fas fa-plus"></i> Thêm sản phẩm có biến thể
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal sản phẩm thường -->
<div class="modal fade" id="simpleProductModal" tabindex="-1" aria-labelledby="simpleProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="simpleProductModalLabel">Thêm sản phẩm thường</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data"
                id="simpleProductForm">
                @csrf
                <input type="hidden" name="has_variants" value="0">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="name">Tên sản phẩm <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                    name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="category_id">Danh mục <span class="text-danger">*</span></label>
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

                            <div class="form-group mb-3">
                                <label for="price">Giá <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('price') is-invalid @enderror"
                                    id="price" name="price" value="{{ old('price') }}" required min="0">
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="discount_price">Giá khuyến mãi</label>
                                <input type="number" class="form-control @error('discount_price') is-invalid @enderror"
                                    id="discount_price" name="discount_price" value="{{ old('discount_price') }}" min="0">
                                @error('discount_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="description">Mô tả</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                    id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="image_thumnail">Ảnh đại diện <span class="text-danger">*</span></label>
                                <input type="file" class="form-control @error('image_thumnail') is-invalid @enderror"
                                    id="image_thumnail" name="image_thumnail" required accept="image/*">
                                @error('image_thumnail')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="gallery">Thư viện ảnh</label>
                                <input type="file" class="form-control @error('gallery.*') is-invalid @enderror"
                                    id="gallery" name="gallery[]" multiple accept="image/*">
                                @error('gallery.*')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal thêm sản phẩm có biến thể -->
<div class="modal fade" id="variantProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm sản phẩm có biến thể</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="variantProductForm" action="{{ route('admin.products.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="has_variants" value="1">
                    <div id="variants-data"></div>

                    <!-- Thông tin cơ bản -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0 text-white"><i class="bi bi-info-circle me-2"></i>Thông tin cơ bản</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select" name="category_id" required>
                                        <option value="">Chọn danh mục</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ảnh đại diện <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control" name="image_thumnail" accept="image/*"
                                        required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Thư viện ảnh</label>
                                    <input type="file" class="form-control" name="gallery[]" accept="image/*" multiple>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thêm biến thể -->
                    <div class="card mb-4">
                        <div
                            class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 text-white"><i class="bi bi-list-check me-2"></i>Biến thể sản phẩm</h6>
                        </div>
                        <div class="card-body">
                            <!-- Form thêm biến thể -->
                            <div id="variantForm" class="border rounded p-3 mb-4">
                                <div class="row">
                                    @foreach($variants as $variant)
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ $variant->name }} <span
                                                    class="text-danger">*</span></label>
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
                                        <label class="form-label">Giá <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" id="variant-price" min="0">
                                            <span class="input-group-text">VNĐ</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="button" class="btn btn-primary" id="add-variant-btn">
                                        <i class="bi bi-plus-circle me-2"></i>Thêm biến thể
                                    </button>
                                </div>
                            </div>

                            <!-- Danh sách biến thể đã thêm -->
                            <div id="variants-container"></div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu sản phẩm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('JS')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý form sản phẩm thường
            const simpleForm = document.getElementById('simpleProductForm');
            simpleForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                if (!this.checkValidity()) {
                    this.reportValidity();
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
                        // Đóng modal
                        const modal = document.getElementById('simpleProductModal');
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        bsModal.hide();
                        
                        // Chuyển hướng
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

            // Xử lý form sản phẩm có biến thể
            const variantForm = document.getElementById('variantProductForm');
            const variantsContainer = document.getElementById('variants-container');
            const variantsDataContainer = document.getElementById('variants-data');
            let selectedVariants = [];

            // Lấy CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('CSRF Token:', csrfToken ? 'Found' : 'Not found');

            // Thêm biến thể
            document.getElementById('add-variant-btn').addEventListener('click', function () {
                const variantSelects = document.querySelectorAll('.variant-select');
                const values = [];
                let isValid = true;

                // Kiểm tra xem đã chọn đủ biến thể chưa
                variantSelects.forEach(select => {
                    const valueId = select.value;
                    if (!valueId) {
                        isValid = false;
                        alert('Vui lòng chọn đầy đủ các biến thể');
                        return;
                    }
                    values.push(valueId); // Không cần chuyển đổi sang số nguyên
                });

                if (!isValid) return;

                const sku = document.getElementById('variant-sku').value;
                const price = document.getElementById('variant-price').value;

                if (!sku || !price) {
                    alert('Vui lòng điền đầy đủ thông tin SKU, giá');
                    return;
                }

                // Thêm biến thể vào danh sách
                const variant = {
                    sku: sku,
                    price: price,
                    values: values
                };

                console.log('Adding variant:', variant);
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
                
                // Tạo ID duy nhất cho biến thể
                const variantId = 'variant_' + Date.now();
                variantElement.dataset.variantId = variantId;

                // Tạo chuỗi hiển thị các biến thể
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
                `;
                variantsContainer.appendChild(variantElement);
                updateVariantNumbers();
            }

            // Xóa biến thể
            window.removeVariant = function (variantId) {
                const variantElement = document.querySelector(`[data-variant-id="${variantId}"]`);
                if (variantElement) {
                    const index = Array.from(variantsContainer.children).indexOf(variantElement);
                    selectedVariants.splice(index, 1);
                    variantElement.remove();
                    updateVariantNumbers();
                }
            };

            // Cập nhật số thứ tự các biến thể
            function updateVariantNumbers() {
                const variants = variantsContainer.querySelectorAll('.variant-item');
                variants.forEach((variant, index) => {
                    const title = variant.querySelector('h6');
                    if (title) {
                        title.textContent = `Biến thể #${index + 1}`;
                    }
                });
            }

            // Xử lý submit form biến thể
            variantForm.addEventListener('submit', function(e) {
                e.preventDefault();

                if (selectedVariants.length === 0) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng thêm ít nhất một biến thể cho sản phẩm',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                const basicFields = this.querySelectorAll('input[required], select[required], textarea[required]');
                let hasEmptyBasicField = false;

                basicFields.forEach(field => {
                    if (!field.value) {
                        field.classList.add('is-invalid');
                        hasEmptyBasicField = true;
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (hasEmptyBasicField) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng điền đầy đủ thông tin cơ bản của sản phẩm',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                const formData = new FormData(this);
                selectedVariants.forEach((variant, index) => {
                    formData.append(`variants[${index}][sku]`, variant.sku);
                    formData.append(`variants[${index}][price]`, variant.price);
                    variant.values.forEach(valueId => {
                        formData.append(`variants[${index}][values][]`, valueId);
                    });
                });

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
                        // Đóng modal
                        const modal = document.getElementById('variantProductModal');
                        const bsModal = bootstrap.Modal.getInstance(modal);
                        bsModal.hide();
                        
                        // Chuyển hướng
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

            // Reset form khi đóng modal
            const modal = document.getElementById('variantProductModal');
            modal.addEventListener('hidden.bs.modal', function () {
                const form = this.querySelector('form');
                if (form) {
                    form.reset();
                }
                selectedVariants = [];
                variantsContainer.innerHTML = '';
            });

            const variantSelects = document.querySelectorAll('.variant-select');
            variantSelects.forEach(select => {
                select.addEventListener('change', function () {
                    console.log('Selected variant:', {
                        variantId: this.dataset.variantId,
                        valueId: this.value
                    });
                });
            });
        });
    </script>
@endsection

<meta name="csrf-token" content="{{ csrf_token() }}">