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

        // Hàm loại bỏ dấu tiếng Việt
        function removeVietnameseAccents(str) {
            if (!str) return '';
            str = str.toString();
            return str.normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .replace(/đ/g, 'd').replace(/Đ/g, 'D');
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

        // ... existing code ...

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

                        // Ẩn/hiện trường số lượng trong phần basicPriceSection
                        const quantitySection = document.getElementById('quantitySection');
                        if (quantitySection) {
                            if (this.checked) {
                                // Khi bật biến thể, ẩn trường số lượng
                                quantitySection.style.display = 'none';
                                
                                // Xóa giá trị và ẩn thông báo lỗi chỉ cho trường số lượng
                                const quantityInput = document.getElementById('quantity');
                                if (quantityInput) {
                                    quantityInput.value = '';
                                    clearValidation(quantityInput);
                                }
                                // Không xóa validation cho trường giá gốc
                            } else {
                                // Khi tắt biến thể, hiện trường số lượng
                                quantitySection.style.display = 'block';
                            }
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

                // Khởi tạo trạng thái hiển thị của trường số lượng dựa vào toggle
                const quantitySection = document.getElementById('quantitySection');
                if (quantitySection) {
                    if (variantToggle && variantToggle.checked) {
                        // Nếu biến thể được bật, ẩn trường số lượng
                        quantitySection.style.display = 'none';
                    } else {
                        // Nếu biến thể tắt, hiện trường số lượng
                        quantitySection.style.display = 'block';
                    }
                }
            }

            // Xử lý submit form
            function handleFormSubmit(e) {
                e.preventDefault();
                
                let isValid = true;
                let firstErrorElement = null;
                
                // Validate các trường cơ bản
                // Tên sản phẩm
                if (!nameInput.value.trim()) {
                    showError(nameInput, 'Tên sản phẩm là bắt buộc');
                    isValid = false;
                    firstErrorElement = firstErrorElement || nameInput;
                } else {
                    showSuccess(nameInput);
                }
                
                // Danh mục
                if (!categorySelect.value) {
                    showError(categorySelect, 'Vui lòng chọn danh mục');
                    isValid = false;
                    firstErrorElement = firstErrorElement || categorySelect;
                } else {
                    showSuccess(categorySelect);
                }
                
                // Ảnh đại diện
                if (!imageInput.files || imageInput.files.length === 0) {
                    showError(imageInput, 'Vui lòng chọn ảnh đại diện');
                    isValid = false;
                    firstErrorElement = firstErrorElement || imageInput;
                } else {
                    showSuccess(imageInput);
                }
                
                // Chế độ biến thể
                const hasVariants = variantToggle && variantToggle.checked;
                
                // Giá gốc - luôn kiểm tra và hiển thị lỗi
                const price = parseFloat(priceInput.value);
                if (!priceInput.value || isNaN(price) || price <= 0) {
                    showError(priceInput, 'Giá gốc phải lớn hơn 0');
                    // Chỉ tính là không hợp lệ khi không có biến thể
                    if (!hasVariants) {
                        isValid = false;
                        firstErrorElement = firstErrorElement || priceInput;
                    }
                } else if (price > 999999999) {
                    showError(priceInput, 'Giá gốc không được lớn hơn 999.999.999 VNĐ');
                    // Chỉ tính là không hợp lệ khi không có biến thể
                    if (!hasVariants) {
                        isValid = false;
                        firstErrorElement = firstErrorElement || priceInput;
                    }
                } else {
                    showSuccess(priceInput);
                }
                
                // Số lượng - chỉ bắt buộc khi không có biến thể
                if (!hasVariants && quantityInput) {
                    if (quantityInput.value === '' || quantityInput.value === null || isNaN(parseInt(quantityInput.value))) {
                        showError(quantityInput, 'Số lượng không được để trống');
                        isValid = false;
                        firstErrorElement = firstErrorElement || quantityInput;
                    } else if (parseInt(quantityInput.value) < 0) {
                        showError(quantityInput, 'Số lượng phải lớn hơn hoặc bằng 0');
                        isValid = false;
                        firstErrorElement = firstErrorElement || quantityInput;
                    } else {
                        showSuccess(quantityInput);
                    }
                }
                // Không cần xóa validation của giá gốc nếu có biến thể

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
                if (hasVariants) {
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
                        let invalidPriceVariant = false;
                        let invalidPriceIndex = -1;

                        for (let i = 0; i < selectedVariants.length; i++) {
                            const variant = selectedVariants[i];
                            missingFields = [];

                            // Kiểm tra giá
                            if (!variant.price || isNaN(parseFloat(variant.price)) || parseFloat(variant.price) <= 0) {
                                missingFields.push('Giá');
                            } else if (parseFloat(variant.price) > 999999999) {
                                invalidPriceVariant = true;
                                invalidPriceIndex = i;
                                break;
                            }

                            // Kiểm tra số lượng
                            if (variant.quantity === undefined || variant.quantity === '' || isNaN(parseInt(variant.quantity))) {
                                missingFields.push('Số lượng');
                            }

                            // Kiểm tra giá trị thuộc tính
                            if (!variant.values || Object.keys(variant.values).length === 0) {
                                missingFields.push('Giá trị thuộc tính');
                            }

                            if (missingFields.length > 0) {
                                hasEmptyVariant = true;
                                emptyVariantIndex = i;
                                break;
                            }
                        }

                        if (invalidPriceVariant) {
                            Swal.fire({
                                title: 'Lỗi!',
                                html: `Biến thể #${invalidPriceIndex + 1} có giá vượt quá giới hạn cho phép.<br>Giá không được lớn hơn 999.999.999 VNĐ.`,
                                icon: 'error',
                                confirmButtonText: 'Đóng'
                            });
                            isValid = false;
                            return;
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

                // Log dữ liệu biến thể gửi đi để debug
                console.log('Dữ liệu biến thể gửi đi:', selectedVariants);

                // Đảm bảo dữ liệu biến thể được gửi đúng cách
                if (hasVariants && selectedVariants.length > 0) {
                    // Xóa tất cả các input hidden biến thể cũ (nếu có)
                    const oldVariantInputs = form.querySelectorAll('input[name^="variants["]');
                    oldVariantInputs.forEach(input => input.remove());

                    // Thêm dữ liệu biến thể vào form
                    selectedVariants.forEach((variant, index) => {
                        // Kiểm tra dữ liệu biến thể trước khi thêm vào form
                        if (!variant.sku || !variant.price || variant.quantity === undefined) {
                            console.error(`Biến thể #${index + 1} thiếu thông tin:`, variant);
                            return;
                        }

                        // Kiểm tra giá biến thể
                        if (parseFloat(variant.price) > 999999999) {
                            console.error(`Biến thể #${index + 1} có giá vượt quá giới hạn:`, variant.price);
                            isValid = false;
                            return;
                        }

                        // Tạo input hidden mới cho mỗi biến thể
                        const skuInput = document.createElement('input');
                        skuInput.type = 'hidden';
                        skuInput.name = `variants[${index}][sku]`;
                        skuInput.value = variant.sku;
                        form.appendChild(skuInput);

                        const priceInput = document.createElement('input');
                        priceInput.type = 'hidden';
                        priceInput.name = `variants[${index}][price]`;
                        priceInput.value = variant.price;
                        form.appendChild(priceInput);

                        const quantityInput = document.createElement('input');
                        quantityInput.type = 'hidden';
                        quantityInput.name = `variants[${index}][quantity]`;
                        quantityInput.value = variant.quantity;
                        form.appendChild(quantityInput);

                        // Thêm các giá trị thuộc tính
                        if (variant.values && Object.keys(variant.values).length > 0) {
                            Object.entries(variant.values).forEach(([variantId, valueId]) => {
                                const valueInput = document.createElement('input');
                                valueInput.type = 'hidden';
                                valueInput.name = `variants[${index}][values][${variantId}]`;
                                valueInput.value = valueId;
                                form.appendChild(valueInput);
                            });
                        } else {
                            console.error(`Biến thể #${index + 1} không có giá trị thuộc tính:`, variant);
                        }
                    });
                } else if (hasVariants) {
                    // Nếu toggle biến thể được bật nhưng không có biến thể nào, gửi một mảng rỗng
                    const variantsInput = document.createElement('input');
                    variantsInput.type = 'hidden';
                    variantsInput.name = 'variants';
                    variantsInput.value = JSON.stringify([]);
                    form.appendChild(variantsInput);
                } else {
                    // Nếu toggle biến thể tắt, không gửi dữ liệu biến thể
                    const oldVariantInputs = form.querySelectorAll('input[name^="variants["]');
                    oldVariantInputs.forEach(input => input.remove());
                    
                    const hasVariantsInput = document.createElement('input');
                    hasVariantsInput.type = 'hidden';
                    hasVariantsInput.name = 'has_variants';
                    hasVariantsInput.value = '0';
                    form.appendChild(hasVariantsInput);
                }

                // Tạo FormData sau khi đã thêm tất cả các input hidden
                const formData = new FormData(form);

                // Log dữ liệu gửi đi để debug
                console.log('Dữ liệu form gửi đi:');
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
            function clearValidation(field) {
                if (!field) return;
                
                field.classList.remove('is-invalid', 'is-valid');
                
                // Ẩn thông báo lỗi
                const feedback = field.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.style.display = 'none';
                }
                
                // Ẩn thông báo lỗi trong input-group
                const parent = field.parentElement;
                if (parent && parent.classList.contains('input-group')) {
                    const groupFeedback = parent.nextElementSibling;
                    if (groupFeedback && groupFeedback.classList.contains('invalid-feedback')) {
                        groupFeedback.style.display = 'none';
                    }
                }
                
                // Ẩn thông báo lỗi custom cho trường số lượng
                if (field.id === 'quantity') {
                    const quantityError = document.getElementById('quantity-error');
                    if (quantityError) {
                        quantityError.style.display = 'none';
                    }
                }
                
                // Xóa các thông báo lỗi trùng lặp nếu có
                const parentNode = field.parentElement.parentElement;
                if (parentNode) {
                    const duplicateErrors = parentNode.querySelectorAll('.invalid-feedback');
                    if (duplicateErrors.length > 1) {
                        // Giữ lại error đầu tiên, xóa các error trùng lặp
                        for (let i = 1; i < duplicateErrors.length; i++) {
                            duplicateErrors[i].remove();
                        }
                    }
                }
            }

            // Hàm hiển thị thông báo lỗi
            function showError(field, message) {
                if (!field) return;
                
                // Xóa thông báo lỗi cũ trước khi thêm thông báo mới
                clearValidation(field);
                
                field.classList.remove('is-valid');
                field.classList.add('is-invalid');
                
                // Tìm phần tử feedback
                let feedbackElement = null;
                
                // Kiểm tra nếu field là một thẻ input trong input-group
                const parent = field.parentElement;
                if (parent && parent.classList.contains('input-group')) {
                    // Tìm phần tử feedback sau input-group
                    feedbackElement = parent.nextElementSibling;
                    if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                        // Nếu không tìm thấy, tạo mới
                        feedbackElement = document.createElement('div');
                        feedbackElement.className = 'invalid-feedback';
                        feedbackElement.style.display = 'block';
                        parent.after(feedbackElement);
                    }
                } else {
                    // Tìm phần tử feedback ngay sau field
                    feedbackElement = field.nextElementSibling;
                    if (!feedbackElement || !feedbackElement.classList.contains('invalid-feedback')) {
                        // Nếu không tìm thấy, tạo mới
                        feedbackElement = document.createElement('div');
                        feedbackElement.className = 'invalid-feedback';
                        feedbackElement.style.display = 'block';
                        field.after(feedbackElement);
                    }
                }
                
                // Cập nhật nội dung
                feedbackElement.textContent = message;
                feedbackElement.style.display = 'block';
            }

            // Hàm hiển thị thành công
            function showSuccess(input) {
                clearValidation(input);
                input.classList.add('is-valid');
                input.classList.remove('is-invalid');
                
                // Ẩn thông báo lỗi
                let parent = input.parentElement;
                if (parent.classList.contains('input-group')) {
                    const errorDivs = parent.querySelectorAll('.invalid-feedback');
                    errorDivs.forEach(div => {
                        div.style.display = 'none';
                    });
                } else {
                    const errorDiv = parent.querySelector('.invalid-feedback');
                    if (errorDiv) {
                        errorDiv.style.display = 'none';
                    }
                }
            }

            // Hàm validate giá biến thể
            function validateVariantPrice(input) {
                const value = parseFloat(input.value);
                if (!input.value || isNaN(value) || value <= 0) {
                    showError(input, 'Giá biến thể phải lớn hơn 0');
                    return false;
                }
                // Thêm kiểm tra giới hạn tối đa cho giá
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
                
                // Thu thập thông tin thuộc tính cho việc tạo SKU
                const attributesForSku = [];

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
                        
                        // Thu thập thông tin cho SKU
                        const variantName = select.closest('.variant-type-item').querySelector('h5').textContent;
                        const valueText = select.selectedOptions[0].text;
                        attributesForSku.push({
                            variant_name: variantName,
                            value: valueText
                        });
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
                clearValidation(priceInput);
                clearValidation(quantityInput);

                // Validate dữ liệu
                let isValid = true;

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
                
                // Tạo SKU tự động
                const productCode = document.querySelector('input[name="product_code"]')?.value || 'SKU';
                let sku = productCode + '-';
                attributesForSku.forEach(attr => {
                    // Lấy 3 ký tự đầu của mỗi giá trị thuộc tính và loại bỏ dấu
                    const valueWithoutAccent = removeVietnameseAccents(attr.value);
                    sku += valueWithoutAccent.substring(0, 3).toUpperCase();
                });
                
                // Thêm số ngẫu nhiên vào cuối SKU để đảm bảo không bị trùng lặp
                sku += '-' + Math.floor(100 + Math.random() * 100);
                
                // Gán SKU tự động vào input ẩn
                skuInput.value = sku;

                // Kiểm tra trùng lặp SKU
                if (selectedVariants.some(v => v.sku === skuInput.value.trim())) {
                    // Thêm số ngẫu nhiên vào SKU để tránh trùng lặp
                    skuInput.value = sku + '-' + Math.floor(Math.random() * 100);
                }

                // Kiểm tra giá một lần nữa
                const price = parseFloat(priceInput.value);
                if (price > 999999999) {
                    showError(priceInput, 'Giá biến thể không được lớn hơn 999.999.999 VNĐ');
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

            // Thêm sự kiện input cho các trường giá và số lượng
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
                                    if (value > 999999999) {
                                        showError(this, 'Giá không được lớn hơn 999.999.999 VNĐ');
                                    } else {
                                        showSuccess(this);
                                    }
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
                                    showError(this, 'Số lượng không được để trống');
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
                            <div class="col-md-6">
                                <label class="form-label">Giá <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control variant-price-input" value="${variant.price || ''}" min="0">
                                    <span class="input-group-text">VNĐ</span>
                                    <div class="invalid-feedback" style="display: none;">Giá phải lớn hơn 0</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                <input type="number" class="form-control variant-quantity-input" value="${variant.quantity || ''}" min="0">
                                <div class="invalid-feedback" style="display: none;">Số lượng phải lớn hơn 0</div>
                            </div>
                            <!-- Trường SKU đã bị ẩn vì sẽ được tạo tự động -->
                            <input type="hidden" class="variant-sku-input" value="${variant.sku || ''}">
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
                if (!variantElement) {
                    console.error('Không tìm thấy phần tử biến thể với ID:', variantId);
                    return;
                }

                const variantIndex = parseInt(variantElement.dataset.variantIndex);
                if (isNaN(variantIndex) || variantIndex < 0 || variantIndex >= selectedVariants.length) {
                    console.error('Không tìm thấy biến thể với index:', variantIndex);
                    return;
                }

                const skuInput = variantElement.querySelector('.variant-sku-input');
                const priceInput = variantElement.querySelector('.variant-price-input');
                const quantityInput = variantElement.querySelector('.variant-quantity-input');
                
                if (!skuInput || !priceInput || !quantityInput) {
                    console.error('Không tìm thấy các trường input cần thiết');
                    return;
                }

                // Validate dữ liệu
                let isValid = true;

                // Validate giá
                const price = parseFloat(priceInput.value);
                if (!priceInput.value || isNaN(price) || price <= 0) {
                    showError(priceInput, 'Giá phải lớn hơn 0');
                    isValid = false;
                } else if (price > 999999999) {
                    showError(priceInput, 'Giá không được lớn hơn 999.999.999 VNĐ');
                    isValid = false;
                } else {
                    showSuccess(priceInput);
                }

                // Validate số lượng
                const quantity = parseInt(quantityInput.value);
                if (!quantityInput.value || isNaN(quantity) || quantity < 0) {
                    showError(quantityInput, 'Số lượng không được để trống');
                    isValid = false;
                } else {
                    showSuccess(quantityInput);
                }

                if (!isValid) {
                    console.error('Dữ liệu không hợp lệ');
                    return;
                }

                // Cập nhật dữ liệu hiển thị
                const skuDisplay = variantElement.querySelector('.variant-sku-display');
                const priceDisplay = variantElement.querySelector('.variant-price-display');
                const quantityDisplay = variantElement.querySelector('.variant-quantity-display');
                
                if (skuDisplay) skuDisplay.textContent = skuInput.value;
                if (priceDisplay) priceDisplay.textContent = parseInt(price).toLocaleString('vi-VN') + ' VNĐ';
                if (quantityDisplay) quantityDisplay.textContent = quantity;

                // Cập nhật dữ liệu trong hidden inputs
                const skuHidden = variantElement.querySelector('.variant-sku-hidden');
                const priceHidden = variantElement.querySelector('.variant-price-hidden');
                const quantityHidden = variantElement.querySelector('.variant-quantity-hidden');
                
                if (skuHidden) skuHidden.value = skuInput.value;
                if (priceHidden) priceHidden.value = price;
                if (quantityHidden) quantityHidden.value = quantity;

                // Cập nhật dữ liệu trong mảng selectedVariants
                selectedVariants[variantIndex].sku = skuInput.value;
                selectedVariants[variantIndex].price = price;
                selectedVariants[variantIndex].quantity = quantity;

                // Log để debug
                console.log('Đã cập nhật biến thể #' + (variantIndex + 1) + ':', selectedVariants[variantIndex]);

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

                    // Thêm sự kiện input cho giá
                    if (priceInput && !priceInput.dataset.hasInputEvent) {
                        priceInput.dataset.hasInputEvent = 'true';
                        priceInput.addEventListener('input', function() {
                            const value = parseFloat(this.value);
                            if (this.value && value > 0) {
                                if (value > 999999999) {
                                    showError(this, 'Giá không được lớn hơn 999.999.999 VNĐ');
                                } else {
                                    showSuccess(this);
                                }
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
                                showError(this, 'Số lượng không được để trống');
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
                
                // Lấy tên sản phẩm để tạo mã sản phẩm
                let productCode = '';
                const productName = document.getElementById('name').value;
                if (productName) {
                    // Tạo mã sản phẩm từ tên sản phẩm (lấy các chữ cái đầu của mỗi từ)
                    productCode = productName
                        .split(' ')
                        .map(word => word.charAt(0))
                        .join('')
                        .toUpperCase();
                } else {
                    productCode = 'SKU';
                }

                combinations.forEach((combination) => {
                    // Tạo SKU tự động
                    let sku = productCode + '-';
                    combination.forEach(item => {
                        // Lấy 2 ký tự đầu của mỗi giá trị thuộc tính và loại bỏ dấu
                        const valueWithoutAccent = removeVietnameseAccents(item.value);
                        sku += valueWithoutAccent.substring(0, 2).toUpperCase();
                    });
                    
                    // Thêm số ngẫu nhiên vào cuối SKU để đảm bảo không bị trùng lặp
                    sku += '-' + Math.floor(100 + Math.random() * 100);
                    
                    // Tạo biến thể
                    const variant = {
                        sku: sku,
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
                    text: `Đã tạo ${variants.length} biến thể. Vui lòng nhấp vào từng biến thể để nhập thông tin giá và số lượng.`,
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
                    if (value > 999999999) {
                        showError(this, 'Giá gốc không được lớn hơn 999.999.999 VNĐ');
                    } else {
                        showSuccess(this);
                        // Kiểm tra lại giá khuyến mãi nếu có
                        if (discountPriceInput.value) {
                            const discountValue = parseFloat(discountPriceInput.value);
                            if (discountValue >= value) {
                                clearValidation(discountPriceInput);
                                showError(discountPriceInput, 'Giá khuyến mãi phải nhỏ hơn giá gốc');
                            } else {
                                showSuccess(discountPriceInput);
                            }
                        }
                    }
                } else {
                    showError(this, 'Giá gốc phải lớn hơn 0');
                }
            });

            discountPriceInput.addEventListener('input', function() {
                // Xóa thông báo lỗi cũ trước khi validate
                clearValidation(this);
                
                if (!this.value) {
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

            // Thêm validate cho trường số lượng
            const quantityInput = document.getElementById('quantity');
            if (quantityInput) {
                quantityInput.addEventListener('input', function() {
                    // Nếu đang ở chế độ biến thể, không cần validate
                    if (variantToggle && variantToggle.checked) {
                        clearValidation(this);
                        return;
                    }
                    
                    if (this.value === '' || this.value === null) {
                        showError(this, 'Số lượng không được để trống');
                    } else if (isNaN(parseInt(this.value))) {
                        showError(this, 'Số lượng phải là số');
                    } else if (parseInt(this.value) < 0) {
                        showError(this, 'Số lượng phải lớn hơn hoặc bằng 0');
                    } else {
                        showSuccess(this);
                    }
                });
            }

            imageInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    showSuccess(this);
                } else {
                    showError(this, 'Ảnh đại diện là bắt buộc');
                }
            });
        });
    </script>
