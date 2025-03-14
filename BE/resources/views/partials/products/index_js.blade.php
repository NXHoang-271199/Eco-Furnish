  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- nouislider js -->
    <script src="{{ asset('assets/admins/libs/nouislider/nouislider.min.js') }}"></script>
    <script src="{{ asset('assets/admins/libs/wnumb/wNumb.min.js') }}"></script>
    <!-- gridjs js -->
    <script src="{{ asset('assets/admins/libs/gridjs/gridjs.umd.js') }}"></script>
    <!-- ecommerce product list -->
    <script src="{{ asset('assets/admins/js/pages/ecommerce-product-list.init.js') }}"></script>
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        let priceRangeSlider;
        let selectedCategoryId = 'all';
        let searchTimeout;
        let currentFilters = {
            search: '',
            category: 'all',
            minPrice: 0,
            maxPrice: 100000000
        };
        
        $(document).ready(function() {
            // Khởi tạo price range slider
            priceRangeSlider = document.getElementById('product-price-range');
            if (priceRangeSlider) {
                noUiSlider.create(priceRangeSlider, {
                    start: [0, 100000000],
                    connect: true,
                    step: 100000,
                    format: wNumb({
                        decimals: 0,
                        thousand: '.',
                    }),
                    range: {
                        'min': 0,
                        'max': 100000000
                    }
                });

                const input0 = document.getElementById('minCost');
                const input1 = document.getElementById('maxCost');
                const inputs = [input0, input1];

                // Cập nhật giá trị input khi kéo slider
                priceRangeSlider.noUiSlider.on('update', function(values, handle) {
                    inputs[handle].value = values[handle];
                });

                // Cập nhật slider khi thay đổi input
                inputs.forEach(function(input, handle) {
                    input.addEventListener('change', function() {
                        let value = this.value;
                        value = value.replace(/[,.]/g, '');
                        value = parseInt(value) || 0;
                        priceRangeSlider.noUiSlider.setHandle(handle, value);
                    });

                    input.addEventListener('blur', function() {
                        let value = this.value;
                        value = value.replace(/[,.]/g, '');
                        value = parseInt(value) || 0;
                        this.value = value.toLocaleString('vi-VN');
                    });
                });
            }

            // Xử lý tìm kiếm sản phẩm
            $('#searchProduct').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    currentFilters.search = $('#searchProduct').val().toLowerCase().trim();
                    applyAllFilters();
                }, 300);
            });
        });

        function applyAllFilters() {
            const productRows = document.querySelectorAll('#orderTable tbody tr');
            let visibleProducts = 0;

            productRows.forEach(row => {
                const nameCell = row.querySelector('td:nth-child(2)');
                const productName = nameCell.textContent.toLowerCase();
                const productCode = nameCell.querySelector('p.text-muted').textContent.toLowerCase();
                const categoryCell = row.querySelector('td:nth-child(3)');
                const categoryName = categoryCell.textContent.trim();
                const priceCell = row.querySelector('td:nth-child(4)');

                // Kiểm tra điều kiện tìm kiếm
                const matchesSearch = currentFilters.search === '' || 
                    productName.includes(currentFilters.search) || 
                    productCode.includes(currentFilters.search);

                // Kiểm tra điều kiện danh mục
                const matchesCategory = currentFilters.category === 'all' || 
                    categoryName === document.querySelector(`.category-filter[data-category-id="${currentFilters.category}"] .listname`).textContent.trim();

                // Kiểm tra điều kiện giá
                let matchesPrice = false;
                const originalPrice = parseInt(priceCell.getAttribute('data-original-price'));
                const minVariantPrice = priceCell.hasAttribute('data-min-variant-price') ? 
                    parseInt(priceCell.getAttribute('data-min-variant-price')) : null;
                const maxVariantPrice = priceCell.hasAttribute('data-max-variant-price') ? 
                    parseInt(priceCell.getAttribute('data-max-variant-price')) : null;

                if (originalPrice <= currentFilters.maxPrice) {
                    if (currentFilters.minPrice === 0 || originalPrice >= currentFilters.minPrice) {
                        matchesPrice = true;
                    }
                }

                if (!matchesPrice && minVariantPrice !== null && maxVariantPrice !== null) {
                    if (minVariantPrice <= currentFilters.maxPrice && 
                        (currentFilters.minPrice === 0 || maxVariantPrice >= currentFilters.minPrice)) {
                        matchesPrice = true;
                    }
                }

                // Hiển thị hoặc ẩn sản phẩm dựa trên tất cả điều kiện
                if (matchesSearch && matchesCategory && matchesPrice) {
                    row.style.display = '';
                    visibleProducts++;
                } else {
                    row.style.display = 'none';
                }
            });

            return visibleProducts;
        }

        function applyCategoryFilter() {
            currentFilters.category = selectedCategoryId;
            const visibleProducts = applyAllFilters();
        }

        function filterProducts() {
            // Cập nhật giá trị filter
            let minPriceInput = document.getElementById('minCost').value.replace(/[,.]/g, '');
            let maxPriceInput = document.getElementById('maxCost').value.replace(/[,.]/g, '');
            
            currentFilters.minPrice = parseInt(minPriceInput) || 0;
            currentFilters.maxPrice = parseInt(maxPriceInput) || 100000000;

            const visibleProducts = applyAllFilters();

            // Hiển thị thông báo kết quả lọc
            if (visibleProducts === 0) {
                Swal.fire({
                    title: 'Không tìm thấy sản phẩm!',
                    html: `Không có sản phẩm nào có giá trong khoảng ${currentFilters.minPrice.toLocaleString('vi-VN')} - ${currentFilters.maxPrice.toLocaleString('vi-VN')} VNĐ<br>
                          Vui lòng thử lại với khoảng giá khác.`,
                    icon: 'info',
                    confirmButtonText: 'Đóng'
                });
            } else {
                Swal.fire({
                    title: 'Đã lọc sản phẩm!',
                    html: `Tìm thấy ${visibleProducts} sản phẩm có giá trong khoảng ${currentFilters.minPrice.toLocaleString('vi-VN')} - ${currentFilters.maxPrice.toLocaleString('vi-VN')} VNĐ`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }

        function resetFilters() {
            // Reset tất cả giá trị về mặc định
            currentFilters = {
                search: '',
                category: 'all',
                minPrice: 0,
                maxPrice: 100000000
            };

            // Reset search input
            document.getElementById('searchProduct').value = '';

            // Reset giá trị slider và input
            if (priceRangeSlider) {
                priceRangeSlider.noUiSlider.set([0, 100000000]);
            }
            document.getElementById('minCost').value = '0';
            document.getElementById('maxCost').value = '100.000.000';

            // Reset category filter
            selectedCategoryId = 'all';
            document.querySelectorAll('.category-filter').forEach(filter => {
                filter.classList.remove('active');
            });
            document.querySelector('.category-filter[data-category-id="all"]').classList.add('active');

            // Áp dụng lại tất cả bộ lọc
            const visibleProducts = applyAllFilters();

            // Thông báo đã reset
            Swal.fire({
                title: 'Đã hủy bộ lọc!',
                text: 'Tất cả sản phẩm đã được hiển thị lại',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý lọc theo danh mục
            const categoryFilters = document.querySelectorAll('.category-filter');

            categoryFilters.forEach(filter => {
                filter.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Cập nhật trạng thái active và lưu category đã chọn
                    categoryFilters.forEach(f => f.classList.remove('active'));
                    this.classList.add('active');
                    selectedCategoryId = this.getAttribute('data-category-id');

                    // Chỉ áp dụng lọc danh mục
                    applyCategoryFilter();
                });
            });

            // Thêm sự kiện click cho nút áp dụng bộ lọc
            const filterButton = document.querySelector('.filter-button');
            if (filterButton) {
                filterButton.onclick = filterProducts;
            }
        });

        function confirmDelete(id) {
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Bạn có chắc chắn muốn xóa sản phẩm này?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Có, xóa!',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/products/${id}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Thành công!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    window.location.reload();
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
                            Swal.fire({
                                title: 'Lỗi!',
                                text: 'Có lỗi xảy ra khi xóa sản phẩm',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                }
            });
        }

        function toggleFilter() {
            const filter = document.getElementById('filterOffcanvas');
            const backdrop = document.querySelector('.offcanvas-backdrop');
            
            if (filter.classList.contains('show')) {
                filter.classList.remove('show');
                backdrop.classList.remove('show');
                document.body.style.overflow = '';
            } else {
                filter.classList.add('show');
                backdrop.classList.add('show');
                document.body.style.overflow = 'hidden';
            }
        }

        // Thêm event listener cho Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const filter = document.getElementById('filterOffcanvas');
                if (filter.classList.contains('show')) {
                    toggleFilter();
                }
            }
        });
    </script>