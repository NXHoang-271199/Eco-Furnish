  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- nouislider js -->
    <script src="{{ asset('assets/admins/libs/nouislider/nouislider.min.js') }}"></script>
    <script src="{{ asset('assets/admins/libs/wnumb/wNumb.min.js') }}"></script>
    <!-- gridjs js -->
    <script src="{{ asset('assets/admins/libs/gridjs/gridjs.umd.js') }}"></script>
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
            // Đảm bảo các dòng đã ẩn trước khi khởi tạo (trạng thái mặc định trong CSS)
            setTimeout(function() {
                // Sau khi mọi thứ đã tải xong, bắt đầu hiệu ứng với độ trễ
                animateItems('#orderTable tbody tr', 100);
            }, 100);
            
            initTooltips();
            initDropdownAnimation();
            initRowHoverEffect();
            fixDropdownPosition();
            initDropdownZIndexHandling();
            
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
            
            // Xử lý sự kiện click bên ngoài dropdown menu để đóng
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu.show').removeClass('show');
                    $('#orderTable tr').removeClass('has-dropdown-open');
                }
            });
        });
        
        // Xử lý z-index cho dropdown menu khi được mở
        function initDropdownZIndexHandling() {
            // Khi dropdown mở, đặt z-index cho dòng để vượt qua các dòng khác
            $(document).on('show.bs.dropdown', '.dropdown', function() {
                $(this).closest('tr').addClass('has-dropdown-open');
                
                // Đảm bảo rằng tất cả các dòng khác có z-index thấp hơn
                $('#orderTable tr').not('.has-dropdown-open').css('z-index', '1');
                $('.has-dropdown-open').css('z-index', '1060');
            });
            
            // Khi dropdown đóng, reset z-index
            $(document).on('hide.bs.dropdown', '.dropdown', function() {
                $(this).closest('tr').removeClass('has-dropdown-open');
            });
        }
        
        // Sửa vị trí của dropdown menu để không bị cắt
        function fixDropdownPosition() {
            // Xử lý hiển thị dropdown menu
            $(document).on('show.bs.dropdown', '.dropdown', function() {
                var $this = $(this);
                var $menu = $this.find('.dropdown-menu');
                var $table = $this.closest('.table-responsive');
                
                // Đặt lại các thuộc tính vị trí
                $menu.css({
                    'position': 'absolute',
                    'top': '100%',
                    'left': 'auto',
                    'right': '0',
                    'transform': 'none',
                    'z-index': '9999'
                });
                
                // Kiểm tra xem dropdown có bị cắt bởi cạnh phải của màn hình không
                setTimeout(function() {
                    var menuRect = $menu[0].getBoundingClientRect();
                    var windowWidth = window.innerWidth;
                    
                    // Nếu menu bị cắt bởi cạnh phải
                    if (menuRect.right > windowWidth) {
                        $menu.css({
                            'right': '0',
                            'left': 'auto'
                        });
                    }
                    
                    // Kiểm tra xem dropdown có bị cắt bởi cạnh dưới của màn hình không
                    var menuBottom = menuRect.top + menuRect.height;
                    var windowHeight = window.innerHeight;
                    
                    if (menuBottom > windowHeight) {
                        $menu.css({
                            'top': 'auto',
                            'bottom': '100%',
                            'margin-bottom': '5px'
                        });
                    }
                    
                    // Đảm bảo menu hiển thị trên các phần tử khác
                    $menu.css('z-index', '9999');
                    
                    // Nếu là hàng cuối cùng của bảng, hiển thị dropdown phía trên
                    var $row = $this.closest('tr');
                    var $lastRow = $table.find('tr:last-child');
                    var $secondLastRow = $table.find('tr:nth-last-child(2)');
                    
                    if ($row.is($lastRow) || $row.is($secondLastRow)) {
                        $menu.css({
                            'top': 'auto',
                            'bottom': '100%',
                            'margin-bottom': '5px'
                        });
                    }
                    
                    // Xử lý trên màn hình nhỏ
                    if (window.innerWidth < 768) {
                        $menu.css({
                            'position': 'fixed',
                            'top': 'auto',
                            'left': '50%',
                            'bottom': '20%',
                            'right': 'auto',
                            'transform': 'translateX(-50%)',
                            'width': '80%',
                            'max-width': '250px'
                        });
                    }
                }, 0);
            });
        }
        
        // Khởi tạo tooltips cho các nút
        function initTooltips() {
            $('[data-bs-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                animation: true
            });
        }
        
        // Thêm hiệu ứng cho các dropdown
        function initDropdownAnimation() {
            $('.dropdown').on('show.bs.dropdown', function() {
                $(this).find('.dropdown-menu').first().stop(true, true).slideDown(200);
            });
            
            $('.dropdown').on('hide.bs.dropdown', function() {
                $(this).find('.dropdown-menu').first().stop(true, true).slideUp(200);
            });
        }
        
        // Hiệu ứng hover cho dòng sản phẩm
        function initRowHoverEffect() {
            $('#orderTable tbody tr').hover(
                function() {
                    // Kiểm tra xem có dropdown nào đang mở không
                    if ($('#orderTable tr.has-dropdown-open').length === 0) {
                        $(this).addClass('bg-light');
                        $(this).css('transition', 'background-color 0.3s ease');
                    } else {
                        // Chỉ thêm hiệu ứng hover cho dòng đang mở dropdown
                        if ($(this).hasClass('has-dropdown-open')) {
                            $(this).addClass('bg-light');
                            $(this).css('transition', 'background-color 0.3s ease');
                        }
                    }
                }, 
                function() {
                    $(this).removeClass('bg-light');
                }
            );
        }
        
        // Animation cho danh sách phần tử
        function animateItems(selector, delay) {
            const items = document.querySelectorAll(selector);
            
            // Đảm bảo rằng các hàng đã ẩn (opacity: 0)
            items.forEach(item => {
                item.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            });
            
            // Bắt đầu hiển thị các hàng với độ trễ
            items.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * delay);
            });
        }

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
                    confirmButtonText: 'Đóng',
                    customClass: {
                        popup: 'animated fadeInDown faster',
                        confirmButton: 'btn btn-primary'
                    },
                    showClass: {
                        popup: 'animated fadeInDown faster'
                    },
                    hideClass: {
                        popup: 'animated fadeOutUp faster'
                    }
                });
            } else {
                Swal.fire({
                    title: 'Đã lọc sản phẩm!',
                    html: `Tìm thấy ${visibleProducts} sản phẩm có giá trong khoảng ${currentFilters.minPrice.toLocaleString('vi-VN')} - ${currentFilters.maxPrice.toLocaleString('vi-VN')} VNĐ`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: {
                        popup: 'animated fadeInDown faster'
                    },
                    showClass: {
                        popup: 'animated fadeInDown faster'
                    },
                    hideClass: {
                        popup: 'animated fadeOutUp faster'
                    }
                });
                
                // Hiệu ứng highlight các sản phẩm được lọc
                highlightFilteredProducts();
            }
            
            // Đóng sidebar filter trên mobile sau khi lọc
            if (window.innerWidth < 768) {
                toggleFilter();
            }
        }
        
        // Tạo hiệu ứng highlight cho các sản phẩm sau khi lọc
        function highlightFilteredProducts() {
            const visibleRows = document.querySelectorAll('#orderTable tbody tr[style=""]');
            visibleRows.forEach(row => {
                row.style.transition = 'background-color 0.5s ease';
                row.style.backgroundColor = 'rgba(64, 81, 137, 0.08)';
                
                setTimeout(() => {
                    row.style.backgroundColor = '';
                }, 1000);
            });
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
                showConfirmButton: false,
                customClass: {
                    popup: 'animated fadeInDown faster'
                },
                showClass: {
                    popup: 'animated fadeInDown faster'
                },
                hideClass: {
                    popup: 'animated fadeOutUp faster'
                }
            });
            
            // Animation cho các sản phẩm
            animateItems('#orderTable tbody tr', 50);
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

                    // Hiệu ứng cho các category filters
                    animateElement(this);
                    
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
        
        // Hiệu ứng cho element được click
        function animateElement(el) {
            el.style.transition = 'transform 0.3s ease';
            el.style.transform = 'scale(1.05)';
            setTimeout(() => {
                el.style.transform = 'scale(1)';
            }, 300);
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Bạn có chắc chắn muốn xóa sản phẩm này?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Có, xóa!',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                showLoaderOnConfirm: true,
                allowOutsideClick: false,
                customClass: {
                    popup: 'animated fadeInDown faster',
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'animated fadeInDown faster'
                },
                hideClass: {
                    popup: 'animated fadeOutUp faster'
                },
                preConfirm: () => {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: `/admin/products/${id}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Animation xóa dòng sản phẩm
                                    const row = document.querySelector(`#orderTable tbody tr td button[onclick="confirmDelete(${id})"]`).closest('tr');
                                    row.style.transition = 'all 0.5s ease';
                                    row.style.opacity = '0';
                                    row.style.transform = 'translateX(20px)';
                                    
                                    setTimeout(() => {
                                        resolve(response);
                                    }, 500);
                                } else {
                                    reject(new Error(response.message || 'Có lỗi xảy ra khi xóa sản phẩm'));
                                }
                            },
                            error: function(xhr) {
                                reject(new Error('Có lỗi xảy ra khi xóa sản phẩm'));
                            }
                        });
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Thành công!',
                        text: result.value.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false,
                        customClass: {
                            popup: 'animated fadeInDown faster'
                        }
                    }).then(() => {
                        window.location.reload();
                    });
                }
            }).catch((error) => {
                Swal.fire({
                    title: 'Lỗi!',
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'animated fadeInDown faster'
                    }
                });
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
                
                // Animation cho các filter items
                animateItems('.filter-section', 100);
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
        
        // Hiệu ứng sóng nước khi click các nút
        document.addEventListener('DOMContentLoaded', function () {
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('click', function (e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const ripple = document.createElement('span');
                    ripple.className = 'ripple-effect';
                    ripple.style.position = 'absolute';
                    ripple.style.width = '100px';
                    ripple.style.height = '100px';
                    ripple.style.background = 'rgba(255, 255, 255, 0.4)';
                    ripple.style.borderRadius = '50%';
                    ripple.style.transform = 'translate(-50%, -50%) scale(0)';
                    ripple.style.top = y + 'px';
                    ripple.style.left = x + 'px';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.pointerEvents = 'none';
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });
        
        // Thêm CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: translate(-50%, -50%) scale(3);
                    opacity: 0;
                }
            }
            
            @keyframes fadeInDown {
                from {
                    opacity: 0;
                    transform: translate3d(0, -20px, 0);
                }
                to {
                    opacity: 1;
                    transform: translate3d(0, 0, 0);
                }
            }
            
            @keyframes fadeOutUp {
                from {
                    opacity: 1;
                }
                to {
                    opacity: 0;
                    transform: translate3d(0, -20px, 0);
                }
            }
            
            .animated {
                animation-duration: 0.5s;
                animation-fill-mode: both;
            }
            
            .fadeInDown {
                animation-name: fadeInDown;
            }
            
            .fadeOutUp {
                animation-name: fadeOutUp;
            }
            
            .faster {
                animation-duration: 0.3s;
            }
        `;
        document.head.appendChild(style);
    </script>