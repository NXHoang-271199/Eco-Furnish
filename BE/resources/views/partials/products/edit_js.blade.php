   <!-- Sweet Alerts js -->
   <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @include('partials.products.edit_css')

    <script>

        // Preview thumbnail image
        const imageThumbInput = document.getElementById('image_thumnail');
        if (imageThumbInput) {
            imageThumbInput.addEventListener('change', function(e) {
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
        }

        function removeThumbnail() {
            const preview = document.getElementById('thumbnailPreview');
            const input = document.getElementById('image_thumnail');
            preview.style.display = 'none';
            preview.querySelector('img').src = '';
            input.value = '';
        }

        // Preview gallery images
        let galleryFiles = new DataTransfer(); // Biến lưu trữ tất cả files
        let removedImages = []; // Mảng lưu trữ ID của các ảnh đã xóa

        const galleryInput = document.getElementById('gallery');
        if (galleryInput) {
            galleryInput.addEventListener('change', function(e) {
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
        }

        // Remove gallery item
        function removeGalleryItem(element) {
            const item = element.parentElement;
            const container = item.parentElement;
            
            // Nếu là ảnh đã tồn tại (có data-id), thêm vào danh sách ảnh cần xóa
            const imageId = item.getAttribute('data-id');
            if (imageId) {
                removedImages.push(imageId);
            } else {
                // Nếu là ảnh mới, xóa khỏi galleryFiles
                try {
                    const galleryInput = document.getElementById('gallery');
                    if (galleryInput) {
                        const index = Array.from(container.children).indexOf(item) - (container.querySelector('.add-photo') ? 1 : 0);
                        
                        if (index >= 0 && galleryFiles && galleryFiles.files) {
                            // Remove from galleryFiles
                            const dt = new DataTransfer();
                            const files = Array.from(galleryFiles.files);
                            files.splice(index, 1);
                            files.forEach(file => dt.items.add(file));
                            galleryFiles = dt;
                            galleryInput.files = galleryFiles.files;
                        }
                    }
                } catch (error) {
                    console.error('Lỗi khi xóa file:', error);
                }
            }
            
            // Remove preview
            item.remove();
        }

        $(document).ready(function() {
            // Khởi tạo biến
            const variantTypeSelect = $('#variantTypeSelect');
            const addVariantTypeBtn = $('#addVariantTypeBtn');
            const selectedVariantTypes = $('#selectedVariantTypes');
            const variantValuesContainer = $('#variant-values-container');
            const variantsContainer = $('#variants-container');
            const addVariantBtn = $('#add-variant');
            const form = $('#productForm');
            const nameInput = $('#name');
            const categorySelect = $('#category_id');
            const priceInput = $('#price');
            const discountPriceInput = $('#discount_price');
            const imageInput = $('#image_thumnail');
            const descriptionInput = $('#description');
            const variantToggle = $('#variantToggle');
            const variantSection = $('#variantSection');
            const hasVariantsInput = $('#hasVariants');
            let selectedTypes = [];
            let selectedVariants = []; // Thêm biến để lưu trữ các biến thể đã chọn
            let isAddingVariant = false; // Thêm biến kiểm soát việc thêm biến thể
            
            // Biến theo dõi trạng thái cuộn đến lỗi
            window.hasScrolledToError = false;

            // Khởi tạo validate realtime
            initializeRealTimeValidation();

            // Thêm sự kiện change cho select thuộc tính để ẩn thông báo lỗi khi người dùng chọn thuộc tính
            variantTypeSelect.on('change', function() {
                const selectedValue = $(this).val();
                if (selectedValue) {
                    // Nếu đã chọn thuộc tính, xóa thông báo lỗi
                    $(this).removeClass('is-invalid');
                    $(this).parent().next('.invalid-feedback').remove();
                }
            });

            // Xử lý toggle biến thể
            variantToggle.on('change', function() {
                // Ẩn/hiện trường số lượng
                const quantitySection = $('#quantitySection');
                // Lấy các phần giá cơ bản và giá khuyến mãi
                const priceSection = $('#priceSection');
                const discountPriceSection = $('#discountPriceSection');
                
                if (this.checked) {
                    // Hiển thị phần biến thể
                    variantSection.slideDown(300);
                    hasVariantsInput.val('1');
                    isAddingVariant = true;
                    
                    // Ẩn trường số lượng, giá gốc và giá khuyến mãi khi bật biến thể
                    quantitySection.hide();
                    priceSection.hide();
                    discountPriceSection.hide();
                    
                    // Ẩn thông báo lỗi của trường số lượng và reset validation state
                    clearValidation($('#quantity'));
                    $('#quantity-error').hide();
                    $('#quantity').val(''); // Xóa giá trị số lượng
                } else {
                    // Ẩn phần biến thể
                    variantSection.slideUp(300);
                    hasVariantsInput.val('0');
                    isAddingVariant = false;
                    
                    // Hiển thị trường số lượng, giá gốc và giá khuyến mãi khi tắt biến thể
                    quantitySection.show();
                    priceSection.show();
                    discountPriceSection.show();
                    
                    // Hiển thị thông báo xác nhận nếu đã có biến thể
                    if (selectedTypes.length > 0 || selectedVariants.length > 0) {
                        Swal.fire({
                            title: 'Xác nhận',
                            text: 'Khi tắt chế độ biến thể, tất cả các biến thể đã tạo sẽ bị xóa. Bạn có chắc chắn muốn tiếp tục?',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Đồng ý',
                            cancelButtonText: 'Hủy',
                            confirmButtonColor: '#3b5998'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Xóa tất cả biến thể
                                resetVariantForm();
                            } else {
                                // Bật lại toggle
                                variantToggle.prop('checked', true);
                                variantSection.slideDown(300);
                                hasVariantsInput.val('1');
                                isAddingVariant = true;
                                
                                // Ẩn lại trường số lượng, giá gốc và giá khuyến mãi
                                quantitySection.hide();
                                priceSection.hide();
                                discountPriceSection.hide();
                            }
                        });
                    }
                }
            });

            // Hàm reset form biến thể
            function resetVariantForm() {
                // Xóa tất cả biến thể đã chọn
                selectedTypes = [];
                selectedVariants = [];
                selectedVariantTypes.empty();
                variantsContainer.empty();
                
                // Ẩn các phần liên quan
                $('#generate-variants-container').hide();
                $('#generate-variants-btn').hide();
                $('#variantForm').addClass('d-none');
                variantValuesContainer.addClass('d-none');
                
                // Cập nhật UI
                updateSelectedVariantTypesUI();
            }

            // Cập nhật giao diện nút tạo biến thể tự động
            $('#generate-variants-btn').html('<i class="fas fa-magic me-2"></i>Tạo biến thể tự động');

            // Khởi tạo trạng thái hiển thị của phần biến thể dựa vào toggle
            if (!variantToggle.is(':checked')) {
                variantSection.hide();
                hasVariantsInput.val('0');
                isAddingVariant = false;
                
                // Hiển thị trường số lượng, giá gốc và giá khuyến mãi khi không có biến thể
                $('#quantitySection').show();
                $('#priceSection').show();
                $('#discountPriceSection').show();
            } else {
                variantSection.show();
                hasVariantsInput.val('1');
                isAddingVariant = true;
                
                // Ẩn trường số lượng, giá gốc và giá khuyến mãi khi có biến thể
                $('#quantitySection').hide();
                $('#priceSection').hide();
                $('#discountPriceSection').hide();
            }

            // Load biến thể hiện có
            const existingVariants = {!! isset($product->variants) ? json_encode($product->variants) : '[]' !!};
            console.log('Existing variants:', existingVariants);

            // Nhóm biến thể theo SKU
            const groupedVariants = {};
            existingVariants.forEach(variant => {
                if (!groupedVariants[variant.sku]) {
                    groupedVariants[variant.sku] = {
                        sku: variant.sku,
                        price: variant.price,
                        discount_price: variant.discount_price,
                        quantity: variant.quantity,
                        values: {}
                    };
                }
                groupedVariants[variant.sku].values[variant.variant_id] = variant.variant_value_id;
            });

            // Debug function
            function logSelectedTypes(message) {
                console.log(message, selectedTypes.map(id => Number(id)));
            }

            // Thêm loại biến thể
            addVariantTypeBtn.on('click', function() {
                const selectedOption = variantTypeSelect.find('option:selected');
                const variantId = Number(selectedOption.val());
                const variantName = selectedOption.text();
                
                // Reset các thông báo lỗi trước đó
                variantTypeSelect.removeClass('is-invalid');
                variantTypeSelect.parent().next('.invalid-feedback').remove();
                
                if (!variantId) {
                    showError(variantTypeSelect, 'Vui lòng chọn thuộc tính');
                    return;
                }

                isAddingVariant = true; // Đánh dấu đang thêm biến thể
                logSelectedTypes('Current selected types:');
                if (selectedTypes.includes(variantId)) {
                    Swal.fire({
                        title: 'Thông báo',
                        text: 'Thuộc tính này đã được thêm',
                        icon: 'warning',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                // Hiển thị modal chọn giá trị thuộc tính
                const variantValues = JSON.parse(selectedOption.attr('data-values'));
                
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
                    customClass: {
                        container: 'variant-modal-container',
                        popup: 'variant-modal-popup',
                        content: 'variant-modal-content'
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Thêm',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#405189',
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
                        selectedTypes.push(variantId);
                        logSelectedTypes('After adding new type:');

                        // Tạo container cho loại biến thể
                        const variantTypeContainer = document.createElement('div');
                        variantTypeContainer.className = 'selected-variant-type p-3 mb-3 border rounded';
                        variantTypeContainer.dataset.variantId = variantId;

                        // Tạo header cho loại biến thể
                        const header = document.createElement('div');
                        header.className = 'variant-header d-flex justify-content-between align-items-center';
                        header.innerHTML = `
                            <h6 class="mb-0 text-primary">${variantName}</h6>
                            <button type="button" class="btn-remove-variant" onclick="removeVariantType('${variantId}')">
                                <i class="fas fa-times"></i>
                            </button>
                        `;

                        // Tạo phần hiển thị giá trị đã chọn
                        const valueDisplay = document.createElement('div');
                        valueDisplay.className = 'variant-values mt-3';
                        
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

                        selectedVariantTypes.append(variantTypeContainer);
                        
                        // Hiển thị nút tạo biến thể tự động
                        $('#generate-variants-container').show();
                        $('#generate-variants-btn').show();

                        // Reset select
                        variantTypeSelect.val('');
                        
                        // Cập nhật UI
                        updateSelectedVariantTypesUI();
                        
                        // Cập nhật danh sách select
                        updateVariantTypeSelect();
                    }
                });
            });

            // Xóa loại biến thể
            window.removeVariantType = function(variantId) {
                const container = $(`.selected-variant-type[data-variant-id="${variantId}"]`);
                if (container.length) {
                    container.remove();
                    selectedTypes = selectedTypes.filter(type => type !== Number(variantId));
                    
                    // Ẩn form nếu không còn loại biến thể nào
                    if (selectedTypes.length === 0) {
                        $('#generate-variants-btn').hide();
                        isAddingVariant = false;
                    }
                    
                    // Reset select để có thể chọn lại
                    variantTypeSelect.val('');
                    
                    // Cập nhật danh sách select
                    updateVariantTypeSelect();
                }
            };
            
            // Thêm hàm cập nhật danh sách select
            function updateVariantTypeSelect() {
                // Cập nhật trạng thái disabled cho các option
                variantTypeSelect.find('option').each(function() {
                    const optionValue = $(this).val();
                    if (optionValue) {
                        // Kiểm tra xem thuộc tính này đã được chọn chưa
                        const isSelected = selectedTypes.includes(Number(optionValue));
                        $(this).prop('disabled', isSelected);
                    }
                });
                
                // Reset giá trị select
                variantTypeSelect.val('');
            }
            
            // Cập nhật giao diện cho phần chọn thuộc tính
            if (!$('#variantTypeSelect').parent().hasClass('variant-type-select')) {
                $('#variantTypeSelect').wrap('<div class="variant-type-select d-flex gap-2"></div>');
                $('#addVariantTypeBtn').appendTo($('#variantTypeSelect').parent());
            }
            
            // Thêm class và style cho các container
            $('#selectedVariantTypes').addClass('variant-info-section p-3 mb-4');
            $('#variant-values-container').addClass('variant-info-section p-3');
            
            // Cải thiện giao diện cho phần hiển thị thuộc tính đã chọn
            function updateSelectedVariantTypesUI() {
                if (selectedTypes.length > 0) {
                    $('#selectedVariantTypes').show();
                    if (!$('#selectedVariantTypes').find('.variant-types-header').length) {
                        $('#selectedVariantTypes').prepend(`
                            <div class="variant-types-header mb-3">
                                <h6 class="mb-2 text-primary">Thuộc tính đã chọn</h6>
                                <p class="text-muted small mb-0">Các thuộc tính và giá trị dưới đây sẽ được sử dụng để tạo biến thể sản phẩm</p>
                            </div>
                        `);
                    }
                } else {
                    $('#selectedVariantTypes').hide();
                }
            }
            
            // Gọi hàm cập nhật UI ban đầu
            updateSelectedVariantTypesUI();

            // Thêm biến thể mới
            addVariantBtn.on('click', function() {
                // Kiểm tra xem có thuộc tính nào được chọn chưa
                if (selectedTypes.length === 0) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng chọn ít nhất một thuộc tính biến thể',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }

                // Hiển thị form thêm biến thể
                $('#variantForm').removeClass('d-none');
                
                // Tạo các select cho từng loại biến thể
                let variantValuesHTML = '';
                selectedTypes.forEach(variantId => {
                    const variantOption = variantTypeSelect.find(`option[value="${variantId}"]`);
                    const variantName = variantOption.text();
                    const variantValues = JSON.parse(variantOption.attr('data-values'));
                    
                    variantValuesHTML += `
                        <div class="col-md-4 mb-3">
                            <label class="form-label">${variantName} <span class="text-danger">*</span></label>
                            <select class="form-select variant-value-select" data-variant-id="${variantId}">
                                <option value="">Chọn ${variantName}</option>
                                ${variantValues.map(value => `
                                    <option value="${value.id}">${value.value}</option>
                                `).join('')}
                            </select>
                            <div class="invalid-feedback">Vui lòng chọn giá trị</div>
                        </div>
                    `;
                });
                
                // Cập nhật HTML
                variantValuesContainer.html(variantValuesHTML);
                variantValuesContainer.removeClass('d-none');
            });

            // Cải thiện giao diện cho form thêm biến thể
            $('#variantForm').addClass('variant-info-section');
            
            // Cải thiện giao diện cho phần hiển thị biến thể đã tạo
            function updateVariantsContainerUI() {
                if ($('#variants-container').children().length > 0) {
                    if (!$('#variants-container').find('.variants-header').length) {
                        $('#variants-container').prepend(`
                            <div class="variants-header mb-3">
                                <h6 class="mb-2 text-primary">Biến thể đã tạo</h6>
                                <p class="text-muted small mb-0">Nhấp vào biến thể để chỉnh sửa thông tin</p>
                            </div>
                        `);
                    }
                } else {
                    $('#variants-container').find('.variants-header').remove();
                }
            }
            
            // Cập nhật hàm hiển thị biến thể
            function displayVariant(variant, index) {
                const variantIndex = index !== undefined ? index : selectedVariants.length - 1;
                const variantId = 'variant_' + variantIndex;
                
                console.log(`Hiển thị biến thể #${variantIndex + 1} với ID: ${variantId}`, variant);
                
                // Tạo chuỗi hiển thị các giá trị biến thể
                const variantValuesDisplay = Object.entries(variant.values).map(([variantId, valueId]) => {
                    const variantOption = variantTypeSelect.find(`option[value="${variantId}"]`);
                    const variantName = variantOption.text();
                    const variantValues = JSON.parse(variantOption.attr('data-values'));
                    const selectedValue = variantValues.find(v => v.id == valueId);
                    return `${variantName}: ${selectedValue ? selectedValue.value : 'N/A'}`;
                }).join(' - ');
                
                const variantElement = $(`
                    <div class="variant-item mb-4 border rounded" data-variant-id="${variantId}" data-variant-index="${variantIndex}">
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
                                        <label class="form-label text-muted mb-1">Giá KM</label>
                                        <p class="mb-0 fw-medium variant-discount-price-display">${variant.discount_price ? parseInt(variant.discount_price).toLocaleString('vi-VN') + ' VNĐ' : 'Không có'}</p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="variant-info">
                                        <label class="form-label text-muted mb-1">Số lượng</label>
                                        <p class="mb-0 fw-medium variant-quantity-display">${variant.quantity || 'Chưa có'}</p>
                            </div>
                        </div>
                                <div class="col-md-4">
                                    <div class="variant-info">
                                        <label class="form-label text-muted mb-1">Thông tin biến thể</label>
                                        <p class="mb-0 fw-medium">${variantValuesDisplay}</p>
                            </div>
                                    </div>
                                </div>
                        </div>
                        <div class="variant-edit p-3 bg-light border-top" style="display: none;">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">SKU <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control variant-sku-input" value="${variant.sku || ''}">
                                    <div class="invalid-feedback">Vui lòng nhập SKU</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Giá <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" class="form-control variant-price-input" value="${variant.price || ''}" min="0">
                                        <span class="input-group-text">VNĐ</span>
                                        <div class="invalid-feedback">Giá phải lớn hơn 0</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Giá khuyến mãi</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control variant-discount-price-input" value="${variant.discount_price || ''}" min="0">
                                        <span class="input-group-text">VNĐ</span>
                                        <div class="invalid-feedback">Giá KM phải nhỏ hơn giá gốc</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control variant-quantity-input" value="${variant.quantity || ''}" min="0">
                                    <div class="invalid-feedback">Số lượng phải lớn hơn 0</div>
                                </div>
                            <div class="mt-3 text-end">
                                <button type="button" class="btn btn-secondary me-2 cancel-variant-edit">Hủy</button>
                                <button type="button" class="btn btn-primary save-variant-edit" data-variant-id="${variantId}">Lưu</button>
                            </div>
                                </div>
                        <div class="variant-hidden-inputs">
                            <input type="hidden" name="variants[${variantIndex}][sku]" class="variant-sku-hidden" value="${variant.sku || ''}">
                            <input type="hidden" name="variants[${variantIndex}][price]" class="variant-price-hidden" value="${variant.price || ''}">
                            <input type="hidden" name="variants[${variantIndex}][discount_price]" class="variant-discount-price-hidden" value="${variant.discount_price || ''}">
                            <input type="hidden" name="variants[${variantIndex}][quantity]" class="variant-quantity-hidden" value="${variant.quantity || ''}">
                            ${Object.entries(variant.values).map(([variantId, valueId]) => 
                                `<input type="hidden" name="variants[${variantIndex}][values][${variantId}]" value="${valueId}">`
                            ).join('')}
                            </div>
                        </div>
                `);

                // Thêm vào container
                $('#variants-container').append(variantElement);
                
                // Thêm sự kiện input cho các trường trong biến thể
                attachInputEventsToVariant(variantElement);
                
                // Cập nhật UI
                updateVariantsContainerUI();
            }
            
            // Thêm sự kiện input cho các trường trong biến thể
            function attachInputEventsToVariant(variantElement) {
                const skuInput = variantElement.find('.variant-sku-input');
                const priceInput = variantElement.find('.variant-price-input');
                const discountPriceInput = variantElement.find('.variant-discount-price-input');
                const quantityInput = variantElement.find('.variant-quantity-input');
                const variantIndex = parseInt(variantElement.data('variant-index'));
                
                // Thêm sự kiện input cho SKU
                skuInput.off('input').on('input', function() {
                    if (!$(this).val().trim()) {
                        showError($(this), 'Vui lòng nhập SKU');
                    } else if (checkDuplicateSku($(this).val().trim(), variantIndex)) {
                        showError($(this), 'SKU này đã tồn tại');
                    } else {
                        showSuccess($(this));
                    }
                });
                
                // Thêm sự kiện input cho giá
                priceInput.off('input').on('input', function() {
                    const value = parseFloat($(this).val());
                    if (!$(this).val() || isNaN(value) || value <= 0) {
                        showError($(this), 'Giá phải lớn hơn 0');
                    } else if (value > 999999999) {
                        showError($(this), 'Giá không được lớn hơn 999.999.999 VNĐ');
                    } else {
                        showSuccess($(this));
                        // Kiểm tra giá khuyến mãi
                        validateVariantDiscountPrice(discountPriceInput, $(this).val());
                    }
                });
                
                // Thêm sự kiện input cho giá khuyến mãi
                discountPriceInput.off('input').on('input', function() {
                    validateVariantDiscountPrice($(this), priceInput.val());
                });
                
                // Thêm sự kiện input cho số lượng
                quantityInput.off('input').on('input', function() {
                    const value = parseInt($(this).val());
                    if (!$(this).val() || isNaN(value) || value < 0) {
                        showError($(this), 'Số lượng không được âm');
                    } else {
                        showSuccess($(this));
                    }
                });
            }

            // Hàm kiểm tra giá khuyến mãi của biến thể
            function validateVariantDiscountPrice(discountPriceInput, originalPrice) {
                // Nếu không có giá khuyến mãi, không cần validate
                if (!discountPriceInput.val().trim()) {
                    clearValidation(discountPriceInput);
                    return true;
                }
                
                const discountPrice = parseFloat(discountPriceInput.val());
                const price = parseFloat(originalPrice);
                
                if (isNaN(price) || price <= 0) {
                    showError(discountPriceInput, 'Vui lòng nhập giá gốc hợp lệ trước');
                    return false;
                }
                
                if (discountPrice >= price) {
                    showError(discountPriceInput, 'Giá khuyến mãi phải nhỏ hơn giá gốc');
                    return false;
                } else {
                    showSuccess(discountPriceInput);
                    return true;
                }
            }

            // Cập nhật hàm xóa biến thể
            window.removeVariant = function(variantId, event) {
                if (event) {
                    event.stopPropagation();
                }
                
                Swal.fire({
                    title: 'Xác nhận xóa',
                    text: 'Bạn có chắc chắn muốn xóa biến thể này?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Xóa',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#f06548'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const variantElement = $(`[data-variant-id="${variantId}"]`);
                        if (variantElement.length) {
                            const variantIndex = parseInt(variantElement.data('variant-index'));
                            if (isNaN(variantIndex) || variantIndex < 0 || variantIndex >= selectedVariants.length) {
                                console.error('Không tìm thấy biến thể với index:', variantIndex);
                                return;
                            }
                            
                            // Xóa biến thể khỏi mảng
                            selectedVariants.splice(variantIndex, 1);
                            variantElement.remove();
                            
                            // Cập nhật lại index và hiển thị cho tất cả các biến thể còn lại
                            updateVariantIndexes();
                            
                            // Cập nhật UI
                            updateVariantsContainerUI();
                        }
                    }
                });
            };
            
            // Mở/đóng form chỉnh sửa biến thể
            window.toggleVariantEdit = function(variantId) {
                const variantElement = $(`[data-variant-id="${variantId}"]`);
                if (variantElement.length) {
                    const editForm = variantElement.find('.variant-edit');
                    if (editForm.css('display') === 'none') {
                        // Đóng tất cả các form chỉnh sửa khác trước khi mở form mới
                        $('.variant-edit').not(editForm).hide();
                        editForm.slideDown(300);
                        
                        // Gắn sự kiện click cho nút lưu và hủy
                        editForm.find('.save-variant-edit').off('click').on('click', function() {
                            window.saveVariantEdit(variantId);
                        });
                        
                        editForm.find('.cancel-variant-edit').off('click').on('click', function() {
                            editForm.slideUp(300);
                        });
                    } else {
                        editForm.slideUp(300);
                    }
                }
            };
            
            // Hàm kiểm tra SKU trùng lặp
            function checkDuplicateSku(sku, currentIndex) {
                // Kiểm tra trong các biến thể khác
                for (let i = 0; i < selectedVariants.length; i++) {
                    if (i !== currentIndex && selectedVariants[i].sku === sku) {
                        return true; // Tìm thấy SKU trùng lặp
                    }
                }
                return false; // Không tìm thấy SKU trùng lặp
            }

            // Lưu thông tin chỉnh sửa biến thể
            window.saveVariantEdit = function(variantId) {
                console.log('Đang lưu biến thể với ID:', variantId);
                
                // Đảm bảo xóa thông báo lỗi của trường discount_price
                $('#discount_price').removeClass('is-invalid is-valid');
                $('#discount_price').siblings('.invalid-feedback').hide().empty();
                
                const variantElement = $(`[data-variant-id="${variantId}"]`);
                if (!variantElement.length) {
                    console.error('Không tìm thấy phần tử biến thể với ID:', variantId);
                    return;
                }

                const variantIndex = parseInt(variantElement.data('variant-index'));
                if (isNaN(variantIndex) || variantIndex < 0 || variantIndex >= selectedVariants.length) {
                    console.error('Không tìm thấy biến thể với index:', variantIndex);
                    return;
                }

                // Lấy giá trị từ form
                const skuInput = variantElement.find('.variant-sku-input');
                const priceInput = variantElement.find('.variant-price-input');
                const discountPriceInput = variantElement.find('.variant-discount-price-input');
                const quantityInput = variantElement.find('.variant-quantity-input');
                
                console.log('Giá trị hiện tại:', {
                    sku: skuInput.val(),
                    price: priceInput.val(),
                    discount_price: discountPriceInput.val(),
                    quantity: quantityInput.val()
                });
                
                // Validate
                let isValid = true;
                
                // Validate SKU
                if (!skuInput.val().trim()) {
                    showError(skuInput, 'Vui lòng nhập SKU');
                    isValid = false;
                } else if (checkDuplicateSku(skuInput.val().trim(), variantIndex)) {
                    showError(skuInput, 'SKU này đã tồn tại');
                    isValid = false;
                } else {
                    showSuccess(skuInput);
                }
                
                // Validate giá
                const price = parseFloat(priceInput.val());
                if (!priceInput.val() || isNaN(price) || price <= 0) {
                    showError(priceInput, 'Giá phải lớn hơn 0');
                    isValid = false;
                } else if (price > 999999999) {
                    showError(priceInput, 'Giá không được lớn hơn 999.999.999 VNĐ');
                    isValid = false;
                } else {
                    showSuccess(priceInput);
                }
                
                // Validate giá khuyến mãi nếu có
                if (discountPriceInput.val().trim()) {
                    const discountPrice = parseFloat(discountPriceInput.val());
                    if (discountPrice >= price) {
                        showError(discountPriceInput, 'Giá khuyến mãi phải nhỏ hơn giá gốc');
                        isValid = false;
                    } else {
                        showSuccess(discountPriceInput);
                    }
                }
                
                // Validate số lượng
                const quantity = parseInt(quantityInput.val());
                if (!quantityInput.val() || isNaN(quantity) || quantity < 0) {
                    showError(quantityInput, 'Số lượng không được âm');
                    isValid = false;
                } else {
                    showSuccess(quantityInput);
                }
                
                if (!isValid) {
                    console.error('Dữ liệu không hợp lệ');
                    return;
                }

                // Cập nhật giá trị trong mảng
                const variantIndexValue = parseInt(variantIndex);
                if (!isNaN(variantIndexValue) && variantIndexValue >= 0 && variantIndexValue < selectedVariants.length) {
                    // Cập nhật dữ liệu trong mảng
                    selectedVariants[variantIndexValue].sku = skuInput.val().trim();
                    selectedVariants[variantIndexValue].price = priceInput.val();
                    selectedVariants[variantIndexValue].discount_price = discountPriceInput.val() || null;
                    selectedVariants[variantIndexValue].quantity = quantityInput.val();
                    
                    console.log(`Đã cập nhật biến thể #${variantIndexValue + 1}:`, selectedVariants[variantIndexValue]);
                } else {
                    console.error('Không tìm thấy biến thể với index:', variantIndexValue);
                }
                
                // Cập nhật hiển thị
                variantElement.find('.variant-sku-display').text(selectedVariants[variantIndex].sku || 'Chưa có');
                variantElement.find('.variant-price-display').text(
                    selectedVariants[variantIndex].price ? 
                    parseInt(selectedVariants[variantIndex].price).toLocaleString('vi-VN') + ' VNĐ' : 
                    'Chưa có'
                );
                variantElement.find('.variant-discount-price-display').text(
                    selectedVariants[variantIndex].discount_price ? 
                    parseInt(selectedVariants[variantIndex].discount_price).toLocaleString('vi-VN') + ' VNĐ' : 
                    'Không có'
                );
                variantElement.find('.variant-quantity-display').text(selectedVariants[variantIndex].quantity || 'Chưa có');
                
                // Cập nhật input hidden
                variantElement.find('.variant-sku-hidden').val(selectedVariants[variantIndex].sku);
                variantElement.find('.variant-price-hidden').val(selectedVariants[variantIndex].price);
                variantElement.find('.variant-discount-price-hidden').val(selectedVariants[variantIndex].discount_price || '');
                variantElement.find('.variant-quantity-hidden').val(selectedVariants[variantIndex].quantity);
                
                // Đóng form
                toggleVariantEdit(variantId);
            };
            
            // Hàm cập nhật lại index cho các biến thể
            function updateVariantIndexes() {
                $('#variants-container .variant-item').each(function(index) {
                    const variantId = $(this).data('variant-id');
                    $(this).data('variant-index', index);
                    $(this).attr('data-variant-index', index);
                    
                    // Cập nhật tiêu đề
                    $(this).find('h6').text(`Biến thể #${index + 1}`);
                    
                    // Cập nhật name cho các input hidden
                    $(this).find('.variant-hidden-inputs input').each(function() {
                        const name = $(this).attr('name');
                        if (name) {
                            const newName = name.replace(/variants\[\d+\]/, `variants[${index}]`);
                            $(this).attr('name', newName);
                        }
                    });
                });
            }
            
            // Hiển thị các biến thể hiện có
            if (Object.keys(groupedVariants).length > 0) {
                // Chuyển đổi từ object sang array
                const variantsArray = Object.values(groupedVariants);
                
                // Thêm vào mảng selectedVariants
                selectedVariants = variantsArray;
                
                // Hiển thị từng biến thể
                variantsArray.forEach((variant, index) => {
                    displayVariant(variant, index);
                });
                
                // Gắn sự kiện cho các biến thể
                attachEventsToGeneratedVariants();
            }

            // Cập nhật hàm validate realtime
            function initializeRealTimeValidation() {
                // Event listener cho trường tên sản phẩm
                nameInput.on('input blur', function() {
                    if ($(this).val().trim()) {
                        showSuccess($(this));
                    } else {
                        showError($(this), 'Vui lòng nhập tên sản phẩm');
                    }
                });
                
                // Event listener cho trường danh mục
                categorySelect.on('change blur', function() {
                    if ($(this).val()) {
                        showSuccess($(this));
                    } else {
                        showError($(this), 'Vui lòng chọn danh mục');
                    }
                });
                
                // Event listener cho trường giá cơ bản
                priceInput.off('input blur').on('input blur', function() {
                    validatePrice();
                });
                
                // Event listener cho trường giá khuyến mãi
                discountPriceInput.off('input blur').on('input blur', function() {
                    validateDiscountPrice();
                });
                
                // Xóa tất cả các thông báo lỗi cũ cho giá cơ bản và giá khuyến mãi
                removeAllFeedbackMessages(priceInput);
                removeAllFeedbackMessages(discountPriceInput);
                
                // Event listener cho trường số lượng
                $('#quantity').on('input blur', function() {
                    // Nếu đang ở chế độ biến thể, không cần validate
                    if (variantToggle.is(':checked')) {
                        clearValidation($(this));
                        return;
                    }
                    
                    if ($(this).val() === '' || $(this).val() === null) {
                        showError($(this), 'Số lượng không được để trống');
                        $('#quantity-error').text('Số lượng không được để trống').show();
                    } else if (isNaN(parseInt($(this).val()))) {
                        showError($(this), 'Số lượng phải là số');
                        $('#quantity-error').text('Số lượng phải là số').show();
                    } else if (parseInt($(this).val()) < 0) {
                        showError($(this), 'Số lượng phải lớn hơn hoặc bằng 0');
                        $('#quantity-error').text('Số lượng phải lớn hơn hoặc bằng 0').show();
                    } else {
                        showSuccess($(this));
                        $('#quantity-error').hide();
                    }
                });
                
                // Event listener cho trường ảnh đại diện
                imageInput.on('change', function() {
                    const preview = $('#thumbnailPreview');
                    if (this.files && this.files[0] || preview.find('img').attr('src')) {
                        showSuccess($(this));
                    } else {
                        showError($(this), 'Vui lòng chọn ảnh đại diện');
                    }
                });
                
                // Validate trạng thái ban đầu của các trường
                validateInitialState();
            }
            
            // Hàm validate giá cơ bản
            function validatePrice() {
                // Xóa tất cả các thông báo lỗi cũ trước
                removeAllFeedbackMessages(priceInput);
                
                const price = parseFloat(priceInput.val());
                if (!priceInput.val() || isNaN(price) || price <= 0) {
                    showErrorMessage(priceInput, 'Giá cơ bản phải lớn hơn 0');
                    return false;
                } else if (price > 999999999) {
                    showErrorMessage(priceInput, 'Giá cơ bản không được lớn hơn 999.999.999 VNĐ');
                    return false;
                } else {
                    showSuccessForPrice(priceInput);
                    // Validate lại giá khuyến mãi khi giá cơ bản thay đổi
                    validateDiscountPrice();
                    return true;
                }
            }
            
            // Hàm validate giá khuyến mãi
            function validateDiscountPrice() {
                // Xóa tất cả các thông báo lỗi cũ trước
                removeAllFeedbackMessages(discountPriceInput);
                
                if (!discountPriceInput.val().trim()) {
                    clearValidation(discountPriceInput);
                    return true;
                }
                
                const discountPrice = parseInt(discountPriceInput.val());
                const basePrice = parseInt(priceInput.val());
                
                if (isNaN(basePrice) || basePrice <= 0) {
                    showErrorMessage(discountPriceInput, 'Vui lòng nhập giá cơ bản hợp lệ trước');
                    return false;
                }
                
                if (discountPrice >= basePrice) {
                    showErrorMessage(discountPriceInput, 'Giá khuyến mãi phải nhỏ hơn giá cơ bản');
                    return false;
                } else {
                    showSuccessForPrice(discountPriceInput);
                    return true;
                }
            }
            
            // Hàm validate trạng thái ban đầu của các trường
            function validateInitialState() {
                // Validate tên sản phẩm
                if (nameInput.val().trim()) {
                    showSuccess(nameInput);
                }
                
                // Validate danh mục
                if (categorySelect.val()) {
                    showSuccess(categorySelect);
                }
                
                // Validate giá cơ bản
                validatePrice();
                
                // Validate giá khuyến mãi
                if (discountPriceInput.val().trim()) {
                    validateDiscountPrice();
                }
                
                // Validate số lượng (chỉ khi không có biến thể)
                if (!variantToggle.is(':checked')) {
                    const quantityInput = $('#quantity');
                    if (quantityInput.val() && parseInt(quantityInput.val()) >= 0) {
                        showSuccess(quantityInput);
                    }
                }
                
                // Validate ảnh đại diện
                const preview = $('#thumbnailPreview');
                if (preview.find('img').attr('src')) {
                    showSuccess(imageInput);
                }
            }

            // Thêm hàm tạo biến thể tự động
            function handleGenerateVariants() {
                console.log('Đang tạo biến thể tự động...');
                
                // Kiểm tra xem có thuộc tính nào được chọn chưa
                if (selectedTypes.length === 0) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Vui lòng chọn ít nhất một thuộc tính biến thể',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }
                
                // Thu thập tất cả các giá trị thuộc tính đã chọn
                const variantAttributes = [];
                $('.selected-variant-type').each(function() {
                    const variantId = $(this).data('variant-id');
                    const variantValueData = $(this).find('.variant-value-data').val();
                    
                    if (variantValueData) {
                        const values = JSON.parse(variantValueData);
                        variantAttributes.push({
                            variantId: variantId,
                            values: values
                        });
                    }
                });
                
                // Kiểm tra xem tất cả thuộc tính đều có ít nhất một giá trị
                const missingValues = variantAttributes.filter(attr => !attr.values || attr.values.length === 0);
                if (missingValues.length > 0) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: 'Có thuộc tính chưa có giá trị nào được chọn',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }
                
                // Hiển thị thông báo xác nhận nếu đã có biến thể
                if (selectedVariants.length > 0) {
                    Swal.fire({
                        title: 'Xác nhận',
                        text: 'Tạo biến thể tự động sẽ xóa tất cả các biến thể đã tạo trước đó. Bạn có chắc chắn muốn tiếp tục?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Đồng ý',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#405189'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            generateVariants(variantAttributes);
                        }
                    });
                } else {
                    generateVariants(variantAttributes);
                }
            }
            
            // Hàm tạo tất cả tổ hợp biến thể có thể
            function generateVariants(attributes) {
                // Reset lại mảng biến thể
                selectedVariants = [];
                
                // Tạo một mảng các mảng giá trị
                const valueArrays = attributes.map(attr => attr.values.map(value => ({
                    variantId: attr.variantId,
                    valueId: value.id,
                    valueName: value.value
                })));
                
                // Hàm tạo tổ hợp Cartesian product
                function cartesianProduct(arrays) {
                    return arrays.reduce((acc, array) => {
                        return acc.flatMap(x => array.map(y => [...x, y]));
                    }, [[]]);
                }
                
                // Tạo tất cả tổ hợp có thể
                const combinations = cartesianProduct(valueArrays);
                
                // Hiển thị số lượng biến thể sẽ được tạo
                console.log(`Tạo ${combinations.length} biến thể...`);
                
                // Hiển thị thông báo loading
                let timerInterval;
                Swal.fire({
                    title: 'Đang tạo biến thể',
                    html: `Đang tạo <b>${combinations.length}</b> biến thể, vui lòng đợi...`,
                    timer: 2000,
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading();
                        timerInterval = setInterval(() => {}, 100);
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                }).then(() => {
                    // Tạo các biến thể
                    $('#variants-container').empty();
                    
                    // Tạo biến thể cho mỗi tổ hợp
                    combinations.forEach((combination, index) => {
                        // Tạo đối tượng biến thể mới
                        const variant = {
                            sku: `SKU-${index + 1}`,
                            price: $('#price').val() || '0',
                            quantity: '0',
                            values: {}
                        };
                        
                        // Thu thập tất cả các giá trị thuộc tính cho biến thể này
                        combination.forEach(item => {
                            variant.values[item.variantId] = item.valueId;
                        });
                        
                        // Thêm vào mảng biến thể
                        selectedVariants.push(variant);
                        
                        // Hiển thị biến thể mới
                        displayVariant(variant, index);
                    });
                    
                    // Gắn sự kiện cho các biến thể đã tạo
                    attachEventsToGeneratedVariants();
                    
                    // Thông báo thành công
                    Swal.fire({
                        title: 'Thành công!',
                        text: `Đã tạo ${combinations.length} biến thể sản phẩm`,
                        icon: 'success',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#405189'
                    });
                });
            }

            // Thêm hàm tạo biến thể tự động
            $('#generate-variants-btn').on('click', handleGenerateVariants);

            // Thêm sự kiện input validate cho trường số lượng
            $('#quantity').on('input', function() {
                // Nếu đang ở chế độ biến thể, không cần validate trường số lượng
                if (variantToggle.is(':checked')) {
                    // Ẩn tất cả thông báo lỗi liên quan đến số lượng
                    clearValidation($(this));
                    $('#quantity-error').hide();
                    return;
                }
                
                // Xóa thông báo lỗi cũ
                clearValidation($(this));
                
                if ($(this).val() === '' || $(this).val() === null) {
                    showError($(this), 'Số lượng không được để trống');
                    $('#quantity-error').text('Số lượng không được để trống').show();
                } else if (isNaN(parseInt($(this).val()))) {
                    showError($(this), 'Số lượng phải là số');
                    $('#quantity-error').text('Số lượng phải là số').show();
                } else if (parseInt($(this).val()) < 0) {
                    showError($(this), 'Số lượng phải lớn hơn hoặc bằng 0');
                    $('#quantity-error').text('Số lượng phải lớn hơn hoặc bằng 0').show();
                } else {
                    showSuccess($(this));
                    $('#quantity-error').hide();
                }
            });

            // Hàm loại bỏ dấu tiếng Việt
            function removeVietnameseAccents(str) {
                if (!str) return '';
                str = str.toString();
                return str.normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .replace(/đ/g, 'd').replace(/Đ/g, 'D');
            }

            // Cập nhật hàm validateField để kiểm tra SKU trùng lặp
            function validateField(field, value, isAddingVariant = false, variantIndex = -1) {
                // Xóa thông báo lỗi cũ
                clearValidation(field);
                
                // Kiểm tra theo loại field
                switch (field.attr('id')) {
                    case 'name':
                        if (!value) {
                            showError(field, 'Vui lòng nhập tên sản phẩm');
                            return false;
                        }
                        break;
                        
                    case 'category_id':
                        if (!value) {
                            showError(field, 'Vui lòng chọn danh mục');
                            return false;
                        }
                        break;
                        
                    case 'price':
                        if (!value || value <= 0) {
                            showError(field, 'Giá cơ bản phải lớn hơn 0');
                            return false;
                        }
                        if (parseInt(value) > 999999999) {
                            showError(field, 'Giá cơ bản không được lớn hơn 999.999.999 VNĐ');
                            return false;
                        }
                        break;
                        
                    case 'discount_price':
                        const basePrice = parseInt($('#price').val());
                        if (value && parseInt(value) >= basePrice) {
                            showError(field, 'Giá khuyến mãi phải nhỏ hơn giá cơ bản');
                            return false;
                        }
                        break;
                        
                    case 'quantity':
                        // Nếu đang ở chế độ biến thể, không validate số lượng
                        if (variantToggle.is(':checked')) {
                            return true;
                        }
                        
                        if (value === '' || value === null || isNaN(parseInt(value))) {
                            showError(field, 'Số lượng không được để trống');
                            $('#quantity-error').text('Số lượng không được để trống').show();
                            return false;
                        } else if (parseInt(value) < 0) {
                            showError(field, 'Số lượng phải lớn hơn hoặc bằng 0');
                            $('#quantity-error').text('Số lượng phải lớn hơn hoặc bằng 0').show();
                            return false;
                        } else {
                            $('#quantity-error').hide();
                        }
                        break;
                        
                    case 'variant-sku':
                        if (!value && isAddingVariant) {
                            showError(field, 'Vui lòng nhập SKU');
                            return false;
                        }
                        if (value && checkDuplicateSku(value, variantIndex)) {
                            showError(field, 'SKU này đã tồn tại');
                            return false;
                        }
                        break;
                        
                    case 'variant-price':
                        if (!value && isAddingVariant) {
                            showError(field, 'Vui lòng nhập giá');
                            return false;
                        }
                        if (parseInt(value) > 999999999) {
                            showError(field, 'Giá biến thể không được lớn hơn 999.999.999 VNĐ');
                            return false;
                        }
                        break;
                        
                    case 'variant-quantity':
                        if (!value && isAddingVariant) {
                            showError(field, 'Vui lòng nhập số lượng');
                            return false;
                        }
                        break;
                }
                
                // Nếu không có lỗi, thêm class is-valid
                showSuccess(field);
                return true;
            }

            function showError(field, message) {
                // Đối với trường giá cơ bản và giá khuyến mãi, sử dụng phương pháp mới
                if (field.attr('id') === 'price' || field.attr('id') === 'discount_price') {
                    showErrorMessage(field, message);
                    return;
                }
                
                // Xử lý trường hợp nếu field là chuỗi (id)
                if (typeof field === 'string') {
                    field = $('#' + field);
                }
                
                // Xóa các trạng thái validation hiện tại
                field.removeClass('is-valid').addClass('is-invalid');
                
                // Tìm phần tử feedback
                let feedbackElement = field.siblings('.invalid-feedback');
                
                // Nếu không tìm thấy, kiểm tra trong parent
                if (feedbackElement.length === 0) {
                    feedbackElement = field.parent().find('.invalid-feedback');
                }
                
                // Nếu tìm thấy, cập nhật nội dung và hiển thị
                if (feedbackElement.length > 0) {
                    // Xóa các thông báo lỗi trùng lặp trước khi thêm mới
                    feedbackElement.empty();
                    feedbackElement.text(message).css('display', 'block');
                } else {
                    // Nếu không tìm thấy, tạo mới
                    // Kiểm tra xem đã có phần tử feedback nào được tạo trước đó
                    const existingFeedback = field.next('.invalid-feedback');
                    if (existingFeedback.length > 0) {
                        existingFeedback.text(message).css('display', 'block');
                    } else {
                        const newFeedback = $('<div class="invalid-feedback">' + message + '</div>');
                        newFeedback.css('display', 'block');
                        
                        // Thêm vào DƯỚI field hoặc parent nếu là input-group
                        if (field.parent().hasClass('input-group')) {
                            field.parent().after(newFeedback);
                        } else if (field.parent().hasClass('variant-type-select')) {
                            // Nếu là phần chọn thuộc tính biến thể, thêm vào parent
                            field.parent().after(newFeedback);
                        } else {
                            field.after(newFeedback);
                        }
                    }
                }

                // Khi hiển thị lỗi từ JS, ẩn lỗi từ Laravel
                if (field.attr('id') === 'quantity') {
                    field.closest('.form-group').find('.invalid-feedback').hide();
                }
            }

            function showSuccess(field) {
                // Đối với trường giá cơ bản và giá khuyến mãi, sử dụng phương pháp mới
                if (field.attr('id') === 'price' || field.attr('id') === 'discount_price') {
                    showSuccessForPrice(field);
                    return;
                }
                
                field.removeClass('is-invalid').addClass('is-valid');
                
                // Đảm bảo input-group-text không hiển thị biểu tượng validation
                if (field.parent().hasClass('input-group')) {
                    field.parent().find('.input-group-text').removeClass('is-invalid is-valid');
                }
                
                // Ẩn các thông báo lỗi
                let feedbackElement = field.siblings('.invalid-feedback');
                if (feedbackElement.length === 0) {
                    feedbackElement = field.parent().find('.invalid-feedback');
                }
                
                if (feedbackElement.length > 0) {
                    feedbackElement.css('display', 'none');
                    feedbackElement.empty(); // Xóa nội dung để tránh duplicate
                }
            }

            function clearValidation(field) {
                // Xóa các trạng thái validation
                field.removeClass('is-invalid is-valid');
                
                // Đảm bảo input-group-text cũng được làm sạch
                const inputGroup = field.parent();
                if (inputGroup.hasClass('input-group')) {
                    inputGroup.find('.input-group-text').removeClass('is-valid is-invalid');
                    // Xóa tick tùy chỉnh nếu có
                    inputGroup.find('.valid-feedback-icon').remove();
                }
                
                // Đối với trường giá cơ bản và giá khuyến mãi, xóa tất cả thông báo lỗi
                if (field.attr('id') === 'price' || field.attr('id') === 'discount_price') {
                    removeAllFeedbackMessages(field);
                    return;
                }
                
                // Ẩn các thông báo lỗi
                let feedbackElement = field.siblings('.invalid-feedback');
                if (feedbackElement.length === 0) {
                    feedbackElement = field.parent().find('.invalid-feedback');
                }
                
                if (feedbackElement.length > 0) {
                    feedbackElement.css('display', 'none');
                    // Thêm dòng này để xóa nội dung của thông báo lỗi
                    feedbackElement.empty();
                }
                
                // Đảm bảo ẩn cả hai loại thông báo lỗi
                if (field.attr('id') === 'quantity') {
                    $('#quantity-error').hide();
                    field.closest('.form-group').find('.invalid-feedback').hide().empty();
                }
                
                // Xóa thông báo lỗi của trường discount_price nếu đang làm việc với biến thể
                if (field.closest('.variant-item').length > 0) {
                    $('#discount_price').removeClass('is-invalid is-valid');
                    $('#discount_price').siblings('.invalid-feedback').hide().empty();
                }
            }

            // Cập nhật hàm resetValidationState
            function resetValidationState() {
                $('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                $('.invalid-feedback').text('').hide();
                $('.invalid-feedback').remove(); // Xóa hết tất cả thông báo lỗi
                $('#quantity-error').hide();
                $('#discount_price').closest('.form-group').find('.invalid-feedback').hide().empty();
                
                // Reset biến theo dõi cuộn đến lỗi
                window.hasScrolledToError = false;
                
                // Xóa tất cả các thông báo lỗi cũ cho giá cơ bản và giá khuyến mãi
                removeAllFeedbackMessages(priceInput);
                removeAllFeedbackMessages(discountPriceInput);
            }

            // Thêm hàm mới để xóa tất cả các thông báo lỗi cho một trường
            function removeAllFeedbackMessages(field) {
                // Tìm tất cả feedback elements liên quan đến field này
                const parentElement = field.closest('.form-group');
                if (parentElement.length > 0) {
                    // Xóa tất cả invalid-feedback trong form-group
                    parentElement.find('.invalid-feedback').remove();
                } else {
                    // Xóa tất cả invalid-feedback liền kề với field
                    field.siblings('.invalid-feedback').remove();
                    // Xóa cả invalid-feedback trong parent (nếu là input-group)
                    field.parent().find('.invalid-feedback').remove();
                    // Xóa cả invalid-feedback liền kề với parent (nếu là input-group)
                    field.parent().siblings('.invalid-feedback').remove();
                }
            }
            
            // Hàm hiển thị thông báo thành công cho giá
            function showSuccessForPrice(field) {
                // Xóa lớp is-invalid và thêm is-valid để hiện tick mặc định trong input
                field.removeClass('is-invalid').addClass('is-valid');
                
                // Xóa tất cả thông báo lỗi
                removeAllFeedbackMessages(field);
                
                // Đảm bảo input-group-text không có tick
                const inputGroup = field.parent();
                if (inputGroup.hasClass('input-group')) {
                    // Đảm bảo input-group-text không có dấu tick mặc định
                    inputGroup.find('.input-group-text').removeClass('is-valid is-invalid');
                }
                
                // Xóa tick tùy chỉnh nếu có
                inputGroup.find('.valid-feedback-icon').remove();
            }
            
            // Hàm hiển thị thông báo lỗi mới (thay thế cho showError)
            function showErrorMessage(field, message) {
                // Xử lý trường hợp nếu field là chuỗi (id)
                if (typeof field === 'string') {
                    field = $('#' + field);
                }
                
                // Thêm class is-invalid và xóa is-valid
                field.removeClass('is-valid').addClass('is-invalid');
                
                // Đảm bảo input-group-text không có classes validation
                const inputGroup = field.parent();
                if (inputGroup.hasClass('input-group')) {
                    inputGroup.find('.input-group-text').removeClass('is-valid is-invalid');
                    // Xóa icon tick tùy chỉnh nếu có
                    inputGroup.find('.valid-feedback-icon').remove();
                }
                
                // Tạo ID duy nhất cho feedback element dựa trên ID của field
                const feedbackId = field.attr('id') ? field.attr('id') + '-feedback' : 'feedback-' + Math.random().toString(36).substring(2, 9);
                
                // Xóa feedback element cũ có cùng ID (nếu có)
                $('#' + feedbackId).remove();
                
                // Tạo feedback element mới
                const newFeedback = $('<div id="' + feedbackId + '" class="invalid-feedback">' + message + '</div>');
                newFeedback.css('display', 'block');
                
                // Xác định vị trí để thêm feedback
                if (field.parent().hasClass('input-group')) {
                    field.parent().after(newFeedback);
                } else if (field.parent().hasClass('variant-type-select')) {
                    field.parent().after(newFeedback);
                } else {
                    field.after(newFeedback);
                }
            }

            // Xử lý submit form
            $('#productForm').on('submit', function(e) {
                e.preventDefault();
                
                console.log('Form submitted');
                console.log('isAddingVariant:', isAddingVariant);
                console.log('selectedVariants:', selectedVariants);
                console.log('variantToggle checked:', variantToggle.is(':checked'));
                console.log('productForm action:', $(this).attr('action'));
                
                // Kiểm tra validation
                let isValid = true;
                let firstErrorElement = null;
                
                // Kiểm tra tên sản phẩm
                if (!nameInput.val().trim()) {
                    showError(nameInput, 'Vui lòng nhập tên sản phẩm');
                    isValid = false;
                    firstErrorElement = firstErrorElement || nameInput[0];
                }
                
                // Kiểm tra danh mục
                if (!categorySelect.val()) {
                    showError(categorySelect, 'Vui lòng chọn danh mục sản phẩm');
                    isValid = false;
                    firstErrorElement = firstErrorElement || categorySelect[0];
                }
                
                // Kiểm tra hình ảnh
                const productHasExistingThumbnail = "{{ isset($product) && $product->image_thumnail ? 'true' : 'false' }}" === 'true';
                const thumbnailInput = $('#image_thumnail')[0];
                
                if (!productHasExistingThumbnail && (!thumbnailInput.files || thumbnailInput.files.length === 0)) {
                    $('#thumbnail-error').show();
                    isValid = false;
                    firstErrorElement = firstErrorElement || thumbnailInput;
                }
                
                // Kiểm tra giá và số lượng nếu không có biến thể
                if (!variantToggle.is(':checked')) {
                    // Kiểm tra giá cơ bản
                    const basePrice = parseFloat($('#price').val());
                    if (!$('#price').val() || isNaN(basePrice) || basePrice <= 0) {
                        showError($('#price'), 'Giá gốc phải lớn hơn 0');
                        isValid = false;
                        firstErrorElement = firstErrorElement || $('#price')[0];
                    }
                    
                    // Kiểm tra giá khuyến mãi
                    const discountPrice = parseFloat($('#discount_price').val());
                    if ($('#discount_price').val().trim() && (!isNaN(discountPrice) && discountPrice >= basePrice)) {
                        showError($('#discount_price'), 'Giá khuyến mãi phải nhỏ hơn giá gốc');
                        isValid = false;
                        firstErrorElement = firstErrorElement || $('#discount_price')[0];
                    }
                    
                    // Kiểm tra số lượng
                    const quantity = parseInt($('#quantity').val());
                    if (!$('#quantity').val() || isNaN(quantity) || quantity < 0) {
                        showError($('#quantity'), 'Số lượng không được âm');
                        isValid = false;
                        firstErrorElement = firstErrorElement || $('#quantity')[0];
                    }
                } else {
                    // Kiểm tra nếu có biến thể, kiểm tra các biến thể đã được tạo đầy đủ chưa
                    if (selectedVariants.length === 0 && $('.variant-item').length === 0) {
                        Swal.fire({
                            title: 'Lỗi!',
                            text: 'Vui lòng tạo ít nhất một biến thể cho sản phẩm',
                            icon: 'error',
                            confirmButtonText: 'Đóng'
                        });
                        isValid = false;
                        firstErrorElement = firstErrorElement || $('#variant-section')[0];
                    } else {
                        // Kiểm tra từng biến thể
                        for (let i = 0; i < selectedVariants.length; i++) {
                            const variant = selectedVariants[i];
                            if (!variant.sku || !variant.price || variant.quantity === undefined) {
                                Swal.fire({
                                    title: 'Lỗi!',
                                    text: `Biến thể #${i+1} thiếu thông tin. Vui lòng cập nhật đầy đủ SKU, giá và số lượng.`,
                                    icon: 'error',
                                    confirmButtonText: 'Đóng'
                                });
                                isValid = false;
                                // Cuộn đến container biến thể
                                firstErrorElement = firstErrorElement || $('#variants-container')[0];
                                break;
                            }
                        }
                    }
                }

                if (!isValid) {
                    // Cuộn đến phần tử lỗi đầu tiên nếu có
                    if (firstErrorElement) {
                        firstErrorElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    return false;
                }
                
                // Disable submit button
                const submitBtn = form.find('button[type="submit"]');
                const originalBtnText = submitBtn.html();
                submitBtn.prop('disabled', true);
                submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...');
                
                // Tạo FormData mới
                const formData = new FormData(this);
                
                // Thêm danh sách ảnh đã xóa vào formData nếu có
                if (removedImages && removedImages.length > 0) {
                    formData.append('removed_images', JSON.stringify(removedImages));
                }

                // Thêm method PUT vào formData
                formData.append('_method', 'PUT');

                // Thu thập dữ liệu biến thể từ các variant-item đã được thêm vào danh sách
                if (variantToggle.is(':checked')) {
                    // Đặt has_variants = 1 để server biết xử lý biến thể
                    formData.set('has_variants', '1');
                    
                    // Xóa tất cả các trường variants cũ từ formData
                    const keysToDelete = [];
                    for (let pair of formData.entries()) {
                        if (pair[0].startsWith('variants')) {
                            keysToDelete.push(pair[0]);
                        }
                    }
                    
                    // Xóa các trường đã được đánh dấu
                    keysToDelete.forEach(key => {
                        formData.delete(key);
                    });
                    
                    // Nếu có biến thể mới được thêm vào trong phiên này
                    if (selectedVariants.length > 0) {
                        // Thêm lại dữ liệu biến thể từ mảng selectedVariants
                        selectedVariants.forEach((variant, index) => {
                            formData.append(`variants[${index}][sku]`, variant.sku);
                            formData.append(`variants[${index}][price]`, variant.price);
                            formData.append(`variants[${index}][discount_price]`, variant.discount_price || '');
                            formData.append(`variants[${index}][quantity]`, variant.quantity);
                            
                            // Thêm các giá trị thuộc tính
                            Object.entries(variant.values).forEach(([variantId, valueId]) => {
                                formData.append(`variants[${index}][values][${variantId}]`, valueId);
                            });
                        });
                    } else {
                        // Nếu không có biến thể mới, lấy dữ liệu từ các biến thể hiện có trên trang
                        const existingVariantItems = $('.variant-item');
                        if (existingVariantItems.length > 0) {
                            existingVariantItems.each(function(index) {
                                const variantItem = $(this);
                                const sku = variantItem.find('.variant-sku').text().trim();
                                const price = variantItem.find('.variant-price').text().trim().replace(/[^\d]/g, '');
                                const discountPrice = variantItem.find('.variant-discount-price').text().trim().replace(/[^\d]/g, '') || '';
                                const quantity = variantItem.find('.variant-quantity').text().trim().replace(/[^\d]/g, '');
                                
                                // Lấy các giá trị thuộc tính từ text hiển thị trong variant-attributes
                                const variantInfo = variantItem.find('.variant-info').data('variant-info') || {};
                                
                                formData.append(`variants[${index}][sku]`, sku);
                                formData.append(`variants[${index}][price]`, price);
                                formData.append(`variants[${index}][discount_price]`, discountPrice);
                                formData.append(`variants[${index}][quantity]`, quantity);
                                
                                // Thêm các giá trị thuộc tính từ data-attribute
                                if (variantInfo.attributes) {
                                    Object.entries(variantInfo.attributes).forEach(([variantId, valueId]) => {
                                        formData.append(`variants[${index}][values][${variantId}]`, valueId);
                                    });
                                }
                            });
                        } else {
                            // Nếu không có biến thể nào, hãy đặt has_variants = 0
                            formData.set('has_variants', '0');
                        }
                    }
                } else {
                    // Nếu toggle biến thể tắt, đặt has_variants = 0
                    formData.set('has_variants', '0');
                    
                    // Xóa tất cả các trường variants khỏi formData
                    const keysToDelete = [];
                    for (let pair of formData.entries()) {
                        if (pair[0].startsWith('variants')) {
                            keysToDelete.push(pair[0]);
                        }
                    }
                    
                    // Xóa các trường đã được đánh dấu
                    keysToDelete.forEach(key => {
                        formData.delete(key);
                    });
                }

                // Debug: Log formData trước khi gửi
                console.log('FormData entries:');
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ':', pair[1]);
                }
                
                // Debug: Log variants array
                console.log('Variants array:', selectedVariants);

                // Gửi request AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log('Success response:', response);
                        if (response.success) {
                            // Hiển thị thông báo thành công
                            Swal.fire({
                                title: 'Thành công!',
                                text: response.message || 'Cập nhật sản phẩm thành công',
                                icon: 'success',
                                confirmButtonColor: '#3b5998'
                            }).then(() => {
                                // Chuyển hướng sang trang danh sách sản phẩm
                                window.location.href = '/admin/products';
                            });
                        } else {
                            // Hiển thị thông báo lỗi từ server
                            Swal.fire({
                                title: 'Lỗi!',
                                text: response.message || 'Có lỗi xảy ra khi cập nhật sản phẩm',
                                icon: 'error',
                                confirmButtonText: 'Đóng'
                            });
                            
                            // Kích hoạt lại nút submit
                            submitBtn.prop('disabled', false);
                            submitBtn.html(originalBtnText);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error response:', xhr.responseText);
                        // Hiển thị thông báo lỗi
                        let errorMessage = 'Có lỗi xảy ra khi cập nhật sản phẩm';
                        
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.message || errorMessage;
                            
                            // Hiển thị các lỗi validation nếu có
                            if (response.errors) {
                                const errorList = Object.values(response.errors).flat();
                                if (errorList.length > 0) {
                                    errorMessage = errorList.join('<br>');
                                }
                            }
                        } catch (e) {
                            console.error('Error parsing response:', e);
                        }
                        
                        Swal.fire({
                            title: 'Lỗi!',
                            html: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'Đóng'
                        });
                        
                        // Kích hoạt lại nút submit
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalBtnText);
                    }
                });
            });

            // Cập nhật hàm gắn các sự kiện cho biến thể tự động
            function attachEventsToGeneratedVariants() {
                console.log('Đang gắn sự kiện cho các biến thể tự động');
                
                // Thêm sự kiện click cho các biến thể được tạo tự động
                $('.variant-header.cursor-pointer').off('click').on('click', function() {
                    const variantItem = $(this).closest('.variant-item');
                    const variantId = variantItem.attr('data-variant-id');
                    const variantIndex = parseInt(variantItem.attr('data-variant-index'));
                    console.log('Click vào header của biến thể:', variantId, 'Index:', variantIndex);
                    
                    // Đóng tất cả các form khác trước
                    $('.variant-edit').not(variantItem.find('.variant-edit')).slideUp(300);
                    
                    if (variantId && !variantId.startsWith('variant_')) {
                        window.toggleVariantEdit(variantId);
                    } else {
                        const variantForm = variantItem.find('.variant-edit');
                        
                        // Hiển thị/ẩn form hiện tại
                        variantForm.slideToggle(300);
                    }
                });
                
                // Thêm sự kiện click cho nút lưu thay đổi
                $('.save-variant-edit').off('click').on('click', function() {
                    const variantItem = $(this).closest('.variant-item');
                    const variantId = variantItem.attr('data-variant-id');
                    const variantIndex = parseInt(variantItem.attr('data-variant-index'));
                    console.log('Click vào nút lưu của biến thể:', variantId, 'Index:', variantIndex);
                    
                    // Kiểm tra xem biến thể có tồn tại trong mảng selectedVariants không
                    if (isNaN(variantIndex) || variantIndex < 0 || variantIndex >= selectedVariants.length) {
                        console.error('Không tìm thấy biến thể với index:', variantIndex);
                        Swal.fire({
                            title: 'Lỗi!',
                            text: 'Không tìm thấy thông tin biến thể. Vui lòng thử lại.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    
                    // Gọi hàm saveVariantEdit để lưu thay đổi
                    if (variantId && variantId.startsWith('variant_')) {
                        const variantForm = variantItem.find('.variant-edit');
                        const variantHeader = variantItem.find('.variant-header');
                        
                        // Đảm bảo xóa thông báo lỗi của trường discount_price
                        $('#discount_price').removeClass('is-invalid is-valid');
                        $('#discount_price').siblings('.invalid-feedback').hide().empty();
                        
                        // Lấy giá trị từ form
                        const sku = variantForm.find('.variant-sku-input').val();
                        const price = variantForm.find('.variant-price-input').val();
                        const quantity = variantForm.find('.variant-quantity-input').val();
                        
                        console.log('Giá trị từ form:', { sku, price, quantity, variantIndex });
                        
                        // Validate
                        let isValid = true;
                        
                        // Validate SKU
                        if (!sku.trim()) {
                            showError(variantForm.find('.variant-sku-input'), 'Vui lòng nhập SKU');
                            isValid = false;
                        } else if (checkDuplicateSku(sku.trim(), variantIndex)) {
                            showError(variantForm.find('.variant-sku-input'), 'SKU này đã tồn tại');
                            isValid = false;
                        } else {
                            showSuccess(variantForm.find('.variant-sku-input'));
                        }
                        
                        // Validate giá
                        const priceValue = parseFloat(price);
                        if (!price || isNaN(priceValue) || priceValue <= 0) {
                            showError(variantForm.find('.variant-price-input'), 'Giá phải lớn hơn 0');
                            isValid = false;
                        } else if (priceValue > 999999999) {
                            showError(variantForm.find('.variant-price-input'), 'Giá biến thể không được lớn hơn 999.999.999 VNĐ');
                            isValid = false;
                        } else {
                            showSuccess(variantForm.find('.variant-price-input'));
                        }
                        
                        // Validate số lượng
                        const quantityValue = parseInt(quantity);
                        if (!quantity || isNaN(quantityValue) || quantityValue < 0) {
                            showError(variantForm.find('.variant-quantity-input'), 'Số lượng không được âm');
                            isValid = false;
                        } else {
                            showSuccess(variantForm.find('.variant-quantity-input'));
                        }
                        
                        if (!isValid) {
                            console.error('Dữ liệu không hợp lệ');
                            return;
                        }
                        
                        // Cập nhật giá trị hiển thị
                        variantHeader.find('span:contains("SKU:")').next('.fw-medium').text(sku || 'Chưa có');
                        variantHeader.find('span:contains("Giá:")').next('.fw-medium').text(price ? parseInt(price).toLocaleString('vi-VN') + ' VNĐ' : 'Chưa có');
                        variantHeader.find('span:contains("SL:")').next('.fw-medium').text(quantity || 'Chưa có');
                        
                        // Cập nhật giá trị input hidden
                        variantHeader.find('input[name$="[sku]"]').val(sku);
                        variantHeader.find('input[name$="[price]"]').val(price);
                        variantHeader.find('input[name$="[quantity]"]').val(quantity);
                        
                        // Cập nhật dữ liệu trong mảng selectedVariants
                        selectedVariants[variantIndex].sku = sku;
                        selectedVariants[variantIndex].price = price;
                        selectedVariants[variantIndex].quantity = quantity;
                        
                        console.log(`Đã cập nhật biến thể #${variantIndex + 1}:`, selectedVariants[variantIndex]);
                        
                        // Đóng form
                        variantForm.slideUp(300);
                    } else {
                        window.saveVariantEdit(variantId);
                    }
                });
                
                // Thêm sự kiện click cho nút hủy
                $('.cancel-variant-edit').off('click').on('click', function() {
                    const variantItem = $(this).closest('.variant-item');
                    const variantForm = variantItem.find('.variant-edit');
                    
                    // Đóng form
                    variantForm.slideUp(300);
                });
                
                // Thêm sự kiện click cho nút xóa biến thể
                $('.remove-variant').off('click').on('click', function(e) {
                    e.stopPropagation(); // Ngăn sự kiện click lan tỏa lên phần tử cha
                    const variantItem = $(this).closest('.variant-item');
                    
                    Swal.fire({
                        title: 'Xác nhận xóa',
                        text: 'Bạn có chắc chắn muốn xóa biến thể này?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Xóa',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#f06548'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            variantItem.remove();
                        }
                    });
                });
                
                // Thêm sự kiện input cho các trường trong biến thể
                $('.variant-item').each(function() {
                    attachInputEventsToVariant($(this));
                });
            }

            // Thêm CSS fix để tránh hiển thị biểu tượng validation trên input-group-text
            $('head').append(`
                <style>
                    /* Ngăn chặn hoàn toàn biểu tượng validation trên input-group-text */
                    .input-group-text.is-valid, .input-group-text.is-invalid {
                        background-image: none !important;
                        padding-right: 0.75rem !important;
                    }
                    .input-group .input-group-text {
                        background-image: none !important;
                    }
                    
                    /* Đảm bảo tick validation hiển thị đúng trong input */
                    .input-group .form-control.is-valid {
                        background-position: right calc(0.375em + 0.1875rem) center !important;
                        padding-right: calc(1.5em + 0.75rem) !important;
                        border-color: #34c38f !important;
                    }
                </style>
            `);
        });
    </script>