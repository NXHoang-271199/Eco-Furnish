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
            const nameInput = document.getElementById('name');
            const imageInput = document.getElementById('image_thumnail');
            const categorySelect = document.getElementById('category_id');
            let selectedVariants = [];
            
            // Khởi tạo các biến và event listeners
            function initializeForm() {
                if (!form) {
                    console.error('Không tìm thấy form');
                    return;
                }
            
            // Đảm bảo phần biến thể bị ẩn khi tải trang
                if (variantSection) {
            variantSection.classList.remove('show');
                }
                if (hasVariantsInput) {
            hasVariantsInput.value = '0';
                }

            // Xử lý toggle biến thể
                if (variantToggle) {
            variantToggle.addEventListener('change', function() {
                        if (variantSection) {
                variantSection.classList.toggle('show');
                        }
                        if (hasVariantsInput) {
                hasVariantsInput.value = this.checked ? '1' : '0';
                            
                            // Thêm validate cho các trường bắt buộc khi chuyển sang chế độ biến thể
                            const requiredFields = [nameInput, categorySelect, imageInput, priceInput];
                            requiredFields.forEach(field => {
                                // Xóa validate cũ nếu có
                                field.classList.remove('is-invalid');
                                const oldErrorDiv = field.nextElementSibling;
                                if (oldErrorDiv && oldErrorDiv.classList.contains('invalid-feedback')) {
                                    oldErrorDiv.remove();
                                }

                                // Kiểm tra giá trị hiện tại
                                const value = field.value.trim();
                                const fieldLabel = field.previousElementSibling ? field.previousElementSibling.textContent.replace(' *', '') : 'Trường này';
                                
                                if (!value) {
                                    field.classList.add('is-invalid');
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'invalid-feedback';
                                    errorDiv.textContent = fieldLabel + ' không được để trống';
                                    field.after(errorDiv);
                                }
                            });

                            // Thêm event listener để tắt validate khi các trường được điền
                            requiredFields.forEach(field => {
                                if (field) {
                                    // Xóa event listener cũ nếu có
                                    field.removeEventListener('input', handleInput);
                                    field.removeEventListener('change', handleChange);

                                    // Thêm event listener mới
                                    function handleInput() {
                                        if (this.value.trim()) {
                                            this.classList.remove('is-invalid');
                                            const errorDiv = this.nextElementSibling;
                                            if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                                                errorDiv.remove();
                                            }
                                        }
                                    }

                                    function handleChange() {
                                        if (this.value) {
                                            this.classList.remove('is-invalid');
                                            const errorDiv = this.nextElementSibling;
                                            if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                                                errorDiv.remove();
                                            }
                                        }
                                    }

                                    field.addEventListener('input', handleInput);
                                    
                                    // Đặc biệt xử lý cho select
                                    if (field.tagName === 'SELECT') {
                                        field.addEventListener('change', handleChange);
                                    }
                                }
                            });
                        }

                        // Reset form biến thể khi toggle
                        if (!this.checked) {
                            selectedVariants = [];
                            const variantsContainer = document.getElementById('variants-container');
                            if (variantsContainer) {
                                variantsContainer.innerHTML = '';
                            }
                        }
                    });
                }

            // Thêm biến thể
                const addVariantBtn = document.getElementById('add-variant-btn');
                if (addVariantBtn) {
                    addVariantBtn.addEventListener('click', handleAddVariant);
                }

                // Xử lý submit form
                form.addEventListener('submit', handleFormSubmit);
            }

            // Xử lý thêm biến thể
            function handleAddVariant() {
                const variantSelects = document.querySelectorAll('.variant-select');
                const values = [];
                let hasValue = false;

                // Kiểm tra xem có ít nhất một biến thể được chọn
                variantSelects.forEach(select => {
                    const valueId = select.value;
                    values.push(valueId);
                    if (valueId) {
                        hasValue = true;
                    }
                });

                if (!hasValue) {
                    variantSelects.forEach(select => {
                        showError(select, 'Vui lòng chọn ít nhất một biến thể');
                    });
                    return;
                }

                const skuInput = document.getElementById('variant-sku');
                const variantPriceInput = document.getElementById('variant-price');
                
                if (!skuInput || !variantPriceInput) {
                    console.error('Không tìm thấy input SKU hoặc giá');
                    return;
                }

                const sku = skuInput.value.trim();
                const price = variantPriceInput.value;

                // Validate SKU và giá
                let isValid = true;

                if (!sku) {
                    showError(skuInput, 'Vui lòng nhập mã SKU');
                    isValid = false;
                } else if (selectedVariants.some(v => v.sku === sku)) {
                    showError(skuInput, 'Mã SKU này đã tồn tại');
                    isValid = false;
                }

                if (!price) {
                    showError(variantPriceInput, 'Vui lòng nhập giá');
                    isValid = false;
                } else if (isNaN(price) || parseFloat(price) <= 0) {
                    showError(variantPriceInput, 'Giá phải là số dương');
                    isValid = false;
                }

                // Kiểm tra trùng lặp biến thể
                const isDuplicateVariant = selectedVariants.some(existingVariant => {
                    const existingValues = existingVariant.values.filter(v => v).sort().join(',');
                    const newValues = values.filter(v => v).sort().join(',');
                    return existingValues === newValues;
                });

                if (isDuplicateVariant) {
                    variantSelects.forEach(select => {
                        showError(select, 'Biến thể này đã tồn tại');
                    });
                    isValid = false;
                }

                if (!isValid) return;

                // Thêm biến thể mới
                const variant = { sku, price, values };
                selectedVariants.push(variant);
                displayVariant(variant);

                // Reset form
                skuInput.value = '';
                variantPriceInput.value = '';
                variantSelects.forEach(select => {
                    select.value = '';
                    clearError(select);
                });
                clearError(skuInput);
                clearError(variantPriceInput);
            }

            // Xử lý submit form
            async function handleFormSubmit(e) {
                e.preventDefault();
                
                // Reset form state
                clearAllErrors();
                
                let isValid = true;
                const errors = [];

                // Validate tên sản phẩm
                if (!nameInput || !nameInput.value.trim()) {
                    errors.push({
                        element: nameInput,
                        message: 'Vui lòng nhập tên sản phẩm'
                    });
                    isValid = false;
                }

                // Validate danh mục
                if (!categorySelect || !categorySelect.value) {
                    errors.push({
                        element: categorySelect,
                        message: 'Vui lòng chọn danh mục sản phẩm'
                    });
                    isValid = false;
                }

                // Validate ảnh đại diện
                if (!imageInput || !imageInput.files || !imageInput.files[0]) {
                    errors.push({
                        element: imageInput,
                        message: 'Vui lòng chọn ảnh đại diện'
                    });
                    isValid = false;
                }

                // Validate dựa vào chế độ biến thể
                if (variantToggle && variantToggle.checked) {
                    if (selectedVariants.length === 0) {
                        await Swal.fire({
                            title: 'Lỗi!',
                            text: 'Vui lòng thêm ít nhất một biến thể',
                            icon: 'error',
                            confirmButtonText: 'Đồng ý'
                        });
                        isValid = false;
                    }
                } else {
                    // Validate giá nếu không có biến thể
                    if (!priceInput || !priceInput.value) {
                        errors.push({
                            element: priceInput,
                            message: 'Vui lòng nhập giá sản phẩm'
                        });
                        isValid = false;
                    }
                }

                if (!isValid) {
                    showAllErrors(errors);
                    return false;
                }

                // Submit form
                try {
                    const formData = new FormData(form);
                    const submitBtn = form.querySelector('button[type="submit"]');
                    
                    if (submitBtn) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';
                        
                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                body: formData
                            });

                            const data = await response.json();

                            if (data.success) {
                                await Swal.fire({
                                    title: 'Thành công!',
                                    text: 'Thêm sản phẩm thành công',
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500
                                });
                                window.location.href = '/admin/products';
                            } else {
                                showServerErrors(data.errors || {});
                            }
                        } finally {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Có lỗi xảy ra khi thêm sản phẩm. Vui lòng thử lại.',
                        icon: 'error',
                        confirmButtonText: 'Đồng ý'
                    });
                }
            }

            // Hiển thị lỗi từ server
            function showServerErrors(errors) {
                const errorMessages = [];
                Object.keys(errors).forEach(key => {
                    const input = document.querySelector(`[name="${key}"]`);
                    if (input) {
                        errorMessages.push({
                            element: input,
                            message: errors[key][0]
                        });
                    }
                });
                showAllErrors(errorMessages);
            }

            // Xóa tất cả thông báo lỗi
            function clearAllErrors() {
                const allErrors = document.querySelectorAll('.invalid-feedback, .alert-danger');
                allErrors.forEach(error => error.remove());
                
                const allInvalidInputs = document.querySelectorAll('.is-invalid');
                allInvalidInputs.forEach(input => input.classList.remove('is-invalid'));
            }

            // Hiển thị lỗi cho một trường
            function showError(element, message) {
                element.classList.add('is-invalid');
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback d-block';
                errorDiv.textContent = message;
                
                const parent = element.parentElement;
                const existingError = parent.querySelector('.invalid-feedback');
                if (existingError) {
                    existingError.remove();
                }
                
                parent.appendChild(errorDiv);
            }

            // Xóa thông báo lỗi
            function clearError(element) {
                element.classList.remove('is-invalid');
                const parent = element.parentElement;
                const error = parent.querySelector('.invalid-feedback');
                if (error) {
                    error.remove();
                }
            }

            // Hiển thị tất cả lỗi
            function showAllErrors(errors) {
                if (!errors || errors.length === 0) return;
                
                errors.forEach(error => {
                    if (error.element && error.message) {
                        showError(error.element, error.message);
                    }
                });

                // Cuộn đến lỗi đầu tiên
                if (errors[0].element) {
                    errors[0].element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            // Hiển thị biến thể
            function displayVariant(variant) {
                const variantElement = document.createElement('div');
                variantElement.className = 'variant-item mb-4 border rounded p-3';
                
                const variantId = 'variant_' + Date.now();
                variantElement.dataset.variantId = variantId;

                // Lấy thông tin tất cả các select biến thể
                const variantSelects = document.querySelectorAll('.variant-select');
                const variantDetails = [];

                // Lặp qua từng select để lấy thông tin biến thể được chọn
                variantSelects.forEach((select, index) => {
                    const valueId = variant.values[index];
                    if (valueId) {
                        const variantName = select.previousElementSibling.textContent.replace(' *', '');
                        const selectedOption = select.querySelector(`option[value="${valueId}"]`);
                        if (selectedOption) {
                            variantDetails.push({
                                name: variantName,
                                value: selectedOption.textContent
                            });
                        }
                    }
                });

                // Tạo chuỗi hiển thị từ các biến thể đã chọn
                const variantDisplay = variantDetails
                    .map(v => `${v.name}: ${v.value}`)
                    .join(' - ');

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
                            ${variantDisplay ? `
                                <label class="form-label">Biến thể</label>
                                <p class="mb-0">${variantDisplay}</p>
                            ` : ''}
                        </div>
                    </div>
                    <input type="hidden" name="variants[${selectedVariants.length - 1}][sku]" value="${variant.sku}">
                    <input type="hidden" name="variants[${selectedVariants.length - 1}][price]" value="${variant.price}">
                    ${variant.values.map((value, index) => 
                        value ? `<input type="hidden" name="variants[${selectedVariants.length - 1}][values][]" value="${value}">` : ''
                    ).join('')}
                `;

                const variantsContainer = document.getElementById('variants-container');
                if (variantsContainer) {
                    variantsContainer.appendChild(variantElement);
                }
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

            // Khởi tạo form
            initializeForm();
        });
    </script>