@extends('layouts.admin')

@section('title', 'Danh sách sản phẩm')

@section('CSS')
    <!-- nouisliderute css -->
    <link rel="stylesheet" href="{{ asset('assets/admins/libs/nouislider/nouislider.min.css') }}">
    <!-- gridjs css -->
    <link rel="stylesheet" href="{{ asset('assets/admins/libs/gridjs/theme/mermaid.min.css') }}">
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .category-filter {
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .category-filter:hover {
            background-color: #f8f9fa;
        }
        
        .category-filter.active {
            background-color: #405189;
            color: #fff !important;
        }
        
        .category-filter.active .listname {
            color: #fff !important;
        }

        /* Price Range Slider Styles */
        .price-range-wrapper {
            padding: 10px 5px;
        }

        .noUi-connect {
            background: #405189;
        }

        .noUi-handle {
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }

        .noUi-handle:before,
        .noUi-handle:after {
            display: none;
        }

        .price-input {
            position: relative;
            padding: 8px 12px;
            background: #fff;
            border: 1px solid #e2e5e8;
            border-radius: 4px;
            font-size: 14px;
            color: #495057;
            width: 100%;
        }

        .price-input:focus {
            border-color: #405189;
            outline: none;
        }

        .price-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .price-group .price-field {
            flex: 1;
        }

        .price-separator {
            color: #6c757d;
            font-weight: 500;
        }

        .price-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .filter-button {
            flex: 1;
            padding: 10px;
            background: #405189;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .filter-button:hover {
            background: #364574;
        }

        .filter-button i {
            margin-right: 8px;
        }

        .filter-button-reset {
            background: #6c757d;
        }

        .filter-button-reset:hover {
            background: #5a6268;
        }

        .d-flex.gap-2 {
            gap: 0.5rem !important;
        }

        /* Thêm style cho mũi tên phân trang */
        .pagination-nav {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .pagination-nav a {
            color: #6c757d;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .pagination-nav a:hover {
            background-color: #f8f9fa;
        }

        .pagination-info {
            color: #6c757d;
            font-size: 14px;
        }
    </style>
@endsection

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Sản phẩm</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Sản phẩm</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex mb-3">
                        <div class="flex-grow-1">
                            <h5 class="fs-16">Bộ lọc</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <button type="button" class="btn btn-soft-primary btn-sm">
                                <i class="ri-refresh-line align-middle"></i>
                            </button>
                        </div>
                    </div>

                    <div class="filter-choices-input">
                        <input class="form-control" data-choices data-choices-removeItem type="text" id="filter-choices-input" value="" />
                    </div>
                </div>

                <div class="accordion accordion-flush filter-accordion">
                    <div class="card-body border-bottom">
                        <div>
                            <p class="text-muted text-uppercase fs-12 fw-medium mb-2">Danh mục</p>
                            <ul class="list-unstyled mb-0 filter-list">
                                <li>
                                    <a href="#" class="d-flex py-1 align-items-center category-filter active" data-category-id="all">
                                        <div class="flex-grow-1">
                                            <h5 class="fs-13 mb-0 listname">Tất cả danh mục</h5>
                                        </div>
                                    </a>
                                </li>
                                @foreach($categories as $category)
                                <li>
                                    <a href="#" class="d-flex py-1 align-items-center category-filter" data-category-id="{{ $category->id }}">
                                        <div class="flex-grow-1">
                                            <h5 class="fs-13 mb-0 listname">{{ $category->name }}</h5>
                                        </div>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="card-body border-bottom">
                        <p class="text-muted text-uppercase fs-12 fw-medium mb-4">Giá (VNĐ)</p>

                        <div class="price-range-wrapper">
                            <div id="product-price-range"></div>
                            
                            <div class="price-group">
                                <div class="price-field">
                                    <div class="price-label">Từ</div>
                                    <input type="text" class="price-input" id="minCost" value="0" />
                                </div>
                                <div class="price-separator">-</div>
                                <div class="price-field">
                                    <div class="price-label">Đến</div>
                                    <input type="text" class="price-input" id="maxCost" value="100000000" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <button type="button" class="filter-button" onclick="filterProducts()">
                                <i class="ri-filter-line align-bottom"></i> Áp dụng bộ lọc
                            </button>
                            <button type="button" class="filter-button filter-button-reset" onclick="resetFilters()">
                                <i class="ri-refresh-line align-bottom"></i> Hủy lọc
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-9 col-lg-8">
            <div>
                <div class="card">
                    <div class="card-header border-0">
                        <div class="row g-4">
                            <div class="col-sm-auto">
                                <div class="d-flex gap-1">
                                    <a href="{{ route('products.create') }}" class="btn btn-success">
                                        <i class="ri-add-line align-bottom me-1"></i> Thêm sản phẩm
                                    </a>
                                    <a href="{{ route('variants.index') }}" class="btn btn-info">
                                        <i class="ri-list-check align-bottom me-1"></i> Quản lý biến thể
                                    </a>
                                </div>
                            </div>
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <input type="text" class="form-control" id="searchProductList" placeholder="Tìm kiếm sản phẩm...">
                                        <i class="ri-search-line search-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="tab-content text-muted">
                            <div class="tab-pane active" id="productnav-all" role="tabpanel">
                                <div id="table-product-list-all" class="table-card gridjs-border-none">
                                    <div class="table-responsive">
                                        <table class="table table-nowrap align-middle" id="orderTable">
                                            <thead class="text-muted table-light">
                                                <tr class="text-uppercase">
                                                    <th scope="col" style="width: 50px;">ID</th>
                                                    <th scope="col">Sản phẩm</th>
                                                    <th scope="col">Danh mục</th>
                                                    <th scope="col">Giá</th>
                                                    <th scope="col">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($products as $product)
                                                <tr>
                                                    <td>{{ $product->id }}</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="flex-shrink-0 me-3">
                                                                <div class="avatar-sm bg-light rounded p-1">
                                                                    <img src="{{ asset('storage/' . $product->image_thumnail) }}" alt="{{ $product->name }}" class="img-fluid d-block">
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h5 class="fs-14 mb-1">
                                                                    <a href="{{ route('products.show', $product->id) }}" class="text-dark">{{ $product->name }}</a>
                                                                </h5>
                                                                <p class="text-muted mb-0">Mã: <span class="fw-medium">{{ $product->product_code }}</span></p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                                    <td data-original-price="{{ $product->price }}" 
                                                        @if($product->variants->count() > 0)
                                                        data-min-variant-price="{{ $product->variants->min('price') }}"
                                                        data-max-variant-price="{{ $product->variants->max('price') }}"
                                                        @endif>
                                                        <div>
                                                            <strong>Giá gốc:</strong> {{ number_format($product->price) }} VNĐ
                                                        </div>
                                                        @if($product->variants->count() > 0)
                                                            <div class="mt-2">
                                                                <strong>Giá biến thể:</strong> 
                                                                @php
                                                                    $minPrice = $product->variants->min('price');
                                                                    $maxPrice = $product->variants->max('price');
                                                                @endphp
                                                                @if($minPrice === $maxPrice)
                                                                    {{ number_format($minPrice) }} VNĐ
                                                                @else
                                                                    {{ number_format($minPrice) }} - {{ number_format($maxPrice) }} VNĐ
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown d-inline-block">
                                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="ri-more-fill align-middle"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a href="{{ route('products.show', $product->id) }}" class="dropdown-item">
                                                                        <i class="ri-eye-fill align-bottom me-2 text-muted"></i> Chi tiết
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="{{ route('products.edit', $product->id) }}" class="dropdown-item">
                                                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Sửa
                                                                    </a>
                                                                </li>
                                                                <li class="dropdown-divider"></li>
                                                                <li>
                                                                    <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="dropdown-item text-danger">
                                                                            <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Xóa
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-end mt-3">
                                        <div class="pagination-nav">
                                            @if($products->onFirstPage())
                                                <span class="text-muted">« Previous</span>
                                            @else
                                                <a href="{{ $products->previousPageUrl() }}">« Previous</a>
                                            @endif

                                            <span class="pagination-info">
                                                Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
                                            </span>

                                            @if($products->hasMorePages())
                                                <a href="{{ $products->nextPageUrl() }}">Next »</a>
                                            @else
                                                <span class="text-muted">Next »</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('JS')
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
        });

        function getProductPrices(priceCell) {
            // Lấy giá gốc
            const originalPriceText = priceCell.querySelector('div:first-child').textContent;
            const originalPrice = parseInt(originalPriceText.match(/\d+([.,]\d+)?/)[0].replace(/[,.]/g, ''));
            
            const prices = [originalPrice];
            
            // Lấy giá biến thể nếu có
            const variantPriceDiv = priceCell.querySelector('div.mt-2');
            if (variantPriceDiv) {
                const variantText = variantPriceDiv.textContent;
                const priceMatches = variantText.match(/\d{1,3}(,\d{3})*(\.\d+)?/g);
                if (priceMatches) {
                    priceMatches.forEach(price => {
                        prices.push(parseInt(price.replace(/[,.]/g, '')));
                    });
                }
            }
            
            return prices;
        }

        function applyPriceFilter() {
            // Lấy giá trị từ input và xử lý
            let minPriceInput = document.getElementById('minCost').value;
            let maxPriceInput = document.getElementById('maxCost').value;
            
            // Xóa dấu phẩy và chấm trong chuỗi số
            minPriceInput = minPriceInput.replace(/[,.]/g, '');
            maxPriceInput = maxPriceInput.replace(/[,.]/g, '');
            
            // Chuyển đổi thành số và đảm bảo giá trị hợp lệ
            let minPrice = parseInt(minPriceInput);
            let maxPrice = parseInt(maxPriceInput);

            // Đảm bảo giá trị hợp lệ
            minPrice = isNaN(minPrice) ? 0 : minPrice;
            maxPrice = isNaN(maxPrice) ? 100000000 : maxPrice;
            
            console.log('Filtering with price range:', { minPrice, maxPrice });

            const productRows = document.querySelectorAll('#orderTable tbody tr');
            let visibleProducts = 0;

            productRows.forEach(row => {
                const priceCell = row.querySelector('td:nth-child(4)');
                const categoryCell = row.querySelector('td:nth-child(3)');
                const categoryName = categoryCell.textContent.trim();

                // Kiểm tra điều kiện danh mục
                const matchesCategory = selectedCategoryId === 'all' || 
                    categoryName === document.querySelector(`.category-filter[data-category-id="${selectedCategoryId}"] .listname`).textContent.trim();

                // Kiểm tra điều kiện giá
                let matchesPrice = false;

                // Lấy giá gốc và giá biến thể từ data attributes
                const originalPrice = parseInt(priceCell.getAttribute('data-original-price'));
                const minVariantPrice = priceCell.hasAttribute('data-min-variant-price') ? 
                    parseInt(priceCell.getAttribute('data-min-variant-price')) : null;
                const maxVariantPrice = priceCell.hasAttribute('data-max-variant-price') ? 
                    parseInt(priceCell.getAttribute('data-max-variant-price')) : null;

                // Kiểm tra giá gốc
                if (originalPrice <= maxPrice) {
                    if (minPrice === 0 || originalPrice >= minPrice) {
                        matchesPrice = true;
                    }
                }

                // Kiểm tra giá biến thể nếu có và chưa match giá gốc
                if (!matchesPrice && minVariantPrice !== null && maxVariantPrice !== null) {
                    // Kiểm tra nếu có bất kỳ giá biến thể nào trong khoảng
                    if (minVariantPrice <= maxPrice && (minPrice === 0 || maxVariantPrice >= minPrice)) {
                        matchesPrice = true;
                    }
                }

                // Hiển thị hoặc ẩn sản phẩm
                if (matchesCategory && matchesPrice) {
                    row.style.display = '';
                    visibleProducts++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Hiển thị thông báo kết quả lọc giá
            if (visibleProducts === 0) {
                Swal.fire({
                    title: 'Không tìm thấy sản phẩm!',
                    html: `Không có sản phẩm nào có giá trong khoảng ${minPrice.toLocaleString('vi-VN')} - ${maxPrice.toLocaleString('vi-VN')} VNĐ<br>
                          Vui lòng thử lại với khoảng giá khác.`,
                    icon: 'info',
                    confirmButtonText: 'Đóng'
                });
            } else {
                Swal.fire({
                    title: 'Đã lọc sản phẩm!',
                    html: `Tìm thấy ${visibleProducts} sản phẩm có giá trong khoảng ${minPrice.toLocaleString('vi-VN')} - ${maxPrice.toLocaleString('vi-VN')} VNĐ`,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        }

        function applyCategoryFilter() {
            const productRows = document.querySelectorAll('#orderTable tbody tr');
            
            productRows.forEach(row => {
                const categoryCell = row.querySelector('td:nth-child(3)');
                const categoryName = categoryCell.textContent.trim();
                
                // Kiểm tra điều kiện danh mục
                const matchesCategory = selectedCategoryId === 'all' || 
                    categoryName === document.querySelector(`.category-filter[data-category-id="${selectedCategoryId}"] .listname`).textContent.trim();

                // Hiển thị hoặc ẩn sản phẩm
                row.style.display = matchesCategory ? '' : 'none';
            });
        }

        function filterProducts() {
            applyPriceFilter();
        }

        function resetFilters() {
            // Reset giá trị slider và input về mặc định
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

            // Hiển thị lại tất cả sản phẩm
            const productRows = document.querySelectorAll('#orderTable tbody tr');
            productRows.forEach(row => {
                row.style.display = '';
            });

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
    </script>
@endsection 