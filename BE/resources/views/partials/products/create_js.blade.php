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

                // Kiểm tra trùng lặp biến thể
                const isDuplicate = selectedVariants.some(existingVariant => {
                    const existingValues = existingVariant.values.sort().join(',');
                    const newValues = values.sort().join(',');
                    return existingValues === newValues;
                });

                if (isDuplicate) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Biến thể này đã tồn tại',
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
                        Swal.fire({
                            title: 'Thành công!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = data.redirect;
                            }
                        });
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