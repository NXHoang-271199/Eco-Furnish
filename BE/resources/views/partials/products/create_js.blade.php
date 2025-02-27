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
                        }

                        // Reset form biến thể khi toggle
                        if (!this.checked) {
                            selectedVariants = [];
                            const variantsContainer = document.getElementById('variants-container');
                            if (variantsContainer) {
                                variantsContainer.innerHTML = '';
                            }

                            // Kiểm tra validate cho trường giá khi tắt biến thể
                            const priceInput = document.querySelector('[name="price"]');
                            if (priceInput && !priceInput.value.trim()) {
                                const container = priceInput.closest('.input-group').parentElement;
                                priceInput.classList.add('is-invalid');

                                // Xóa feedback cũ nếu có
                                container.querySelectorAll('.invalid-feedback, .valid-feedback').forEach(feedback => {
                                    feedback.remove();
                                });

                                // Thêm thông báo lỗi mới
                                const errorDiv = document.createElement('div');
                                errorDiv.className = 'invalid-feedback d-block';
                                errorDiv.textContent = 'Giá sản phẩm là bắt buộc';
                                container.appendChild(errorDiv);
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

                // Xóa tất cả thông báo lỗi cũ
                document.querySelectorAll('.error-message').forEach(el => el.remove());
                document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

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
                        select.classList.add('is-invalid');
                        const errorDiv = document.createElement('small');
                        errorDiv.className = 'error-message text-danger d-block mt-1';
                        errorDiv.textContent = 'Vui lòng chọn ít nhất một biến thể';
                        select.parentElement.appendChild(errorDiv);
                    });
                    return;
                }

                const variantSkuInput = document.getElementById('variant-sku');
                const variantPriceInput = document.getElementById('variant-price');
                const variantQuantityInput = document.getElementById('variant-quantity');

                if (!variantSkuInput || !variantPriceInput || !variantQuantityInput) {
                    console.error('Không tìm thấy input SKU, giá hoặc số lượng');
                    return;
                }

                const sku = variantSkuInput.value.trim();
                const price = variantPriceInput.value;
                const quantity = variantQuantityInput.value;

                // Validate SKU và giá
                let isValid = true;

                if (!sku) {
                    variantSkuInput.classList.add('is-invalid');
                    const errorDiv = document.createElement('small');
                    errorDiv.className = 'error-message text-danger d-block mt-1';
                    errorDiv.textContent = 'Vui lòng nhập mã SKU';
                    variantSkuInput.parentElement.appendChild(errorDiv);
                    isValid = false;
                } else if (selectedVariants.some(v => v.sku === sku)) {
                    variantSkuInput.classList.add('is-invalid');
                    const errorDiv = document.createElement('small');
                    errorDiv.className = 'error-message text-danger d-block mt-1';
                    errorDiv.textContent = 'Mã SKU này đã tồn tại';
                    variantSkuInput.parentElement.appendChild(errorDiv);
                    isValid = false;
                }

                if (!price) {
                    variantPriceInput.classList.add('is-invalid');
                    const errorDiv = document.createElement('small');
                    errorDiv.className = 'error-message text-danger d-block mt-1';
                    errorDiv.textContent = 'Vui lòng nhập giá';
                    const inputGroup = variantPriceInput.closest('.input-group');
                    inputGroup.parentElement.appendChild(errorDiv);
                    isValid = false;
                } else if (isNaN(price) || parseFloat(price) <= 0) {
                    variantPriceInput.classList.add('is-invalid');
                    const errorDiv = document.createElement('small');
                    errorDiv.className = 'error-message text-danger d-block mt-1';
                    errorDiv.textContent = 'Giá phải là số dương';
                    const inputGroup = variantPriceInput.closest('.input-group');
                    inputGroup.parentElement.appendChild(errorDiv);
                    isValid = false;
                }

                if (!quantity || parseFloat(quantity) < 0) {
                    variantQuantityInput.classList.add('is-invalid');
                    const errorDiv = document.createElement('small');
                    errorDiv.className = 'error-message text-danger d-block mt-1';
                    errorDiv.textContent = 'Số lượng phải là số dương';
                    const inputGroup = variantQuantityInput.closest('.input-group');
                    inputGroup.parentElement.appendChild(errorDiv);
                    isValid = false;
                }

                // Kiểm tra trùng lặp biến thể - chỉ so sánh các giá trị đã chọn
                const selectedValues = values.filter(v => v); // Lọc ra các giá trị đã chọn
                if (selectedValues.length > 0) { // Chỉ kiểm tra nếu có ít nhất 1 giá trị được chọn
                    const isDuplicateVariant = selectedVariants.some(existingVariant => {
                        // Lấy các giá trị không rỗng từ biến thể hiện tại và biến thể đã tồn tại
                        const existingSelectedValues = existingVariant.values.filter(v => v);

                        // Chỉ coi là trùng khi số lượng giá trị đã chọn bằng nhau và tất cả các giá trị đều giống nhau
                        if (existingSelectedValues.length === selectedValues.length) {
                            // Sắp xếp và so sánh từng giá trị
                            const sortedExisting = [...existingSelectedValues].sort();
                            const sortedNew = [...selectedValues].sort();
                            return sortedExisting.every((value, index) => value === sortedNew[index]);
                        }
                        return false;
                    });

                    if (isDuplicateVariant) {
                        variantSelects.forEach(select => {
                            if (select.value) { // Chỉ hiển thị lỗi trên các select đã chọn
                                select.classList.add('is-invalid');
                                const errorDiv = document.createElement('small');
                                errorDiv.className = 'error-message text-danger d-block mt-1';
                                errorDiv.textContent = 'Biến thể này đã tồn tại';
                                select.parentElement.appendChild(errorDiv);
                            }
                        });
                        isValid = false;
                    }
                }

                if (!isValid) return;

                // Thêm biến thể mới
                const variant = { sku, price, quantity, values };
                selectedVariants.push(variant);
                displayVariant(variant);

                // Reset form
                variantSkuInput.value = '';
                variantPriceInput.value = '';
                variantQuantityInput.value = '';
                variantSelects.forEach(select => {
                    select.value = '';
                    select.classList.remove('is-invalid');
                });
            }

            // Xử lý submit form
            async function handleFormSubmit(e) {
                e.preventDefault();

                // Kiểm tra nếu có biến thể nhưng chưa thêm biến thể nào
                const hasVariants = document.getElementById('variantToggle').checked;
                const variantsContainer = document.getElementById('variants-container');

                if (hasVariants && (!variantsContainer || variantsContainer.children.length === 0)) {
                    await Swal.fire({
                        title: 'Lỗi!',
                        text: 'Phải thêm ít nhất một biến thể vào sản phẩm',
                        icon: 'error',
                        confirmButtonText: 'Đồng ý'
                    });

                    // Cuộn đến khu vực biến thể
                    variantSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
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
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: formData
                            });

                            let data;
                            const contentType = response.headers.get('content-type');
                            if (contentType && contentType.includes('application/json')) {
                                data = await response.json();
                            } else {
                                throw new Error('Response không phải là JSON');
                            }

                            // Xóa trạng thái valid cũ
                            document.querySelectorAll('.is-valid').forEach(el => {
                                el.classList.remove('is-valid');
                            });
                            document.querySelectorAll('.valid-feedback, .invalid-feedback').forEach(el => {
                                el.remove();
                            });

                            if (response.ok) {
                                if (data.success) {
                                    await Swal.fire({
                                        title: 'Thành công!',
                                        text: 'Thêm sản phẩm thành công',
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    window.location.href = '/admin/products';
                                }
                            } else {
                                if (response.status === 422) { // Validation error
                                    // Xóa trạng thái valid cũ
                                    document.querySelectorAll('.is-valid').forEach(el => {
                                        el.classList.remove('is-valid');
                                    });
                                    document.querySelectorAll('.valid-feedback, .invalid-feedback').forEach(el => {
                                        el.remove();
                                    });

                                    // Hiển thị thông báo lỗi validation
                                    Object.keys(data.errors).forEach(field => {
                                        const element = document.querySelector(`[name="${field}"]`);
                                        if (element) {
                                            element.classList.add('is-invalid');

                                            // Tìm container chứa feedback messages
                                            let container = element.parentElement;
                                            if (field === 'price') {
                                                container = element.closest('.input-group').parentElement;
                                            }

                                            // Xóa tất cả feedback messages trong container
                                            const feedbacks = container.querySelectorAll('.invalid-feedback, .valid-feedback');
                                            feedbacks.forEach(feedback => feedback.remove());

                                            // Thêm thông báo lỗi mới
                                            const errorDiv = document.createElement('div');
                                            errorDiv.className = 'invalid-feedback d-block';
                                            errorDiv.textContent = data.errors[field][0];
                                            container.appendChild(errorDiv);
                                        }
                                    });

                                    // Thêm trạng thái valid cho các trường bắt buộc cụ thể
                                    document.querySelectorAll('input, select, textarea').forEach(element => {
                                        const name = element.getAttribute('name');
                                        const requiredFields = ['name', 'category_id', 'price', 'image_thumnail'];

                                        if (name && !data.errors[name] && requiredFields.includes(name) && element.value.trim() !== '') {
                                            element.classList.add('is-valid');

                                            // Tìm container chứa feedback messages
                                            let container = element.parentElement;
                                            if (name === 'price') {
                                                container = element.closest('.input-group').parentElement;
                                            }

                                            // Thêm dấu tích xanh
                                            const validDiv = document.createElement('div');
                                            validDiv.className = 'valid-feedback d-block';
                                            validDiv.innerHTML = '<i class="fas fa-check"></i>';
                                            container.appendChild(validDiv);
                                        }
                                    });

                                    // Cuộn đến trường lỗi đầu tiên
                                    const firstError = document.querySelector('.is-invalid');
                                    if (firstError) {
                                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                    }
                                } else {
                                    // Hiển thị thông báo lỗi chung
                                    Swal.fire({
                                        title: 'Lỗi!',
                                        text: data.message || 'Có lỗi xảy ra khi thêm sản phẩm. Vui lòng thử lại.',
                                        icon: 'error',
                                        confirmButtonText: 'Đồng ý'
                                    });
                                }
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            // Hiển thị thông báo lỗi
                            Swal.fire({
                                title: 'Lỗi!',
                                text: 'Có lỗi xảy ra khi thêm sản phẩm. Vui lòng thử lại.',
                                icon: 'error',
                                confirmButtonText: 'Đồng ý'
                            });
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

            // Thêm xử lý input event cho tất cả các trường
            document.querySelectorAll('input, select, textarea').forEach(element => {
                element.addEventListener('input', function() {
                    const fieldName = this.getAttribute('name');

                    // Xóa trạng thái và thông báo cũ
                    this.classList.remove('is-invalid', 'is-valid');

                    // Tìm container chứa feedback messages
                    let container = this.parentElement;
                    if (fieldName === 'price') {
                        container = this.closest('.input-group').parentElement;
                    }

                    // Xóa tất cả feedback messages trong container
                    const feedbacks = container.querySelectorAll('.invalid-feedback, .valid-feedback');
                    feedbacks.forEach(feedback => feedback.remove());

                    // Danh sách các trường bắt buộc cần hiển thị dấu tích
                    const requiredFields = ['name', 'category_id', 'price', 'image_thumnail'];

                    // Chỉ hiển thị dấu tích xanh cho các trường bắt buộc
                    if (this.value.trim() !== '' && requiredFields.includes(fieldName)) {
                        // Thêm trạng thái valid và dấu tích
                        this.classList.add('is-valid');
                        const validDiv = document.createElement('div');
                        validDiv.className = 'valid-feedback d-block';
                        validDiv.innerHTML = '<i class="fas fa-check"></i>';
                        container.appendChild(validDiv);
                    }
                });
            });

            // Thêm event listener cho các select biến thể
            document.querySelectorAll('.variant-select').forEach(select => {
                select.addEventListener('change', function() {
                    // Xóa thông báo lỗi khi thay đổi giá trị
                    this.classList.remove('is-invalid');
                    const errorMessage = this.parentElement.querySelector('.error-message');
                    if (errorMessage) {
                        errorMessage.remove();
                    }
                });
            });

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

                variantElement.innerHTML =
                    '<div class="variant-preview p-3 bg-light border rounded mb-3">' +
                        '<div class="d-flex justify-content-between align-items-center mb-3">' +
                            '<h6 class="mb-0 text-primary">Biến thể #' + selectedVariants.length + '</h6>' +
                            '<button type="button" class="btn btn-soft-danger btn-sm" onclick="removeVariant(\'' + variantId + '\')">' +
                                '<i class="fas fa-trash me-1"></i>Xóa' +
                            '</button>' +
                        '</div>' +
                        '<div class="row g-3">' +
                            '<div class="col-md-2">' +
                                '<div class="variant-info">' +
                                    '<label class="form-label text-muted mb-1">SKU</label>' +
                                    '<p class="mb-0 fw-medium">' + variant.sku + '</p>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-2">' +
                                '<div class="variant-info">' +
                                    '<label class="form-label text-muted mb-1">Giá</label>' +
                                    '<p class="mb-0 fw-medium">' + parseInt(variant.price).toLocaleString('vi-VN') + ' VNĐ</p>' +
                                '</div>' +
                            '</div>' +
                            '<div class="col-md-2">' +
                                '<div class="variant-info">' +
                                    '<label class="form-label text-muted mb-1">Số lượng</label>' +
                                    '<p class="mb-0 fw-medium">' + variant.quantity + '</p>' +
                                '</div>' +
                            '</div>' +
                            (variantDisplay ?
                                '<div class="col-md-6">' +
                                    '<div class="variant-info">' +
                                        '<label class="form-label text-muted mb-1">Thông tin biến thể</label>' +
                                        '<p class="mb-0 fw-medium">' + variantDisplay + '</p>' +
                                    '</div>' +
                                '</div>' : ''
                            ) +
                        '</div>' +
                    '</div>' +
                    '<input type="hidden" name="variants[' + (selectedVariants.length - 1) + '][sku]" value="' + variant.sku + '">' +
                    '<input type="hidden" name="variants[' + (selectedVariants.length - 1) + '][price]" value="' + variant.price + '">' +
                    '<input type="hidden" name="variants[' + (selectedVariants.length - 1) + '][quantity]" value="' + variant.quantity + '">' +
                    variant.values.map((value, index) =>
                        value ? '<input type="hidden" name="variants[' + (selectedVariants.length - 1) + '][values][]" value="' + value + '">' : ''
                    ).join('');

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
