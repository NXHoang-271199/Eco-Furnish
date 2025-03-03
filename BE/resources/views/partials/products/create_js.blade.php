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
            const variantTypeSelect = document.getElementById('variantTypeSelect');
            const addVariantTypeBtn = document.getElementById('addVariantTypeBtn');
            const selectedVariantTypes = document.getElementById('selectedVariantTypes');
            const variantForm = document.getElementById('variantForm');
            const discountPriceSection = document.getElementById('discountPriceSection');
            const priceInput = document.getElementById('price');
            const discountPriceInput = document.getElementById('discount_price');
            const nameInput = document.getElementById('name');
            const imageInput = document.getElementById('image_thumnail');
            const categorySelect = document.getElementById('category_id');
            let selectedVariants = [];
            let selectedTypes = new Set();

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
                            resetVariantForm();
                        }
                    });
                }

                // Xử lý thêm loại biến thể
                if (addVariantTypeBtn) {
                    addVariantTypeBtn.addEventListener('click', handleAddVariantType);
                }

                // Xử lý thêm biến thể
                const addVariantBtn = document.getElementById('add-variant-btn');
                if (addVariantBtn) {
                    addVariantBtn.addEventListener('click', handleAddVariant);
                }

                // Xử lý tạo biến thể tự động
                const generateVariantsBtn = document.getElementById('generate-variants-btn');
                if (generateVariantsBtn) {
                    generateVariantsBtn.addEventListener('click', handleGenerateVariants);
                }

                // Xử lý submit form
                form.addEventListener('submit', handleFormSubmit);
            }

            // Xử lý submit form
            function handleFormSubmit(e) {
                e.preventDefault();

                // Reset validation states
                clearAllValidation();

                // Validate các trường bắt buộc
                let isValid = true;
                let firstErrorElement = null;

                // Validate tên sản phẩm
                if (!nameInput.value.trim()) {
                    showError(nameInput, 'Tên sản phẩm là bắt buộc');
                    isValid = false;
                    if (!firstErrorElement) firstErrorElement = nameInput;
                } else {
                    showSuccess(nameInput);
                }

                // Validate danh mục
                if (!categorySelect.value) {
                    showError(categorySelect, 'Vui lòng chọn danh mục');
                    isValid = false;
                    if (!firstErrorElement) firstErrorElement = categorySelect;
                } else {
                    showSuccess(categorySelect);
                }

                // Validate ảnh đại diện
                if (!imageInput.files[0]) {
                    showError(imageInput, 'Ảnh đại diện là bắt buộc');
                    isValid = false;
                    if (!firstErrorElement) firstErrorElement = imageInput;
                } else {
                    showSuccess(imageInput);
                }

                // Validate giá gốc
                if (!priceInput.value || priceInput.value <= 0) {
                    showError(priceInput, 'Giá gốc phải lớn hơn 0');
                    isValid = false;
                    if (!firstErrorElement) firstErrorElement = priceInput;
                } else {
                    showSuccess(priceInput);
                }

                // Validate giá khuyến mãi nếu có
                if (discountPriceInput.value) {
                    if (parseInt(discountPriceInput.value) >= parseInt(priceInput.value)) {
                        showError(discountPriceInput, 'Giá khuyến mãi phải nhỏ hơn giá gốc');
                        isValid = false;
                        if (!firstErrorElement) firstErrorElement = discountPriceInput;
                    } else {
                        showSuccess(discountPriceInput);
                    }
                }

                // Validate biến thể nếu được bật
                if (variantToggle.checked) {
                    // Kiểm tra xem có biến thể nào được thêm chưa
                    if (selectedTypes.size === 0) {
                        showError(variantTypeSelect, 'Vui lòng thêm ít nhất một thuộc tính biến thể');
                        isValid = false;
                        if (!firstErrorElement) firstErrorElement = variantTypeSelect;
                    } else if (selectedVariants.length === 0) {
                        Swal.fire({
                            title: 'Lỗi!',
                            text: 'Vui lòng tạo ít nhất một biến thể cho sản phẩm',
                            icon: 'error',
                            confirmButtonText: 'Đóng'
                        });
                        isValid = false;
                        return;
                    } else {
                        // Kiểm tra xem tất cả các biến thể đã được nhập đầy đủ thông tin chưa
                        let hasEmptyVariant = false;
                        let emptyVariantIndex = -1;
                        let missingFields = [];

                        for (let i = 0; i < selectedVariants.length; i++) {
                            const variant = selectedVariants[i];
                            missingFields = [];

                            if (!variant.sku || variant.sku.trim() === '') {
                                missingFields.push('SKU');
                            }

                            if (!variant.price || isNaN(parseFloat(variant.price)) || parseFloat(variant.price) <= 0) {
                                missingFields.push('Giá');
                            }

                            if (variant.quantity === undefined || variant.quantity === '' || isNaN(parseInt(variant.quantity))) {
                                missingFields.push('Số lượng');
                            }

                            if (!variant.values || Object.keys(variant.values).length === 0) {
                                missingFields.push('Giá trị thuộc tính');
                            }

                            if (missingFields.length > 0) {
                                hasEmptyVariant = true;
                                emptyVariantIndex = i;
                                break;
                            }
                        }

                        if (hasEmptyVariant) {
                            Swal.fire({
                                title: 'Lỗi!',
                                html: `Biến thể #${emptyVariantIndex + 1} thiếu thông tin: <strong>${missingFields.join(', ')}</strong>.<br>Vui lòng nhấp vào biến thể để nhập đầy đủ thông tin.`,
                                icon: 'error',
                                confirmButtonText: 'Đóng'
                            });
                            isValid = false;
                            return;
                        }

                        // Kiểm tra trùng lặp SKU
                        const skus = selectedVariants.map(v => v.sku);
                        const uniqueSkus = [...new Set(skus)];
                        if (skus.length !== uniqueSkus.length) {
                            Swal.fire({
                                title: 'Lỗi!',
                                text: 'Có SKU bị trùng lặp giữa các biến thể. Vui lòng kiểm tra lại.',
                                icon: 'error',
                                confirmButtonText: 'Đóng'
                            });
                            isValid = false;
                            return;
                        }
                    }
                }

                if (!isValid) {
                    // Cuộn đến phần tử lỗi đầu tiên
                    if (firstErrorElement) {
                        firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }

                // Disable submit button
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';

                // Submit form using AJAX
                const formData = new FormData(form);

                // Đảm bảo dữ liệu biến thể được gửi đúng cách
                if (variantToggle.checked && selectedVariants.length > 0) {
                    // Xóa tất cả các input hidden biến thể cũ (nếu có)
                    const oldVariantInputs = form.querySelectorAll('input[name^="variants["]');
                    oldVariantInputs.forEach(input => input.remove());

                    // Thêm dữ liệu biến thể vào formData
                    selectedVariants.forEach((variant, index) => {
                        // Kiểm tra dữ liệu biến thể trước khi thêm vào formData
                        if (!variant.sku || !variant.price || variant.quantity === undefined) {
                            console.error(`Biến thể #${index + 1} thiếu thông tin:`, variant);
                            return;
                        }

                        formData.append(`variants[${index}][sku]`, variant.sku);
                        formData.append(`variants[${index}][price]`, variant.price);
                        formData.append(`variants[${index}][quantity]`, variant.quantity);

                        // Thêm các giá trị thuộc tính
                        if (variant.values && Object.keys(variant.values).length > 0) {
                            Object.entries(variant.values).forEach(([variantId, valueId]) => {
                                formData.append(`variants[${index}][values][${variantId}]`, valueId);
                            });
                        } else {
                            console.error(`Biến thể #${index + 1} không có giá trị thuộc tính:`, variant);
                        }
                    });
                } else if (variantToggle.checked) {
                    // Nếu toggle biến thể được bật nhưng không có biến thể nào, gửi một mảng rỗng
                    formData.append('variants', JSON.stringify([]));
                } else {
                    // Nếu toggle biến thể tắt, không gửi dữ liệu biến thể
                    formData.delete('variants');
                    formData.append('has_variants', '0');
                }

                // Log dữ liệu gửi đi để debug
                console.log('Dữ liệu gửi đi:');
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    // Kiểm tra content type của response
                    const contentType = response.headers.get('content-type');

                    // Kiểm tra status code trước
                    if (!response.ok) {
                        // Nếu response không ok, thử lấy thông tin lỗi từ response
                        if (contentType && contentType.includes('application/json')) {
                            return response.json().then(errorData => {
                                throw new Error(errorData.message || `Lỗi ${response.status}: ${response.statusText}`);
                            });
                        } else {
                            throw new Error(`Lỗi ${response.status}: ${response.statusText}`);
                        }
                    }

                    // Nếu response ok, kiểm tra content type
                    if (contentType && contentType.includes('application/json')) {
                        return response.json().then(data => ({
                            status: response.status,
                            data: data
                        }));
                    } else {
                        // Nếu response không phải JSON, throw error
                        throw new Error('Máy chủ không trả về định dạng JSON hợp lệ');
                    }
                })
                .then(({status, data}) => {
                    if (status === 200 || status === 201) {
                        if (data.success) {
                            // Hiển thị thông báo thành công
                            Swal.fire({
                                title: 'Thành công!',
                                text: data.message,
                                icon: 'success',
                                showCancelButton: false,
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed && data.redirect) {
                                    window.location.href = data.redirect;
                                }
                            });
                        } else {
                            // Hiển thị thông báo lỗi từ server
                            Swal.fire({
                                title: 'Lỗi!',
                                text: data.message || 'Có lỗi xảy ra khi thêm sản phẩm',
                                icon: 'error',
                                confirmButtonText: 'Đóng'
                            });
                        }
                    } else if (status === 422) {
                        // Xử lý lỗi validation
                        const errors = data.errors || {};
                        console.error('Lỗi validation:', errors);

                        // Hiển thị tất cả các lỗi
                        let errorMessages = [];
                        Object.keys(errors).forEach(field => {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                showError(input, errors[field][0]);
                                if (!firstErrorElement) {
                                    firstErrorElement = input;
                                }
                            }
                            errorMessages.push(errors[field][0]);
                        });

                        if (firstErrorElement) {
                            firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }

                        Swal.fire({
                            title: 'Lỗi!',
                            html: `
                                <p>Vui lòng kiểm tra lại thông tin nhập vào:</p>
                                <ul class="text-left">
                                    ${errorMessages.map(msg => `<li>${msg}</li>`).join('')}
                                </ul>
                            `,
                            icon: 'error',
                            confirmButtonText: 'Đóng'
                        });
                    } else {
                        throw new Error('Có lỗi xảy ra khi thêm sản phẩm');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Hiển thị thông báo lỗi chi tiết hơn
                    let errorMessage = error.message || 'Có lỗi xảy ra khi thêm sản phẩm';

                    // Kiểm tra nếu có response từ server
                    if (error.response) {
                        try {
                            // Thử parse response JSON
                            const errorData = error.response.json();
                            if (errorData && errorData.message) {
                                errorMessage = errorData.message;
                            }
                        } catch (e) {
                            console.error('Không thể parse response JSON:', e);
                        }
                    }

                    Swal.fire({
                        title: 'Lỗi!',
                        text: errorMessage,
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });

                    // Log thêm thông tin để debug
                    console.log('Form data được gửi:');
                    for (let pair of formData.entries()) {
                        console.log(pair[0] + ': ' + pair[1]);
                    }
                })
                .finally(() => {
                    // Restore submit button state
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            }

            // Hàm xóa tất cả trạng thái validation
            function clearAllValidation() {
                const elements = form.querySelectorAll('.is-invalid, .is-valid');
                elements.forEach(element => {
                    element.classList.remove('is-invalid', 'is-valid');
                    const feedback = element.nextElementSibling;
                    if (feedback && (feedback.classList.contains('invalid-feedback') || feedback.classList.contains('valid-feedback'))) {
                        feedback.remove();
                    }
                });
            }

            // Xử lý thêm loại biến thể
            function handleAddVariantType() {
                const variantId = variantTypeSelect.value;

                if (!variantId) {
                    showError(variantTypeSelect, 'Vui lòng chọn thuộc tính biến thể');
                    return;
                }

                if (selectedTypes.has(variantId)) {
                    return;
                }

                // Xóa thông báo lỗi khi đã chọn thuộc tính
                showSuccess(variantTypeSelect);

                const variantOption = variantTypeSelect.options[variantTypeSelect.selectedIndex];
                const variantName = variantOption.text;
                const variantValues = JSON.parse(variantOption.dataset.values);

                // Hiển thị modal chọn giá trị thuộc tính
                Swal.fire({
                    title: `Chọn giá trị ${variantName}`,
                    html: `
                        <div class="variant-value-selection">
                            <p class="text-muted mb-2"><small>Giữ phím Ctrl (hoặc Command trên Mac) để chọn nhiều giá trị</small></p>
                            <select id="variant-value-selection" class="form-select" multiple size="${Math.min(variantValues.length, 5)}">
                                ${variantValues.map(value => `
                                    <option value="${value.id}">${value.value}</option>
                                `).join('')}
                            </select>
                            <div class="invalid-feedback" style="display: none;">Vui lòng chọn ít nhất một giá trị</div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Thêm',
                    cancelButtonText: 'Hủy',
                    preConfirm: () => {
                        const selectedValues = Array.from(document.getElementById('variant-value-selection').selectedOptions)
                            .map(option => ({id: option.value, value: option.text}));

                        if (selectedValues.length === 0) {
                            document.querySelector('.variant-value-selection .invalid-feedback').style.display = 'block';
                            return false;
                        }

                        return selectedValues;
                    },
                    didOpen: () => {
                        // Thêm sự kiện để hiển thị số lượng giá trị đã chọn
                        const select = document.getElementById('variant-value-selection');
                        select.addEventListener('change', function() {
                            const selectedCount = this.selectedOptions.length;
                            const feedback = document.querySelector('.variant-value-selection .invalid-feedback');

                            if (selectedCount > 0) {
                                feedback.style.display = 'none';
                                // Thêm thông báo số lượng đã chọn
                                let countDisplay = document.querySelector('.selected-count');
                                if (!countDisplay) {
                                    countDisplay = document.createElement('div');
                                    countDisplay.className = 'selected-count mt-2 text-success';
                                    this.parentElement.appendChild(countDisplay);
                                }
                                countDisplay.textContent = `Đã chọn ${selectedCount} giá trị`;
                            } else {
                                // Xóa thông báo số lượng nếu không có giá trị nào được chọn
                                const countDisplay = document.querySelector('.selected-count');
                                if (countDisplay) {
                                    countDisplay.remove();
                                }
                            }
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        const selectedValues = result.value;

                        // Thêm thuộc tính và giá trị đã chọn
                        selectedTypes.add(variantId);

                        // Tạo container cho loại biến thể
                        const variantTypeContainer = document.createElement('div');
                        variantTypeContainer.className = 'selected-variant-type';
                        variantTypeContainer.dataset.variantId = variantId;

                        // Tạo header cho loại biến thể
                        const header = document.createElement('div');
                        header.className = 'variant-header';
                        header.innerHTML = `
                            <h6 class="mb-0">${variantName}</h6>
                            <button type="button" class="btn-remove-variant" onclick="window.removeVariantType('${variantId}')">
                                <i class="fas fa-times"></i>
                            </button>
                        `;

                        // Tạo phần hiển thị giá trị đã chọn
                        const valueDisplay = document.createElement('div');
                        valueDisplay.className = 'variant-values mt-2';

                        // Hiển thị các giá trị đã chọn dưới dạng badge
                        valueDisplay.innerHTML = `
                            <div class="selected-values">
                                ${selectedValues.map(value => `
                                    <span class="badge bg-primary me-1 mb-1">${value.value}</span>
                                `).join('')}
                            </div>
                            <input type="hidden" class="variant-value-data" data-variant-id="${variantId}"
                                value='${JSON.stringify(selectedValues)}'>
                        `;

                        variantTypeContainer.appendChild(header);
                        variantTypeContainer.appendChild(valueDisplay);

                        selectedVariantTypes.appendChild(variantTypeContainer);

                        // Hiển thị nút tạo biến thể tự động
                        document.getElementById('generate-variants-container').style.display = 'block';
                        document.getElementById('generate-variants-btn').style.display = 'inline-block';

                        // Cập nhật danh sách select và xóa giá trị đã chọn
                        updateVariantTypeSelect();
                        variantTypeSelect.value = '';
                    }
                });
            }

            // Thêm sự kiện change cho select thuộc tính biến thể
            variantTypeSelect.addEventListener('change', function() {
                if (this.value) {
                    showSuccess(this);
                } else {
                    showError(this, 'Vui lòng chọn thuộc tính biến thể');
                }
            });

            // Xóa loại biến thể
            window.removeVariantType = function(variantId) {
                const container = document.querySelector(`.selected-variant-type[data-variant-id="${variantId}"]`);
                if (container) {
                    container.remove();
                    selectedTypes.delete(variantId);
                    updateVariantTypeSelect();

                    // Ẩn form nếu không còn loại biến thể nào
                    if (selectedTypes.size === 0) {
                        document.getElementById('generate-variants-btn').style.display = 'none';
                    }
                }
            };

            // Cập nhật select loại biến thể
            function updateVariantTypeSelect() {
                Array.from(variantTypeSelect.options).forEach(option => {
                    if (option.value) {
                        option.disabled = selectedTypes.has(option.value);
                    }
                });
                variantTypeSelect.value = '';
            }

            // Reset form biến thể
            function resetVariantForm() {
                selectedTypes.clear();
                selectedVariants = [];
                if (selectedVariantTypes) {
                    selectedVariantTypes.innerHTML = '';
                }
                if (variantForm) {
                    variantForm.classList.add('d-none');
                }
                const variantsContainer = document.getElementById('variants-container');
                if (variantsContainer) {
                    variantsContainer.innerHTML = '';
                }
                updateVariantTypeSelect();
            }

            // Hàm xóa thông báo lỗi
            function clearValidation(input) {
                input.classList.remove('is-invalid', 'is-valid');

                // Xác định parent element để xóa thông báo
                let parent = input.parentElement;

                // Kiểm tra nếu input nằm trong input-group
                if (parent.classList.contains('input-group')) {
                    // Xóa tất cả các thông báo lỗi và thành công trong input-group parent
                    const errorDivs = parent.parentElement.querySelectorAll('.invalid-feedback, .valid-feedback');
                    errorDivs.forEach(div => div.remove());
                } else {
                    // Xóa tất cả các thông báo lỗi và thành công
                    const feedbackDivs = parent.querySelectorAll('.invalid-feedback, .valid-feedback');
                    feedbackDivs.forEach(div => div.remove());
                }
            }

            // Hàm hiển thị thông báo lỗi
            function showError(input, message) {
                clearValidation(input);
                input.classList.add('is-invalid');
                input.classList.remove('is-valid');

                // Xử lý đặc biệt cho form biến thể
                if (input === variantForm) {
                    // Xóa thông báo lỗi cũ nếu có
                    const oldError = input.querySelector('.variant-form-error');
                    if (oldError) {
                        oldError.remove();
                    }

                    // Tạo thông báo lỗi mới
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger variant-form-error mt-2';
                    errorDiv.textContent = message;
                    input.insertBefore(errorDiv, input.firstChild);
                    return;
                }

                // Xác định parent element để thêm thông báo lỗi
                let parent = input.parentElement;

                // Kiểm tra nếu input nằm trong input-group
                if (parent.classList.contains('input-group')) {
                    // Xóa tất cả các thông báo lỗi cũ trong input-group
                    const oldErrorDivs = parent.querySelectorAll('.invalid-feedback');
                    oldErrorDivs.forEach(div => div.remove());

                    // Tạo div thông báo lỗi mới
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = message;
                    errorDiv.style.display = 'block';

                    // Thêm vào sau input-group
                    parent.parentElement.appendChild(errorDiv);
                } else {
                    // Xóa tất cả các thông báo lỗi cũ
                    const oldErrorDivs = parent.querySelectorAll('.invalid-feedback');
                    oldErrorDivs.forEach(div => div.remove());

                    // Tạo div thông báo lỗi mới
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback';
                    errorDiv.textContent = message;
                    errorDiv.style.display = 'block';
                    parent.appendChild(errorDiv);
                }
            }

            // Hàm hiển thị thành công
            function showSuccess(input) {
                clearValidation(input);
                input.classList.remove('is-invalid');
                input.classList.add('is-valid');

                // Xác định parent element để thêm thông báo thành công
                let parent = input.parentElement;

                // Kiểm tra nếu input nằm trong input-group
                if (parent.classList.contains('input-group')) {
                    // Xóa tất cả các thông báo thành công cũ trong input-group
                    const oldSuccessDivs = parent.querySelectorAll('.valid-feedback');
                    oldSuccessDivs.forEach(div => div.remove());

                    // Tạo div thông báo thành công mới
                    const successDiv = document.createElement('div');
                    successDiv.className = 'valid-feedback';
                    successDiv.textContent = 'Hợp lệ';
                    successDiv.style.display = 'block';

                    // Thêm vào sau input-group
                    parent.parentElement.appendChild(successDiv);
                } else {
                    // Xóa tất cả các thông báo thành công cũ
                    const oldSuccessDivs = parent.querySelectorAll('.valid-feedback');
                    oldSuccessDivs.forEach(div => div.remove());

                    // Tạo div thông báo thành công mới
                    const successDiv = document.createElement('div');
                    successDiv.className = 'valid-feedback';
                    successDiv.textContent = 'Hợp lệ';
                    successDiv.style.display = 'block';
                    parent.appendChild(successDiv);
                }
            }

            // Hàm validate giá biến thể
            function validateVariantPrice(input) {
                const value = parseFloat(input.value);
                if (!input.value || isNaN(value) || value <= 0) {
                    showError(input, 'Giá biến thể phải lớn hơn 0');
                    return false;
                }
                if (value > 999999999) {
                    showError(input, 'Giá biến thể không được lớn hơn 999.999.999 VNĐ');
                    return false;
                }
                showSuccess(input);
                return true;
            }

            // Xử lý thêm biến thể
            function handleAddVariant() {
                // Kiểm tra xem có loại biến thể nào được chọn chưa
                if (selectedTypes.size === 0) {
                    showError(variantTypeSelect, 'Vui lòng chọn ít nhất một loại biến thể');
                    return;
                }

                // Thu thập các giá trị biến thể đã chọn
                const variantValues = {};
                let hasEmptyValue = false;

                selectedTypes.forEach(variantId => {
                    const select = document.querySelector(`.variant-value-select[data-variant-id="${variantId}"]`);
                    // Thay đổi để hỗ trợ select nhiều giá trị
                    if (select.selectedOptions.length === 0) {
                        hasEmptyValue = true;
                        showError(select, `Vui lòng chọn ít nhất một giá trị`);
                    } else {
                        showSuccess(select);
                        // Lấy giá trị đầu tiên được chọn cho biến thể thủ công
                        variantValues[variantId] = select.selectedOptions[0].value;
                    }
                });

                if (hasEmptyValue) {
                    return;
                }

                // Thu thập thông tin cơ bản của biến thể
                const skuInput = document.getElementById('variant-sku');
                const priceInput = document.getElementById('variant-price');
                const quantityInput = document.getElementById('variant-quantity');

                // Reset validation trước khi validate mới
                clearValidation(skuInput);
                clearValidation(priceInput);
                clearValidation(quantityInput);

                // Validate dữ liệu
                let isValid = true;

                // Validate SKU
                if (!skuInput.value.trim()) {
                    showError(skuInput, 'Vui lòng nhập SKU');
                    isValid = false;
                }

                // Validate giá
                if (!validateVariantPrice(priceInput)) {
                    isValid = false;
                }

                // Validate số lượng
                const quantity = parseInt(quantityInput.value);
                if (!quantityInput.value || quantity <= 0) {
                    showError(quantityInput, 'Số lượng phải lớn hơn 0');
                    isValid = false;
                }

                if (!isValid) {
                    return;
                }

                // Kiểm tra trùng lặp SKU
                if (selectedVariants.some(v => v.sku === skuInput.value.trim())) {
                    showError(skuInput, 'SKU này đã tồn tại');
                    return;
                }

                // Tạo biến thể mới
                const variant = {
                    sku: skuInput.value.trim(),
                    price: parseFloat(priceInput.value),
                    quantity: quantity,
                    values: variantValues
                };

                // Kiểm tra trùng lặp tổ hợp biến thể
                const isDuplicateCombination = selectedVariants.some(existingVariant => {
                    // So sánh từng cặp giá trị biến thể
                    const existingKeys = Object.keys(existingVariant.values);
                    const newKeys = Object.keys(variant.values);

                    // Kiểm tra số lượng thuộc tính có giống nhau không
                    if (existingKeys.length !== newKeys.length) {
                        return false;
                    }

                    // Kiểm tra từng giá trị có giống nhau không
                    return existingKeys.every(key =>
                        existingVariant.values[key] === variant.values[key]
                    );
                });

                if (isDuplicateCombination) {
                    showError(variantForm, 'Tổ hợp biến thể này đã tồn tại');
                    return;
                }

                // Thêm biến thể mới vào danh sách
                selectedVariants.push(variant);
                displayVariant(variant);

                // Reset form và xóa thông báo lỗi
                skuInput.value = '';
                priceInput.value = '';
                quantityInput.value = '';

                // Reset validation cho tất cả các trường
                clearValidation(skuInput);
                clearValidation(priceInput);
                clearValidation(quantityInput);

                // Reset các select giá trị biến thể và validation của chúng
                selectedTypes.forEach(variantId => {
                    const select = document.querySelector(`.variant-value-select[data-variant-id="${variantId}"]`);
                    // Không reset giá trị cho select nhiều giá trị
                    select.classList.remove('is-invalid');
                });

                // Xóa thông báo lỗi của form biến thể nếu có
                const variantFormError = variantForm.querySelector('.variant-form-error');
                if (variantFormError) {
                    variantFormError.remove();
                }
            }

            // Thêm sự kiện input cho các trường SKU, giá và số lượng
            document.getElementById('variant-sku').addEventListener('input', function() {
                if (this.value.trim()) {
                    showSuccess(this);
                } else {
                    showError(this, 'Vui lòng nhập SKU');
                }
            });

            document.getElementById('variant-price').addEventListener('input', function() {
                validateVariantPrice(this);
            });

            document.getElementById('variant-quantity').addEventListener('input', function() {
                const value = parseInt(this.value);
                if (this.value && value > 0) {
                    showSuccess(this);
                } else {
                    showError(this, 'Số lượng phải lớn hơn 0');
                }
            });

            // Thêm sự kiện input cho các trường trong form biến thể tự động
            document.addEventListener('click', function(e) {
                // Kiểm tra nếu người dùng nhấp vào nút lưu biến thể
                if (e.target && e.target.classList.contains('save-variant-edit')) {
                    const variantItem = e.target.closest('.variant-item');
                    if (variantItem) {
                        const skuInput = variantItem.querySelector('.variant-sku-input');
                        const priceInput = variantItem.querySelector('.variant-price-input');
                        const quantityInput = variantItem.querySelector('.variant-quantity-input');

                        // Thêm sự kiện input cho các trường này nếu chưa có
                        if (skuInput && !skuInput.dataset.hasInputEvent) {
                            skuInput.dataset.hasInputEvent = 'true';
                            skuInput.addEventListener('input', function() {
                                if (this.value.trim()) {
                                    showSuccess(this);
                                } else {
                                    showError(this, 'Vui lòng nhập SKU');
                                }
                            });
                        }

                        if (priceInput && !priceInput.dataset.hasInputEvent) {
                            priceInput.dataset.hasInputEvent = 'true';
                            priceInput.addEventListener('input', function() {
                                const value = parseFloat(this.value);
                                if (this.value && value > 0) {
                                    showSuccess(this);
                                } else {
                                    showError(this, 'Giá phải lớn hơn 0');
                                }
                            });
                        }

                        if (quantityInput && !quantityInput.dataset.hasInputEvent) {
                            quantityInput.dataset.hasInputEvent = 'true';
                            quantityInput.addEventListener('input', function() {
                                const value = parseInt(this.value);
                                if (this.value && value >= 0) {
                                    showSuccess(this);
                                } else {
                                    showError(this, 'Số lượng không được âm');
                                }
                            });
                        }
                    }
                }
            });

            // Hiển thị biến thể
            function displayVariant(variant) {
                const variantElement = document.createElement('div');
                variantElement.className = 'variant-item mb-4';

                // Tạo ID duy nhất cho biến thể dựa trên index trong mảng selectedVariants
                const variantIndex = selectedVariants.length - 1;
                const variantId = 'variant_' + variantIndex;
                variantElement.dataset.variantId = variantId;
                variantElement.dataset.variantIndex = variantIndex;

                // Tạo chuỗi hiển thị các giá trị biến thể
                const variantValuesDisplay = Object.entries(variant.values).map(([variantId, valueId]) => {
                    const variantOption = variantTypeSelect.querySelector(`option[value="${variantId}"]`);
                    const variantName = variantOption.text;
                    const variantValues = JSON.parse(variantOption.dataset.values);
                    const selectedValue = variantValues.find(v => v.id == valueId);
                    return `${variantName}: ${selectedValue.value}`;
                }).join(' - ');

                variantElement.innerHTML = `
                    <div class="variant-preview p-3 cursor-pointer" onclick="toggleVariantEdit('${variantId}')">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 text-primary">Biến thể #${variantIndex + 1}</h6>
                            <button type="button" class="btn-remove-variant" onclick="removeVariant('${variantId}', event)">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-2">
                                <div class="variant-info">
                                    <label class="form-label text-muted mb-1">SKU</label>
                                    <p class="mb-0 fw-medium variant-sku-display">${variant.sku || 'Chưa có'}</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="variant-info">
                                    <label class="form-label text-muted mb-1">Giá</label>
                                    <p class="mb-0 fw-medium variant-price-display">${variant.price ? parseInt(variant.price).toLocaleString('vi-VN') + ' VNĐ' : 'Chưa có'}</p>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="variant-info">
                                    <label class="form-label text-muted mb-1">Số lượng</label>
                                    <p class="mb-0 fw-medium variant-quantity-display">${variant.quantity || 'Chưa có'}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="variant-info">
                                    <label class="form-label text-muted mb-1">Thông tin biến thể</label>
                                    <p class="mb-0 fw-medium">${variantValuesDisplay}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="variant-edit p-3 bg-light border-top" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">SKU <span class="text-danger">*</span></label>
                                <input type="text" class="form-control variant-sku-input" value="${variant.sku || ''}">
                                <div class="invalid-feedback">Vui lòng nhập SKU</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Giá <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control variant-price-input" value="${variant.price || ''}" min="0">
                                    <span class="input-group-text">VNĐ</span>
                                    <div class="invalid-feedback">Giá phải lớn hơn 0</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                <input type="number" class="form-control variant-quantity-input" value="${variant.quantity || ''}" min="0">
                                <div class="invalid-feedback">Số lượng phải lớn hơn 0</div>
                            </div>
                        </div>
                        <div class="mt-3 text-end">
                            <button type="button" class="btn btn-secondary me-2" onclick="toggleVariantEdit('${variantId}')">Hủy</button>
                            <button type="button" class="btn btn-primary" onclick="saveVariantEdit('${variantId}')">Lưu</button>
                        </div>
                    </div>
                `;

                // Thêm input hidden để lưu dữ liệu
                const hiddenInputs = document.createElement('div');
                hiddenInputs.className = 'variant-hidden-inputs';
                hiddenInputs.innerHTML = `
                    <input type="hidden" name="variants[${variantIndex}][sku]" class="variant-sku-hidden" value="${variant.sku || ''}">
                    <input type="hidden" name="variants[${variantIndex}][price]" class="variant-price-hidden" value="${variant.price || ''}">
                    <input type="hidden" name="variants[${variantIndex}][quantity]" class="variant-quantity-hidden" value="${variant.quantity || ''}">
                    ${Object.entries(variant.values).map(([variantId, valueId]) =>
                        `<input type="hidden" name="variants[${variantIndex}][values][${variantId}]" value="${valueId}">`
                    ).join('')}
                `;
                variantElement.appendChild(hiddenInputs);

                const variantsContainer = document.getElementById('variants-container');
                if (variantsContainer) {
                    variantsContainer.appendChild(variantElement);
                }
            }

            // Mở/đóng form chỉnh sửa biến thể
            window.toggleVariantEdit = function(variantId, event) {
                if (event) {
                    event.stopPropagation();
                }
                const variantElement = document.querySelector(`[data-variant-id="${variantId}"]`);
                if (variantElement) {
                    const editForm = variantElement.querySelector('.variant-edit');
                    if (editForm.style.display === 'none') {
                        // Đóng tất cả các form chỉnh sửa khác trước khi mở form mới
                        document.querySelectorAll('.variant-edit').forEach(form => {
                            if (form !== editForm && form.style.display !== 'none') {
                                form.style.display = 'none';
                            }
                        });
                        editForm.style.display = 'block';
                    } else {
                        editForm.style.display = 'none';
                    }
                }
            };

            // Lưu thông tin chỉnh sửa biến thể
            window.saveVariantEdit = function(variantId) {
                const variantElement = document.querySelector(`[data-variant-id="${variantId}"]`);
                if (!variantElement) return;

                const variantIndex = parseInt(variantElement.dataset.variantIndex);
                if (isNaN(variantIndex) || variantIndex < 0 || variantIndex >= selectedVariants.length) {
                    console.error('Không tìm thấy biến thể với index:', variantIndex);
                    return;
                }

                const skuInput = variantElement.querySelector('.variant-sku-input');
                const priceInput = variantElement.querySelector('.variant-price-input');
                const quantityInput = variantElement.querySelector('.variant-quantity-input');

                // Validate dữ liệu
                let isValid = true;

                // Validate SKU
                if (!skuInput.value.trim()) {
                    showError(skuInput, 'Vui lòng nhập SKU');
                    isValid = false;
                } else {
                    showSuccess(skuInput);
                }

                // Validate giá
                const price = parseFloat(priceInput.value);
                if (!priceInput.value || isNaN(price) || price <= 0) {
                    showError(priceInput, 'Giá phải lớn hơn 0');
                    isValid = false;
                } else {
                    showSuccess(priceInput);
                }

                // Validate số lượng
                const quantity = parseInt(quantityInput.value);
                if (!quantityInput.value || isNaN(quantity) || quantity < 0) {
                    showError(quantityInput, 'Số lượng không được âm');
                    isValid = false;
                } else {
                    showSuccess(quantityInput);
                }

                if (!isValid) return;

                // Kiểm tra trùng lặp SKU
                const isDuplicateSku = selectedVariants.some((v, i) => i !== variantIndex && v.sku === skuInput.value.trim());

                if (isDuplicateSku) {
                    showError(skuInput, 'SKU này đã tồn tại');
                    return;
                }

                // Cập nhật dữ liệu hiển thị
                variantElement.querySelector('.variant-sku-display').textContent = skuInput.value;
                variantElement.querySelector('.variant-price-display').textContent = parseInt(price).toLocaleString('vi-VN') + ' VNĐ';
                variantElement.querySelector('.variant-quantity-display').textContent = quantity;

                // Cập nhật dữ liệu trong hidden inputs
                variantElement.querySelector('.variant-sku-hidden').value = skuInput.value;
                variantElement.querySelector('.variant-price-hidden').value = price;
                variantElement.querySelector('.variant-quantity-hidden').value = quantity;

                // Cập nhật dữ liệu trong mảng selectedVariants
                selectedVariants[variantIndex].sku = skuInput.value;
                selectedVariants[variantIndex].price = price;
                selectedVariants[variantIndex].quantity = quantity;

                // Đóng form chỉnh sửa
                toggleVariantEdit(variantId);
            };

            // Xóa biến thể
            window.removeVariant = function(variantId, event) {
                if (event) {
                    event.stopPropagation();
                }
                const variantElement = document.querySelector(`[data-variant-id="${variantId}"]`);
                if (variantElement) {
                    const variantIndex = parseInt(variantElement.dataset.variantIndex);
                    if (isNaN(variantIndex) || variantIndex < 0 || variantIndex >= selectedVariants.length) {
                        console.error('Không tìm thấy biến thể với index:', variantIndex);
                        return;
                    }

                    // Xóa biến thể khỏi mảng
                    selectedVariants.splice(variantIndex, 1);
                    variantElement.remove();

                    // Cập nhật lại index và hiển thị cho tất cả các biến thể còn lại
                    updateVariantIndexes();
                }
            };

            // Hàm cập nhật lại index và hiển thị cho tất cả các biến thể
            function updateVariantIndexes() {
                const variantItems = document.querySelectorAll('.variant-item');
                variantItems.forEach((item, idx) => {
                    // Cập nhật index trong dataset
                    item.dataset.variantIndex = idx;

                    // Cập nhật số thứ tự hiển thị
                    item.querySelector('h6').textContent = `Biến thể #${idx + 1}`;

                    // Cập nhật ID của biến thể
                    const oldId = item.dataset.variantId;
                    const newId = 'variant_' + idx;
                    item.dataset.variantId = newId;

                    // Cập nhật các sự kiện onclick
                    const previewDiv = item.querySelector('.variant-preview');
                    previewDiv.setAttribute('onclick', `toggleVariantEdit('${newId}')`);

                    const removeBtn = item.querySelector('.btn-remove-variant');
                    removeBtn.setAttribute('onclick', `removeVariant('${newId}', event)`);

                    const cancelBtn = item.querySelector('.btn-secondary');
                    cancelBtn.setAttribute('onclick', `toggleVariantEdit('${newId}')`);

                    const saveBtn = item.querySelector('.btn-primary');
                    saveBtn.setAttribute('onclick', `saveVariantEdit('${newId}')`);

                    // Cập nhật name của các input hidden
                    const inputs = item.querySelectorAll('.variant-hidden-inputs input[type="hidden"]');
                    inputs.forEach(input => {
                        const name = input.getAttribute('name');
                        input.setAttribute('name', name.replace(/\[\d+\]/, `[${idx}]`));
                    });
                });
            }

            // Hàm gắn sự kiện cho các trường trong biến thể tự động
            function attachEventsToGeneratedVariants() {
                // Lấy tất cả các biến thể
                const variantItems = document.querySelectorAll('.variant-item');

                variantItems.forEach(variantItem => {
                    // Lấy các trường input
                    const skuInput = variantItem.querySelector('.variant-sku-input');
                    const priceInput = variantItem.querySelector('.variant-price-input');
                    const quantityInput = variantItem.querySelector('.variant-quantity-input');

                    // Thêm sự kiện input cho SKU
                    if (skuInput && !skuInput.dataset.hasInputEvent) {
                        skuInput.dataset.hasInputEvent = 'true';
                        skuInput.addEventListener('input', function() {
                            if (this.value.trim()) {
                                showSuccess(this);
                            } else {
                                showError(this, 'Vui lòng nhập SKU');
                            }
                        });
                    }

                    // Thêm sự kiện input cho giá
                    if (priceInput && !priceInput.dataset.hasInputEvent) {
                        priceInput.dataset.hasInputEvent = 'true';
                        priceInput.addEventListener('input', function() {
                            const value = parseFloat(this.value);
                            if (this.value && value > 0) {
                                showSuccess(this);
                            } else {
                                showError(this, 'Giá phải lớn hơn 0');
                            }
                        });
                    }

                    // Thêm sự kiện input cho số lượng
                    if (quantityInput && !quantityInput.dataset.hasInputEvent) {
                        quantityInput.dataset.hasInputEvent = 'true';
                        quantityInput.addEventListener('input', function() {
                            const value = parseInt(this.value);
                            if (this.value && value >= 0) {
                                showSuccess(this);
                            } else {
                                showError(this, 'Số lượng không được âm');
                            }
                        });
                    }
                });
            }

            // Thêm hàm tạo biến thể tự động
            function handleGenerateVariants() {
                // Kiểm tra xem có thuộc tính nào được chọn không
                if (selectedTypes.size === 0) {
                    showError(variantTypeSelect, 'Vui lòng chọn ít nhất một thuộc tính biến thể');
                    return;
                }

                // Hiển thị loading
                const generateBtn = document.getElementById('generate-variants-btn');
                const originalBtnText = generateBtn.innerHTML;
                generateBtn.disabled = true;
                generateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang tạo biến thể...';

                // Thu thập các giá trị thuộc tính đã chọn
                const variantTypes = [];
                const variantValues = [];

                // Lấy thông tin các thuộc tính và giá trị đã chọn
                selectedTypes.forEach(variantId => {
                    const valueDataInput = document.querySelector(`.variant-value-data[data-variant-id="${variantId}"]`);
                    if (valueDataInput) {
                        try {
                            const selectedValues = JSON.parse(valueDataInput.value);
                            if (selectedValues.length > 0) {
                                // Thêm thông tin thuộc tính
                                const variantOption = variantTypeSelect.querySelector(`option[value="${variantId}"]`);
                                const variantName = variantOption.text;

                                variantTypes.push({
                                    id: variantId,
                                    name: variantName
                                });

                                // Thêm thông tin giá trị
                                const values = selectedValues.map(v => ({
                                    id: v.id,
                                    value: v.value,
                                    variantId: variantId
                                }));

                                variantValues.push(values);
                            }
                        } catch (error) {
                            console.error('Lỗi khi parse JSON:', error, valueDataInput.value);
                        }
                    }
                });

                if (variantTypes.length === 0 || variantValues.length === 0) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Không tìm thấy giá trị thuộc tính nào đã được chọn',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    generateBtn.disabled = false;
                    generateBtn.innerHTML = originalBtnText;
                    return;
                }

                // Tạo tất cả các tổ hợp có thể từ các giá trị đã chọn
                const combinations = generateCombinations(variantValues);

                // Tạo các biến thể từ các tổ hợp
                const variants = [];

                combinations.forEach((combination) => {
                    // Tạo biến thể
                    const variant = {
                        sku: '',
                        price: '',
                        quantity: '',
                        values: {}
                    };

                    // Thêm các giá trị thuộc tính
                    combination.forEach(item => {
                        variant.values[item.variantId] = item.id;
                    });

                    variants.push(variant);
                });

                // Xóa các biến thể hiện tại
                selectedVariants = [];
                document.getElementById('variants-container').innerHTML = '';

                // Thêm các biến thể mới
                variants.forEach(variant => {
                    selectedVariants.push(variant);
                    displayVariant(variant);
                });

                // Gắn sự kiện cho các trường trong biến thể tự động
                attachEventsToGeneratedVariants();

                // Hiển thị thông báo thành công
                Swal.fire({
                    title: 'Thành công!',
                    text: `Đã tạo ${variants.length} biến thể. Vui lòng nhấp vào từng biến thể để nhập thông tin SKU, giá và số lượng.`,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });

                // Khôi phục trạng thái nút
                generateBtn.disabled = false;
                generateBtn.innerHTML = originalBtnText;
            }

            // Hàm tạo tất cả các tổ hợp có thể từ các giá trị đã chọn
            function generateCombinations(arrays) {
                // Nếu chỉ có một mảng, trả về mảng đó
                if (arrays.length === 1) {
                    return arrays[0].map(item => [item]);
                }

                // Lấy mảng đầu tiên
                const first = arrays[0];
                // Lấy các tổ hợp của các mảng còn lại
                const rest = generateCombinations(arrays.slice(1));

                // Kết hợp mảng đầu tiên với các tổ hợp của các mảng còn lại
                const result = [];
                for (let i = 0; i < first.length; i++) {
                    for (let j = 0; j < rest.length; j++) {
                        result.push([first[i], ...rest[j]]);
                    }
                }

                return result;
            }

            // Khởi tạo form
            initializeForm();

            // Thêm sự kiện input và change cho các trường chính
            nameInput.addEventListener('input', function() {
                if (this.value.trim()) {
                    showSuccess(this);
                } else {
                    showError(this, 'Tên sản phẩm là bắt buộc');
                }
            });

            categorySelect.addEventListener('change', function() {
                if (this.value) {
                    showSuccess(this);
                } else {
                    showError(this, 'Vui lòng chọn danh mục');
                }
            });

            priceInput.addEventListener('input', function() {
                const value = parseFloat(this.value);
                if (this.value && value > 0) {
                    showSuccess(this);
                    // Kiểm tra lại giá khuyến mãi nếu có
                    if (discountPriceInput.value) {
                        const discountValue = parseFloat(discountPriceInput.value);
                        if (discountValue >= value) {
                            showError(discountPriceInput, 'Giá khuyến mãi phải nhỏ hơn giá gốc');
                        } else {
                            showSuccess(discountPriceInput);
                        }
                    }
                } else {
                    showError(this, 'Giá gốc phải lớn hơn 0');
                }
            });

            discountPriceInput.addEventListener('input', function() {
                if (!this.value) {
                    clearValidation(this);
                    return;
                }

                const value = parseFloat(this.value);
                const basePrice = parseFloat(priceInput.value);

                if (isNaN(basePrice) || basePrice <= 0) {
                    showError(this, 'Vui lòng nhập giá gốc hợp lệ trước');
                    return;
                }

                if (value >= basePrice) {
                    showError(this, 'Giá khuyến mãi phải nhỏ hơn giá gốc');
                } else {
                    showSuccess(this);
                }
            });

            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    showSuccess(this);
                } else {
                    showError(this, 'Ảnh đại diện là bắt buộc');
                }
            });
        });
    </script>
