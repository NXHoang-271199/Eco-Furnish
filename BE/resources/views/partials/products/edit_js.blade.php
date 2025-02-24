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
        let removedImages = []; // Mảng lưu trữ ID của các ảnh đã xóa

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
            
            // Nếu là ảnh đã tồn tại (có data-id), thêm vào danh sách ảnh cần xóa
            const imageId = item.getAttribute('data-id');
            if (imageId) {
                removedImages.push(imageId);
            } else {
                // Nếu là ảnh mới, xóa khỏi galleryFiles
                const index = Array.from(container.children).indexOf(item) - 1; // Trừ 1 vì có nút add-photo
                const dt = new DataTransfer();
                const files = Array.from(galleryFiles.files);
                files.splice(index, 1);
                files.forEach(file => dt.items.add(file));
                galleryFiles = dt;
                galleryInput.files = galleryFiles.files;
            }
            
            // Remove preview
            item.remove();
        }

        $(document).ready(function() {
            const variantsContainer = $('#variants-container');
            const addVariantBtn = $('#add-variant');
            const form = $('#productForm');
            
            // Thêm validate khi blur
            form.find('input[required], textarea[required], select[required]').not('[name^="variants"]').on('blur', function() {
                const field = $(this);
                const value = field.val();
                
                // Xóa thông báo lỗi cũ
                field.removeClass('is-invalid');
                field.next('.invalid-feedback').remove();
                
                if (!value || value.trim() === '') {
                    field.addClass('is-invalid');
                    const fieldLabel = field.prev('label').text().replace(' *', '') || 'Trường này';
                    const errorDiv = $('<div>').addClass('invalid-feedback').text(fieldLabel + ' không được để trống');
                    field.after(errorDiv);
                }
            });

            let variantCount = 0;
            const groupedVariants = {!! json_encode($groupedVariants) !!};
            console.log('Danh sách biến thể theo nhóm:', groupedVariants);

            // Template HTML cho biến thể mới
            function getVariantTemplate(index, existingVariant = null) {
                try {
                    // Kiểm tra dữ liệu biến thể
                    if (!groupedVariants || typeof groupedVariants !== 'object') {
                        console.error('Danh sách biến thể không hợp lệ:', groupedVariants);
                        return null;
                    }

                    let variantHtml = '';
                    let hasValidVariant = false;

                    // Duyệt qua tất cả các biến thể
                    Object.entries(groupedVariants).forEach(([type, variants]) => {
                        if (!Array.isArray(variants) || variants.length === 0) {
                            console.error(`Nhóm biến thể ${type} không hợp lệ:`, variants);
                            return;
                        }

                        const variant = variants[0];
                        if (!variant || !variant.name || !variant.id || !variant.values) {
                            console.error(`Biến thể trong nhóm ${type} thiếu thông tin:`, variant);
                            return;
                        }

                        hasValidVariant = true;

                        // Tạo HTML cho biến thể
                        variantHtml += `
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>${variant.name}</label>
                                    <select class="form-control variant-select" name="variants[${index}][variant_values][${variant.id}]">
                                        <option value="">Chọn ${variant.name.toLowerCase()}</option>`;

                        // Thêm các option cho select
                        if (Array.isArray(variant.values)) {
                            variant.values.forEach(value => {
                                if (value && value.id && value.value) {
                                    let isSelected = false;
                                    if (existingVariant && existingVariant.variant_value_id) {
                                        isSelected = existingVariant.variant_value_id == value.id;
                                    } else if (existingVariant && existingVariant.variant_values) {
                                        isSelected = existingVariant.variant_values[variant.id] == value.id;
                                    }
                                    
                                    variantHtml += `<option value="${value.id}" ${isSelected ? 'selected' : ''}>${value.value}</option>`;
                                }
                            });
                        }

                        variantHtml += `
                                    </select>
                                </div>
                            </div>`;
                    });

                    // Kiểm tra nếu không có biến thể hợp lệ
                    if (!hasValidVariant) {
                        console.error('Không có biến thể nào hợp lệ');
                        return null;
                    }

                    // Tạo template hoàn chỉnh
                    const template = document.createElement('div');
                    template.className = 'variant-combination mb-3';
                    template.innerHTML = `
                        <div class="row">
                            ${variantHtml}
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="variants[${index}][sku]" value="${existingVariant ? existingVariant.sku : ''}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Giá <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="variants[${index}][price]" value="${existingVariant ? existingVariant.price : ''}" required min="0">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm mt-2 remove-variant">
                            <i class="fas fa-trash"></i> Xóa biến thể
                        </button>
                    `;
                    
                    return template;
                } catch (error) {
                    console.error('Lỗi khi tạo template biến thể:', error);
                    return null;
                }
            }

            // Load existing variants if any
            const existingVariants = {!! isset($product->variants) ? json_encode($product->variants) : '[]' !!};
            console.log('Existing variants:', existingVariants);
            
            // Clear container before loading existing variants
            variantsContainer.empty();
            
            // Group variants by SKU
            const groupedBySkuVariants = {};
            if (Array.isArray(existingVariants) && existingVariants.length > 0) {
                existingVariants.forEach(variant => {
                    if (!groupedBySkuVariants[variant.sku]) {
                        groupedBySkuVariants[variant.sku] = {
                            sku: variant.sku,
                            price: variant.price,
                            variant_values: {}
                        };
                    }
                    groupedBySkuVariants[variant.sku].variant_values[variant.variant_id] = variant.variant_value_id;
                });
            }

            // Load grouped variants
            Object.values(groupedBySkuVariants).forEach((variant, index) => {
                console.log('Loading grouped variant:', variant);
                const variantTemplate = getVariantTemplate(index, variant);
                if (variantTemplate) {
                    variantsContainer.append(variantTemplate);
                    variantCount = index + 1;
                }
            });

            // Thêm biến thể mới
            addVariantBtn.on('click', function() {
                const newVariant = getVariantTemplate(variantCount);
                if (newVariant) {
                    variantsContainer.append(newVariant);
                    variantCount++;
                } else {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Không thể thêm biến thể mới. Vui lòng thử lại.',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                }
            });

            // Xóa biến thể
            $(document).on('click', '.remove-variant', function() {
                const variantElement = $(this).closest('.variant-combination');
                
                Swal.fire({
                    title: 'Xác nhận xóa?',
                    text: "Bạn có chắc chắn muốn xóa biến thể này?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Có, xóa!',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        variantElement.remove();
                        updateVariantIndexes();
                    }
                });
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

                // Reset trạng thái validate
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                // Kiểm tra các trường bắt buộc
                const requiredFields = form.find('input[required], textarea[required], select[required]').not('[name^="variants"]');
                let hasError = false;
                let firstErrorField = null;

                // Kiểm tra từng trường bắt buộc
                requiredFields.each(function() {
                    const field = $(this);
                    const value = field.val();
                    
                    if (!value || value.trim() === '') {
                        hasError = true;
                        field.addClass('is-invalid');
                        
                        // Thêm thông báo lỗi
                        const fieldLabel = field.prev('label').text().replace(' *', '') || 'Trường này';
                        const errorDiv = $('<div>').addClass('invalid-feedback').text(fieldLabel + ' không được để trống');
                        field.after(errorDiv);

                        if (!firstErrorField) {
                            firstErrorField = field;
                        }
                    }
                });

                // Nếu có lỗi, hiển thị thông báo và dừng submit
                if (hasError) {
                    // Cuộn đến trường lỗi đầu tiên
                    if (firstErrorField) {
                        $('html, body').animate({
                            scrollTop: firstErrorField.offset().top - 100
                        }, 500);
                    }
                    
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng điền đầy đủ thông tin bắt buộc!',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return false;
                }

                const formData = new FormData(this);
                
                // Xử lý dữ liệu biến thể
                const variants = [];
                let variantHasError = false;

                $('.variant-combination').each(function(index) {
                    const variantElement = $(this);
                    const variantData = {
                        sku: variantElement.find('input[name$="[sku]"]').val(),
                        price: variantElement.find('input[name$="[price]"]').val(),
                        variant_values: {}
                    };

                    // Kiểm tra SKU và giá
                    if (!variantData.sku || !variantData.price) {
                        variantHasError = true;
                        if (!variantData.sku) {
                            const skuInput = variantElement.find('input[name$="[sku]"]');
                            skuInput.addClass('is-invalid');
                            skuInput.after($('<div>').addClass('invalid-feedback d-block').text('SKU không được để trống'));
                        }
                        if (!variantData.price) {
                            const priceInput = variantElement.find('input[name$="[price]"]');
                            priceInput.addClass('is-invalid');
                            priceInput.after($('<div>').addClass('invalid-feedback d-block').text('Giá không được để trống'));
                        }
                        return false;
                    }

                    // Thu thập giá trị biến thể đã chọn (nếu có)
                    variantElement.find('select.variant-select').each(function() {
                        const select = $(this);
                        const value = select.val();
                        if (value) {
                            const variantId = select.attr('name').match(/\[variant_values\]\[(\d+)\]/)[1];
                            variantData.variant_values[variantId] = value;
                        }
                    });

                    variants.push(variantData);
                });

                if (variantHasError) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng điền SKU và giá cho biến thể!',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                // Kiểm tra số lượng biến thể
                if ($('.variant-combination').length > 0 && variants.length === 0) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng thêm ít nhất một biến thể!',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                // Reset formData variants
                for (const key of Array.from(formData.keys())) {
                    if (key.startsWith('variants[')) {
                        formData.delete(key);
                    }
                }

                // Thêm dữ liệu biến thể mới vào formData
                variants.forEach((variant, index) => {
                    formData.append(`variants[${index}][sku]`, variant.sku);
                    formData.append(`variants[${index}][price]`, variant.price);
                    
                    Object.entries(variant.variant_values).forEach(([variantId, valueId]) => {
                        formData.append(`variants[${index}][variant_values][${variantId}]`, valueId);
                    });
                });

                // Log dữ liệu để debug
                console.log('Variants data:', variants);
                console.log('Form data entries:');
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ':', pair[1]);
                }

                // Thêm danh sách ảnh đã xóa
                formData.append('removed_images', JSON.stringify(removedImages));
                
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true);
                submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
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
                                confirmButtonText: 'Đóng'
                            });
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Swal.fire({
                            title: 'Lỗi!',
                            text: response.message || 'Có lỗi xảy ra khi cập nhật sản phẩm',
                            icon: 'error',
                            confirmButtonText: 'Đóng'
                        });
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalText);
                    }
                });
            });
        });
    </script>