   <!-- Sweet Alerts js -->
   <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- CKEditor -->
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    @include('partials.products.edit_css')

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
            
            // Remove preview
            if (imageId) {
            item.remove();
            }
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
            const variantToggle = $('#variantToggle');
            const variantSection = $('#variantSection');
            const hasVariantsInput = $('#hasVariants');
            let selectedTypes = [];
            let selectedVariants = []; // Thêm biến để lưu trữ các biến thể đã chọn
            let isAddingVariant = false; // Thêm biến kiểm soát việc thêm biến thể

            // Xử lý toggle biến thể
            variantToggle.on('change', function() {
                if (this.checked) {
                    // Hiển thị phần biến thể
                    variantSection.slideDown(300);
                    hasVariantsInput.val('1');
                    isAddingVariant = true;
                } else {
                    // Ẩn phần biến thể
                    variantSection.slideUp(300);
                    hasVariantsInput.val('0');
                    isAddingVariant = false;
                    
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
            $('#generate-variants-btn').addClass('btn-primary').removeClass('btn-info');
            $('#generate-variants-btn').html('<i class="fas fa-magic me-2"></i>Tạo biến thể tự động');

            // Khởi tạo trạng thái hiển thị của phần biến thể dựa vào toggle
            if (!variantToggle.is(':checked')) {
                variantSection.hide();
                hasVariantsInput.val('0');
                isAddingVariant = false;
            } else {
                variantSection.show();
                hasVariantsInput.val('1');
                isAddingVariant = true;
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
                            <button type="button" class="btn-remove-variant btn btn-sm btn-outline-danger" onclick="removeVariantType('${variantId}')">
                                <i class="fas fa-times"></i> Xóa
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
                }
            };

            // Cải thiện giao diện cho phần chọn thuộc tính
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
            
            // Cải thiện giao diện cho nút tạo biến thể tự động
            $('#generate-variants-btn').addClass('btn-primary').removeClass('btn-info');
            
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
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeVariant('${variantId}', event)">
                                    <i class="fas fa-trash me-1"></i> Xóa
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
                                <button type="button" class="btn btn-secondary me-2 cancel-variant-edit">Hủy</button>
                                <button type="button" class="btn btn-primary save-variant-edit" data-variant-id="${variantId}">Lưu</button>
                            </div>
                                </div>
                        <div class="variant-hidden-inputs">
                            <input type="hidden" name="variants[${variantIndex}][sku]" class="variant-sku-hidden" value="${variant.sku || ''}">
                            <input type="hidden" name="variants[${variantIndex}][price]" class="variant-price-hidden" value="${variant.price || ''}">
                            <input type="hidden" name="variants[${variantIndex}][quantity]" class="variant-quantity-hidden" value="${variant.quantity || ''}">
                            ${Object.entries(variant.values).map(([variantId, valueId]) => 
                                `<input type="hidden" name="variants[${variantIndex}][values][${variantId}]" value="${valueId}">`
                            ).join('')}
                            </div>
                        </div>
                `);

                // Thêm vào container
                $('#variants-container').append(variantElement);
                
                // Cập nhật UI
                updateVariantsContainerUI();
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
            
            // Lưu thông tin chỉnh sửa biến thể
            window.saveVariantEdit = function(variantId) {
                console.log('Đang lưu biến thể với ID:', variantId);
                
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
                const quantityInput = variantElement.find('.variant-quantity-input');
                
                console.log('Giá trị hiện tại:', {
                    sku: skuInput.val(),
                    price: priceInput.val(),
                    quantity: quantityInput.val()
                });
                
                // Validate
                let isValid = true;
                
                if (!skuInput.val().trim()) {
                    skuInput.addClass('is-invalid');
                    isValid = false;
                } else {
                    skuInput.removeClass('is-invalid').addClass('is-valid');
                }
                
                if (!priceInput.val() || priceInput.val() <= 0) {
                    priceInput.addClass('is-invalid');
                    isValid = false;
                } else {
                    priceInput.removeClass('is-invalid').addClass('is-valid');
                }
                
                if (!quantityInput.val() || quantityInput.val() < 0) {
                    quantityInput.addClass('is-invalid');
                    isValid = false;
                } else {
                    quantityInput.removeClass('is-invalid').addClass('is-valid');
                }
                
                if (!isValid) {
                    console.error('Dữ liệu không hợp lệ');
                    return;
                }

                // Cập nhật giá trị trong mảng
                selectedVariants[variantIndex].sku = skuInput.val().trim();
                selectedVariants[variantIndex].price = priceInput.val();
                selectedVariants[variantIndex].quantity = quantityInput.val();
                
                console.log('Đã cập nhật biến thể trong mảng:', selectedVariants[variantIndex]);
                
                // Cập nhật hiển thị
                variantElement.find('.variant-sku-display').text(selectedVariants[variantIndex].sku || 'Chưa có');
                variantElement.find('.variant-price-display').text(
                    selectedVariants[variantIndex].price ? 
                    parseInt(selectedVariants[variantIndex].price).toLocaleString('vi-VN') + ' VNĐ' : 
                    'Chưa có'
                );
                variantElement.find('.variant-quantity-display').text(selectedVariants[variantIndex].quantity || 'Chưa có');
                
                // Cập nhật input hidden
                variantElement.find('.variant-sku-hidden').val(selectedVariants[variantIndex].sku);
                variantElement.find('.variant-price-hidden').val(selectedVariants[variantIndex].price);
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
            }

            // Hàm validate field
            function validateField(field, value) {
                const fieldId = field.attr('id');
                
                // Reset trạng thái validate
                field.removeClass('is-invalid is-valid');
                field.siblings('.invalid-feedback').text('');
                
                // Validate từng trường
                switch(fieldId) {
                    case 'name':
                        if (!value.trim()) {
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
                            showError(field, 'Giá cơ bản không được lớn hơn 999,999,999 VNĐ');
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
                        
                    case 'variant-sku':
                        if (!value && isAddingVariant) {
                            showError(field, 'Vui lòng nhập SKU');
                            return false;
                        }
                        break;
                        
                    case 'variant-price':
                        if (!value && isAddingVariant) {
                            showError(field, 'Vui lòng nhập giá');
                            return false;
                        }
                        if (parseInt(value) > 999999999) {
                            showError(field, 'Giá biến thể không được lớn hơn 999,999,999 VNĐ');
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

            // Thêm sự kiện input cho các trường
            $('#name, #price, #discount_price, #variant-sku, #variant-price, #variant-quantity').on('input', function() {
                validateField($(this), $(this).val());
            });

            $('#category_id').on('change', function() {
                validateField($(this), $(this).val());
            });

            // Cập nhật hàm showError
            function showError(field, message, shouldScroll = true) {
                let fieldElement;
                if (typeof field === 'string') {
                    fieldElement = $(`#${field}`);
                } else {
                    fieldElement = $(field);
                }
                
                fieldElement.removeClass('is-valid').addClass('is-invalid');
                
                // Nếu field nằm trong input-group
                if (fieldElement.parent('.input-group').length) {
                    fieldElement.parent('.input-group').find('.invalid-feedback').text(message);
                } else {
                    fieldElement.siblings('.invalid-feedback').text(message);
                }
                
                // Chỉ scroll khi được yêu cầu và chưa scroll trước đó
                if (shouldScroll && !window.hasScrolledToError) {
                    // Tính toán vị trí cuộn để field hiển thị ở giữa màn hình
                    const windowHeight = $(window).height();
                    const fieldOffset = fieldElement.offset().top;
                    const fieldHeight = fieldElement.outerHeight();
                    
                    // Tính toán vị trí cuộn để field nằm giữa màn hình
                    const scrollPosition = fieldOffset - (windowHeight / 2) + (fieldHeight / 2);
                    
                    $([document.documentElement, document.body]).animate({
                        scrollTop: scrollPosition
                    }, 500);
                    
                    window.hasScrolledToError = true;
                }
            }

            // Hàm hiển thị thành công
            function showSuccess(field) {
                $(field).removeClass('is-invalid').addClass('is-valid');
            }

            // Cập nhật hàm resetValidationState
            function resetValidationState() {
                $('.is-invalid, .is-valid').removeClass('is-invalid is-valid');
                $('.invalid-feedback').text('');
                window.hasScrolledToError = false;
            }

            // Xử lý submit form
            $('#productForm').on('submit', function(e) {
                e.preventDefault();
                let isValid = true;

                // Reset trạng thái validate
                resetValidationState();
                
                // Validate tên sản phẩm
                const nameInput = $('#name');
                if (!nameInput.val().trim()) {
                    showError('name', 'Vui lòng nhập tên sản phẩm');
                    isValid = false;
                } else {
                    showSuccess(nameInput);
                }
                
                // Validate danh mục
                const categorySelect = $('#category_id');
                if (!categorySelect.val()) {
                    showError('category_id', 'Vui lòng chọn danh mục');
                    isValid = false;
                } else {
                    showSuccess(categorySelect);
                }
                
                // Validate giá cơ bản
                const priceInput = $('#price');
                if (!priceInput.val() || priceInput.val() <= 0) {
                    showError('price', 'Giá cơ bản phải lớn hơn 0');
                    isValid = false;
                } else {
                    showSuccess(priceInput);
                }

                // Validate ảnh đại diện
                const imageInput = $('#image_thumnail');
                const thumbnailPreview = $('#thumbnailPreview');
                if (!imageInput.val() && !thumbnailPreview.find('img').attr('src')) {
                    showError('image_thumnail', 'Vui lòng chọn ảnh đại diện');
                    isValid = false;
                } else {
                    showSuccess(imageInput);
                }
                
                // Validate giá khuyến mãi nếu có
                const discountPriceInput = $('#discount_price');
                if (discountPriceInput.val() && parseInt(discountPriceInput.val()) >= parseInt(priceInput.val())) {
                    showError('discount_price', 'Giá khuyến mãi phải nhỏ hơn giá cơ bản');
                    isValid = false;
                } else if (discountPriceInput.val()) {
                    showSuccess(discountPriceInput);
                }

                if (!isValid) {
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
                const variants = [];
                $('.variant-item').each(function() {
                    const variant = {
                        sku: $(this).find('input[name$="[sku]"]').val(),
                        price: parseInt($(this).find('input[name$="[price]"]').val()),
                        quantity: parseInt($(this).find('input[name$="[quantity]"]').val()),
                        values: {}
                    };

                    // Thu thập values
                    $(this).find('input[name*="[values]"]').each(function() {
                        const name = $(this).attr('name');
                        const variantId = name.match(/\[values\]\[(\d+)\]/)[1];
                        variant.values[variantId] = $(this).val();
                    });

                    variants.push(variant);
                });

                // Xóa tất cả các trường variants cũ từ formData
                const keysToDeleteOld = [];
                for (let pair of formData.entries()) {
                    if (pair[0].startsWith('variants')) {
                        keysToDeleteOld.push(pair[0]);
                    }
                }
                
                // Xóa các trường đã được đánh dấu
                keysToDeleteOld.forEach(key => {
                    formData.delete(key);
                });

                // Thêm variants mới vào formData
                if (variantToggle.is(':checked')) {
                    // Đặt has_variants = 1 để server biết xử lý biến thể
                    formData.set('has_variants', '1');
                    
                    if (variants.length > 0) {
                        variants.forEach((variant, index) => {
                            // Kiểm tra xem variant có đầy đủ thông tin không
                            if (!variant.sku || !variant.price || variant.quantity === undefined) {
                                console.error(`Biến thể #${index + 1} thiếu thông tin:`, variant);
                                return;
                            }
                            
                            // Kiểm tra xem variant có values không
                            if (!variant.values || Object.keys(variant.values).length === 0) {
                                console.error(`Biến thể #${index + 1} không có giá trị thuộc tính:`, variant);
                                return;
                            }
                            
                            formData.append(`variants[${index}][sku]`, variant.sku);
                            formData.append(`variants[${index}][price]`, variant.price);
                            formData.append(`variants[${index}][quantity]`, variant.quantity);
                            
                            // Thêm các giá trị thuộc tính
                            Object.entries(variant.values).forEach(([variantId, value]) => {
                                formData.append(`variants[${index}][variant_values][${variantId}]`, value);
                            });
                        });
                    }
                } else {
                    // Nếu toggle biến thể tắt, KHÔNG gửi dữ liệu biến thể
                    // Đặt has_variants = 0 để server biết không xử lý biến thể
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
                console.log('Variants array:', variants);

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
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log('Error response:', xhr.responseJSON);
                        
                        // Hiển thị thông báo lỗi
                        if (xhr.status === 422) {
                            const response = xhr.responseJSON;
                            let errorMessage = 'Vui lòng kiểm tra lại thông tin:\n';
                            
                            if (response.errors) {
                                Object.values(response.errors).forEach(error => {
                                    errorMessage += `- ${error[0]}\n`;
                                });
                            } else if (response.message) {
                                errorMessage = response.message;
                            }
                            
                            Swal.fire({
                                title: 'Lỗi!',
                                html: errorMessage.replace(/\n/g, '<br>'),
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                        } else {
                            Swal.fire({
                                title: 'Lỗi!',
                                text: 'Có lỗi xảy ra khi cập nhật sản phẩm',
                                icon: 'error',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    },
                    complete: function() {
                        // Restore submit button state
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalBtnText);
                    }
                });
            });

            // Thêm hàm tạo biến thể tự động
            function handleGenerateVariants() {
                // Kiểm tra xem có thuộc tính nào được chọn không
                if (selectedTypes.length === 0) {
                    showError(variantTypeSelect, 'Vui lòng chọn ít nhất một thuộc tính biến thể');
                    return;
                }
                
                // Hiển thị loading
                const generateBtn = $('#generate-variants-btn');
                const originalBtnText = generateBtn.html();
                generateBtn.prop('disabled', true);
                generateBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang tạo biến thể...');
                
                // Thu thập các giá trị thuộc tính đã chọn
                const variantTypes = [];
                const variantValues = [];
                
                // Lấy thông tin các thuộc tính và giá trị đã chọn
                selectedTypes.forEach(variantId => {
                    const valueDataInput = $(`.variant-value-data[data-variant-id="${variantId}"]`);
                    if (valueDataInput.length) {
                        try {
                            const selectedValues = JSON.parse(valueDataInput.val());
                            if (selectedValues.length > 0) {
                                // Thêm thông tin thuộc tính
                                const variantOption = variantTypeSelect.find(`option[value="${variantId}"]`);
                                const variantName = variantOption.text();
                                
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
                            console.error('Lỗi khi parse JSON:', error, valueDataInput.val());
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
                    generateBtn.prop('disabled', false);
                    generateBtn.html(originalBtnText);
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
                variantsContainer.empty();
                
                // Thêm các biến thể mới
                variants.forEach(variant => {
                    // Tạo HTML hiển thị biến thể
                    let variantHtml = `
                        <div class="variant-item p-3 bg-light rounded position-relative mb-3" data-variant-id="variant_auto_${variants.length}" data-variant-index="${variants.length}">
                            <div class="variant-header cursor-pointer" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-2">`;
                    
                    // Hiển thị các giá trị thuộc tính
                    Object.entries(variant.values).forEach(([variantId, valueId], index) => {
                        // Lấy thông tin variant từ select
                        const variantOption = variantTypeSelect.find(`option[value="${variantId}"]`);
                        const variantName = variantOption.text();
                        // Lấy giá trị của variant
                        const variantValues = JSON.parse(variantOption.attr('data-values'));
                        const valueName = variantValues.find(v => v.id == valueId)?.value || '';
                        
                        variantHtml += `
                            <div class="d-inline-block">
                                <span class="text-muted">${variantName}:</span>
                                <span class="fw-medium">${valueName}</span>
                                <input type="hidden" name="variants[][values][${variantId}]" value="${valueId}">
                            </div>`;
                        
                        if (index < Object.entries(variant.values).length - 1) {
                            variantHtml += `<span class="text-muted">,</span>`;
                        }
                    });

                    variantHtml += `
                                </div>
                                <div class="d-flex align-items-center gap-3 mt-2">
                                    <div>
                                        <span class="text-muted">SKU:</span>
                                        <span class="fw-medium">${variant.sku || 'Chưa có'}</span>
                                        <input type="hidden" name="variants[][sku]" value="${variant.sku || ''}">
                                    </div>
                                    <div>
                                        <span class="text-muted">Giá:</span>
                                        <span class="fw-medium">${variant.price ? parseInt(variant.price).toLocaleString('vi-VN') + ' VNĐ' : 'Chưa có'}</span>
                                        <input type="hidden" name="variants[][price]" value="${variant.price || ''}">
                                    </div>
                                    <div>
                                        <span class="text-muted">SL:</span>
                                        <span class="fw-medium">${variant.quantity || 'Chưa có'}</span>
                                        <input type="hidden" name="variants[][quantity]" value="${variant.quantity || ''}">
                                    </div>
                                    <div class="ms-auto">
                                        <button type="button" class="btn btn-link text-danger remove-variant p-0 border-0" style="font-size: 18px; text-decoration: none;">
                                            <i class="fas fa-times"></i>
                                        </button>
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
                                    <button type="button" class="btn btn-secondary me-2 cancel-variant-edit">Hủy</button>
                                    <button type="button" class="btn btn-primary save-variant-edit" data-variant-id="variant_auto_${variants.length}">Lưu</button>
                                </div>
                            </div>
                        </div>`;

                    // Thêm vào container
                    variantsContainer.append(variantHtml);
                });
                
                // Gắn các sự kiện cho biến thể tự động
                attachEventsToGeneratedVariants();
                
                // Hiển thị thông báo thành công
                Swal.fire({
                    title: 'Thành công!',
                    text: `Đã tạo ${variants.length} biến thể. Vui lòng nhấp vào từng biến thể để nhập thông tin SKU, giá và số lượng.`,
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
                
                // Khôi phục trạng thái nút
                generateBtn.prop('disabled', false);
                generateBtn.html(originalBtnText);
            }
            
            // Hàm gắn các sự kiện cho biến thể tự động
            function attachEventsToGeneratedVariants() {
                console.log('Đang gắn sự kiện cho các biến thể tự động');
                
                // Thêm sự kiện click cho các biến thể được tạo tự động
                $('.variant-header.cursor-pointer').off('click').on('click', function() {
                    const variantItem = $(this).closest('.variant-item');
                    const variantId = variantItem.attr('data-variant-id');
                    console.log('Click vào header của biến thể:', variantId);
                    
                    if (variantId) {
                        window.toggleVariantEdit(variantId);
                    } else {
                        console.error('Không tìm thấy data-variant-id cho biến thể');
                        const variantForm = variantItem.find('.variant-edit');
                        
                        // Đóng tất cả các form khác
                        $('.variant-edit').not(variantForm).slideUp(300);
                        
                        // Hiển thị/ẩn form hiện tại
                        variantForm.slideToggle(300);
                    }
                });
                
                // Thêm sự kiện click cho nút lưu thay đổi
                $('.save-variant-edit').off('click').on('click', function() {
                    const variantItem = $(this).closest('.variant-item');
                    const variantId = variantItem.attr('data-variant-id') || $(this).attr('data-variant-id');
                    console.log('Click vào nút lưu của biến thể:', variantId);
                    
                    // Gọi hàm saveVariantEdit để lưu thay đổi
                    if (variantId) {
                        window.saveVariantEdit(variantId);
                    } else {
                        console.error('Không tìm thấy variant-id cho nút lưu');
                        
                        // Fallback: Xử lý trực tiếp nếu không tìm thấy variant-id
                        const variantForm = variantItem.find('.variant-edit');
                        const variantHeader = variantItem.find('.variant-header');
                        
                        // Lấy giá trị từ form
                        const sku = variantForm.find('.variant-sku-input').val();
                        const price = variantForm.find('.variant-price-input').val();
                        const quantity = variantForm.find('.variant-quantity-input').val();
                        
                        console.log('Giá trị từ form:', { sku, price, quantity });
                        
                        // Validate
                        let isValid = true;
                        
                        if (!sku.trim()) {
                            variantForm.find('.variant-sku-input').addClass('is-invalid').siblings('.invalid-feedback').text('Vui lòng nhập SKU');
                            isValid = false;
                        } else {
                            variantForm.find('.variant-sku-input').removeClass('is-invalid').addClass('is-valid');
                        }
                        
                        if (!price || price <= 0) {
                            variantForm.find('.variant-price-input').addClass('is-invalid').siblings('.invalid-feedback').text('Giá phải lớn hơn 0');
                            isValid = false;
                        } else {
                            variantForm.find('.variant-price-input').removeClass('is-invalid').addClass('is-valid');
                        }
                        
                        if (!quantity || quantity < 0) {
                            variantForm.find('.variant-quantity-input').addClass('is-invalid').siblings('.invalid-feedback').text('Số lượng không được âm');
                            isValid = false;
                        } else {
                            variantForm.find('.variant-quantity-input').removeClass('is-invalid').addClass('is-valid');
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
                        // Lấy index của biến thể trong mảng
                        const variantIndex = parseInt(variantItem.attr('data-variant-index'));
                        if (!isNaN(variantIndex) && variantIndex >= 0 && variantIndex < selectedVariants.length) {
                            // Cập nhật dữ liệu trong mảng
                            selectedVariants[variantIndex].sku = sku;
                            selectedVariants[variantIndex].price = price;
                            selectedVariants[variantIndex].quantity = quantity;
                            
                            console.log(`Đã cập nhật biến thể #${variantIndex + 1}:`, selectedVariants[variantIndex]);
                        } else {
                            console.error('Không tìm thấy biến thể với index:', variantIndex);
                        }
                        
                        // Đóng form
                        variantForm.slideUp(300);
                        
                        // Hiển thị thông báo thành công
                        Swal.fire({
                            title: 'Thành công!',
                            text: 'Đã cập nhật thông tin biến thể',
                            icon: 'success',
                            confirmButtonText: 'OK',
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
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

            // Thêm sự kiện cho nút tạo biến thể tự động
            $('#generate-variants-btn').on('click', handleGenerateVariants);
        });
    </script>