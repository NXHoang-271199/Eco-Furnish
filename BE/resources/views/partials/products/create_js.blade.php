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
            const nameInput = document.getElementById('name');
            const imageInput = document.getElementById('image_thumnail');
            
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
                let hasValue = false;

                // Kiểm tra xem có ít nhất một biến thể được chọn
                variantSelects.forEach(select => {
                    const valueId = select.value;
                    values.push(valueId); // Thêm tất cả giá trị vào mảng, kể cả giá trị rỗng
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
                const priceInput = document.getElementById('variant-price');
                const sku = skuInput.value.trim();
                const price = priceInput.value;

                // Kiểm tra SKU và giá
                if (!sku) {
                    showError(skuInput, 'Vui lòng nhập mã SKU');
                    return;
                }

                // Kiểm tra SKU trùng lặp
                const isDuplicateSku = selectedVariants.some(variant => variant.sku === sku);
                if (isDuplicateSku) {
                    showError(skuInput, 'Mã SKU này đã tồn tại');
                    return;
                }

                if (!price) {
                    showError(priceInput, 'Vui lòng nhập giá');
                    return;
                }

                if (isNaN(price) || parseFloat(price) <= 0) {
                    showError(priceInput, 'Giá phải là số dương');
                    return;
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
                    return;
                }

                const variant = {
                    sku: sku,
                    price: price,
                    values: values
                };

                selectedVariants.push(variant);
                displayVariant(variant);

                // Reset form và xóa thông báo lỗi
                skuInput.value = '';
                priceInput.value = '';
                variantSelects.forEach(select => {
                    select.value = '';
                    clearError(select);
                });
                clearError(skuInput);
                clearError(priceInput);
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

            // Hàm kiểm tra và hiển thị lỗi
            function showError(input, message) {
                // Xóa thông báo lỗi cũ nếu có
                clearError(input);
                
                // Thêm class lỗi cho input
                input.classList.add('is-invalid');

                // Tạo div chứa thông báo lỗi
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback d-block mt-1'; // Thêm d-block để luôn hiển thị
                errorDiv.style.fontSize = '0.875em'; // Kích thước chữ nhỏ hơn
                errorDiv.style.color = '#dc3545'; // Màu đỏ
                errorDiv.textContent = message;

                // Chèn thông báo lỗi sau input
                // Nếu input nằm trong input-group, thêm sau input-group
                const inputGroup = input.closest('.input-group');
                if (inputGroup) {
                    inputGroup.parentNode.insertBefore(errorDiv, inputGroup.nextSibling);
                } else {
                    input.parentNode.insertBefore(errorDiv, input.nextSibling);
                }
            }

            // Hàm xóa thông báo lỗi
            function clearError(input) {
                input.classList.remove('is-invalid');
                // Tìm và xóa tất cả thông báo lỗi liên quan
                const container = input.closest('.form-group') || input.parentNode;
                const errorDivs = container.querySelectorAll('.invalid-feedback');
                errorDivs.forEach(div => div.remove());

                // Xóa thông báo lỗi sau input-group nếu có
                const inputGroup = input.closest('.input-group');
                if (inputGroup) {
                    const nextErrorDiv = inputGroup.nextElementSibling;
                    if (nextErrorDiv && nextErrorDiv.classList.contains('invalid-feedback')) {
                        nextErrorDiv.remove();
                    }
                }
            }

            // Validate tên sản phẩm
            nameInput.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    showError(this, 'Vui lòng nhập tên sản phẩm');
                } else {
                    clearError(this);
                }
            });

            // Validate giá sản phẩm
            priceInput.addEventListener('blur', function() {
                if (!this.value) {
                    showError(this, 'Vui lòng nhập giá sản phẩm');
                } else if (isNaN(this.value) || parseFloat(this.value) <= 0) {
                    showError(this, 'Giá sản phẩm phải là số dương');
                } else {
                    clearError(this);
                }
            });

            // Validate ảnh đại diện
            imageInput.addEventListener('change', function() {
                if (!this.files || !this.files[0]) {
                    showError(this, 'Vui lòng chọn ảnh đại diện');
                } else {
                    const file = this.files[0];
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    
                    if (!validTypes.includes(file.type)) {
                        showError(this, 'Vui lòng chọn file ảnh hợp lệ (JPEG, PNG, GIF)');
                    } else if (file.size > 5 * 1024 * 1024) { // 5MB
                        showError(this, 'Kích thước ảnh không được vượt quá 5MB');
                    } else {
                        clearError(this);
                    }
                }
            });

            // Validate giá khuyến mãi
            function validateDiscountPrice() {
                const price = parseFloat(priceInput.value) || 0;
                const discountPrice = parseFloat(discountPriceInput.value) || 0;
                
                if (discountPrice > price) {
                    showError(discountPriceInput, 'Giá khuyến mãi không được lớn hơn giá gốc');
                    return false;
                } else {
                    clearError(discountPriceInput);
                    return true;
                }
            }

            // Thêm event listener cho input giá
            priceInput.addEventListener('input', validateDiscountPrice);
            discountPriceInput.addEventListener('input', validateDiscountPrice);

            // Validate form khi submit
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                let isValid = true;
                
                // Validate tên sản phẩm
                if (!nameInput.value.trim()) {
                    showError(nameInput, 'Vui lòng nhập tên sản phẩm');
                    isValid = false;
                }
                
                // Validate giá sản phẩm nếu không có biến thể
                if (!document.getElementById('variantToggle').checked) {
                    if (!priceInput.value) {
                        showError(priceInput, 'Vui lòng nhập giá sản phẩm');
                        isValid = false;
                    } else if (isNaN(priceInput.value) || parseFloat(priceInput.value) <= 0) {
                        showError(priceInput, 'Giá sản phẩm phải là số dương');
                        isValid = false;
                    }

                    // Validate giá khuyến mãi
                    if (discountPriceInput.value && !validateDiscountPrice()) {
                        isValid = false;
                    }
                }
                
                // Validate ảnh đại diện
                if (!imageInput.files || !imageInput.files[0]) {
                    showError(imageInput, 'Vui lòng chọn ảnh đại diện');
                    isValid = false;
                }
                
                if (!isValid) {
                    // Cuộn đến trường lỗi đầu tiên
                    const firstError = document.querySelector('.is-invalid');
                    if (firstError) {
                        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return;
                }

                // Submit form nếu validation pass
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';

                fetch(this.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hiển thị thông báo thành công
                        Swal.fire({
                            title: 'Thành công!',
                            text: 'Thêm sản phẩm thành công',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            // Chuyển hướng sau khi hiển thị thông báo
                            window.location.href = '/admin/products';
                        });
                    } else {
                        // Hiển thị lỗi nếu có
                        Object.keys(data.errors || {}).forEach(key => {
                            const input = document.querySelector(`[name="${key}"]`);
                            if (input) {
                                showError(input, data.errors[key][0]);
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'alert alert-danger mt-3';
                    errorMessage.textContent = 'Có lỗi xảy ra khi thêm sản phẩm. Vui lòng thử lại.';
                    form.insertBefore(errorMessage, form.firstChild);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
            });
        });

        $(document).ready(function() {
            // Mảng lưu trữ các SKU đã thêm
            let addedSkus = [];

            // Validate form khi submit
            $('#createProductForm').on('submit', function(e) {
                e.preventDefault();
                
                // Reset thông báo lỗi
                $('.invalid-feedback').remove();
                $('.is-invalid').removeClass('is-invalid');
                
                let isValid = true;
                let errors = {};

                // Validate tên sản phẩm
                if (!$('#name').val()) {
                    isValid = false;
                    errors['name'] = 'Tên sản phẩm là bắt buộc';
                    $('#name').addClass('is-invalid');
                    $('#name').after('<div class="invalid-feedback">Tên sản phẩm là bắt buộc</div>');
                }

                // Validate danh mục
                if (!$('#category_id').val()) {
                    isValid = false;
                    errors['category_id'] = 'Danh mục là bắt buộc';
                    $('#category_id').addClass('is-invalid');
                    $('#category_id').after('<div class="invalid-feedback">Danh mục là bắt buộc</div>');
                }

                // Validate giá gốc nếu không có biến thể
                if ($('.variant-row').length === 0 && !$('#price').val()) {
                    isValid = false;
                    errors['price'] = 'Giá sản phẩm là bắt buộc nếu không có biến thể';
                    $('#price').addClass('is-invalid');
                    $('#price').after('<div class="invalid-feedback">Giá sản phẩm là bắt buộc nếu không có biến thể</div>');
                }

                // Validate ảnh đại diện
                if (!$('#image_thumnail').val() && !$('#image_thumnail').attr('data-value')) {
                    isValid = false;
                    errors['image_thumnail'] = 'Ảnh đại diện là bắt buộc';
                    $('#image_thumnail').addClass('is-invalid');
                    $('#image_thumnail').after('<div class="invalid-feedback">Ảnh đại diện là bắt buộc</div>');
                }

                if (!isValid) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng kiểm tra lại thông tin sản phẩm',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Nếu validation pass thì submit form bằng AJAX
                var form = $(this);
                var url = form.attr('action');
                var formData = new FormData(this);
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: 'Thành công!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = response.redirect;
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Lỗi!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';
                        
                        for (var key in errors) {
                            errorMessage += errors[key][0] + '\n';
                        }
                        
                        Swal.fire({
                            title: 'Lỗi!',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            // Kiểm tra SKU trùng lặp khi thêm biến thể
            $('#addVariantBtn').on('click', function() {
                let sku = $('#variantSku').val();
                
                // Kiểm tra SKU có được nhập không
                if (!sku) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng nhập mã SKU',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Kiểm tra SKU đã tồn tại chưa
                if (addedSkus.includes(sku)) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Mã SKU này đã được sử dụng',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Kiểm tra các trường bắt buộc khác của biến thể
                let price = $('#variantPrice').val();
                let color = $('#variantColor').val();
                let size = $('#variantSize').val();

                if (!price || !color || !size) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng điền đầy đủ thông tin biến thể',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Nếu validation pass thì thêm SKU vào mảng và thêm biến thể
                addedSkus.push(sku);
                addVariant();
            });

            // Xóa SKU khỏi mảng khi xóa biến thể
            $(document).on('click', '.remove-variant', function() {
                let sku = $(this).closest('.variant-row').find('[name="variants[sku][]"]').val();
                addedSkus = addedSkus.filter(item => item !== sku);
                $(this).closest('.variant-row').remove();
            });
        });
    </script>