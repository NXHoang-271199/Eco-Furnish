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
<<<<<<< HEAD

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

        /* Tối giản hóa thanh trượt */
        .noUi-target {
            height: 4px;
            border: none;
            box-shadow: none;
            background: #e9ecef;
            margin: 15px 0 30px;
        }

        .noUi-handle {
            width: 18px !important;
            height: 18px !important;
            border-radius: 50%;
            box-shadow: none;
            border: 2px solid #405189;
            background: #fff;
            cursor: pointer;
            right: -9px !important;
        }

        .noUi-connect {
            background: #405189;
        }

        /* Ẩn các đường kẻ trong handle */
        .noUi-handle:before,
        .noUi-handle:after {
            display: none;
        }

        /* Style cho input giá */
        .price-input {
            border: 1px solid #e2e5e8;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
            color: #495057;
            width: 100%;
            transition: all 0.3s ease;
        }

        .price-input:focus {
            border-color: #405189;
            outline: none;
            box-shadow: 0 0 0 2px rgba(64, 81, 137, 0.1);
        }

        /* Style cho ô tìm kiếm sản phẩm */
        .filter-search {
            position: relative;
            margin-bottom: 20px;
        }

        .filter-search input {
            width: 100%;
            padding: 10px 15px;
            padding-right: 35px;
            border: 1px solid #e2e5e8;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .filter-search input:focus {
            border-color: #405189;
            outline: none;
            box-shadow: 0 0 0 2px rgba(64, 81, 137, 0.1);
        }

        .filter-search i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        /* Style cho offcanvas filter */
        .offcanvas-filter {
            position: fixed;
            top: 0;
            left: -100%;
            width: 300px;
            height: 100%;
            background: #fff;
            z-index: 1045;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            overflow-y: auto;
        }

        .offcanvas-filter.show {
            left: 0;
        }

        .offcanvas-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            display: none;
        }

        .offcanvas-backdrop.show {
            display: block;
        }

        /* Style cho phân trang */
        .pagination-wrapper {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #fff;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px rgba(56, 65, 74, 0.15);
        }

        .pagination-info {
            color: #878a99;
            font-size: 13px;
        }

        .pagination {
            margin: 0;
            display: flex;
            gap: 5px;
        }

        .pagination .page-link {
            border: 1px solid #e9ebec;
            color: #405189;
            background-color: #fff;
            padding: 0.5rem 0.75rem;
            font-size: 13px;
            font-weight: 500;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background-color: #405189;
            color: #fff;
            border-color: #405189;
        }

        .pagination .page-link.disabled {
            color: #878a99;
            pointer-events: none;
            background-color: #f3f6f9;
            border-color: #e9ebec;
        }

        .pagination .page-link.active {
            background-color: #405189;
            color: #fff;
            border-color: #405189;
        }
=======
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
    </style>
@endsection

@section('content')
<<<<<<< HEAD
=======
    <!-- start page title -->
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Sản phẩm</h4>
<<<<<<< HEAD
                <div class="page-title-right">
                    <button type="button" class="btn btn-primary" onclick="toggleFilter()">
                        <i class="ri-filter-2-line align-bottom me-1"></i> Bộ lọc
                    </button>
=======

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Sản phẩm</li>
                    </ol>
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
                </div>
            </div>
        </div>
    </div>
<<<<<<< HEAD

    <!-- Offcanvas Filter -->
    <div class="offcanvas-filter" id="filterOffcanvas">
        <div class="card">
            <div class="card-header border-bottom">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Bộ lọc</h5>
                    <button type="button" class="btn-close" onclick="toggleFilter()"></button>
                </div>
            </div>

            <div class="card-body">
                <div class="filter-search mb-4">
                    <input type="text" id="searchProduct" placeholder="Tìm kiếm theo tên hoặc mã sản phẩm..." class="form-control">
                    <i class="ri-search-line"></i>
                </div>

                <div class="filter-section mb-4">
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

                <div class="filter-section mb-4">
                    <p class="text-muted text-uppercase fs-12 fw-medium mb-4">Giá (VNĐ)</p>
                    <div class="price-range-wrapper">
                        <div id="product-price-range"></div>
                        <div class="price-group mt-3">
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

                <div class="d-flex gap-2">
                    <button type="button" class="filter-button flex-grow-1" onclick="filterProducts()">
                        <i class="ri-filter-line align-bottom"></i> Áp dụng
                    </button>
                    <button type="button" class="filter-button filter-button-reset flex-grow-1" onclick="resetFilters()">
                        <i class="ri-refresh-line align-bottom"></i> Hủy
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="offcanvas-backdrop" onclick="toggleFilter()"></div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
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
                                                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="delete-form">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="button" class="dropdown-item text-danger" onclick="confirmDelete(this)">
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
                                <div class="pagination-wrapper">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="pagination-info">
                                            Hiển thị {{ $products->firstItem() }} đến {{ $products->lastItem() }} 
                                            trên tổng số {{ $products->total() }} sản phẩm
                                        </div>
                                        <ul class="pagination">
                                            {{-- Nút Previous --}}
                                            <li class="page-item">
                                                <a class="page-link {{ $products->onFirstPage() ? 'disabled' : '' }}" 
                                                   href="{{ $products->previousPageUrl() }}" 
                                                   {{ $products->onFirstPage() ? 'tabindex="-1"' : '' }}>
                                                    <i class="ri-arrow-left-s-line"></i>
                                                </a>
                                            </li>

                                            {{-- Hiển thị các số trang --}}
                                            @for ($i = 1; $i <= $products->lastPage(); $i++)
                                                <li class="page-item">
                                                    <a class="page-link {{ $products->currentPage() == $i ? 'active' : '' }}" 
                                                       href="{{ $products->url($i) }}">
                                                        {{ $i }}
                                                    </a>
                                                </li>
                                            @endfor

                                            {{-- Nút Next --}}
                                            <li class="page-item">
                                                <a class="page-link {{ !$products->hasMorePages() ? 'disabled' : '' }}" 
                                                   href="{{ $products->nextPageUrl() }}"
                                                   {{ !$products->hasMorePages() ? 'tabindex="-1"' : '' }}>
                                                    <i class="ri-arrow-right-s-line"></i>
                                                </a>
                                            </li>
                                        </ul>
=======
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
                                                    <td>
                                                        <div>
                                                            <strong>Giá gốc:</strong> {{ number_format($product->price) }} VNĐ
                                                        </div>
                                                        @if($product->variants->count() > 0)
                                                            <div class="mt-2">
                                                                <strong>Giá biến thể:</strong> 
                                                                {{ number_format($product->variants->min('price')) }} - 
                                                                {{ number_format($product->variants->max('price')) }} VNĐ
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
                                        {{ $products->links() }}
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
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
<<<<<<< HEAD
        let searchTimeout;
        let currentFilters = {
            search: '',
            category: 'all',
            minPrice: 0,
            maxPrice: 100000000
        };
=======
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
        
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
<<<<<<< HEAD

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
=======
        });

        function getProductPrice(priceCell) {
            // Lấy giá gốc
            const originalPriceText = priceCell.querySelector('div:first-child').textContent;
            const originalPrice = parseInt(originalPriceText.match(/\d+([.,]\d+)?/)[0].replace(/[,.]/g, ''));
            
            // Lấy giá biến thể nếu có
            const variantPriceDiv = priceCell.querySelector('div.mt-2');
            if (variantPriceDiv) {
                const variantPrices = variantPriceDiv.textContent.match(/\d+([.,]\d+)?/g)
                    .map(price => parseInt(price.replace(/[,.]/g, '')));
                return {
                    original: originalPrice,
                    variants: variantPrices,
                    min: Math.min(originalPrice, ...variantPrices),
                    max: Math.max(originalPrice, ...variantPrices)
                };
            }
            
            return {
                original: originalPrice,
                variants: [],
                min: originalPrice,
                max: originalPrice
            };
        }

        function applyFilters() {
            const minPriceInput = document.getElementById('minCost').value.replace(/[,.]/g, '');
            const maxPriceInput = document.getElementById('maxCost').value.replace(/[,.]/g, '');
            
            // Chuyển đổi giá trị input thành số
            const minPrice = parseInt(minPriceInput);
            const maxPrice = parseInt(maxPriceInput);
            
            // Kiểm tra nếu giá trị không hợp lệ
            if (isNaN(minPrice) || isNaN(maxPrice) || minPrice < 0 || maxPrice < minPrice) {
                Swal.fire({
                    title: 'Lỗi!',
                    text: 'Vui lòng nhập khoảng giá hợp lệ',
                    icon: 'error',
                    confirmButtonText: 'Đóng'
                });
                return;
            }

>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
            const productRows = document.querySelectorAll('#orderTable tbody tr');
            let visibleProducts = 0;

            productRows.forEach(row => {
<<<<<<< HEAD
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
=======
                const priceCell = row.querySelector('td:nth-child(4)');
                const categoryCell = row.querySelector('td:nth-child(3)');
                const priceInfo = getProductPrice(priceCell);
                const categoryName = categoryCell.textContent.trim();

                // Kiểm tra điều kiện danh mục
                const matchesCategory = selectedCategoryId === 'all' || 
                    categoryName === document.querySelector(`.category-filter[data-category-id="${selectedCategoryId}"] .listname`).textContent.trim();

                // Kiểm tra điều kiện giá
                let matchesPrice = false;

                // Kiểm tra giá gốc
                const originalPriceInRange = priceInfo.original >= minPrice && priceInfo.original <= maxPrice;

                if (priceInfo.variants.length > 0) {
                    // Nếu có biến thể, kiểm tra xem có ít nhất một biến thể nằm trong khoảng không
                    const hasVariantInRange = priceInfo.variants.some(price => price >= minPrice && price <= maxPrice);
                    // Chỉ hiển thị nếu giá gốc hoặc ít nhất một biến thể nằm trong khoảng
                    matchesPrice = originalPriceInRange || hasVariantInRange;
                } else {
                    // Nếu không có biến thể, chỉ kiểm tra giá gốc
                    matchesPrice = originalPriceInRange;
                }

                if (matchesCategory && matchesPrice) {
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
                    row.style.display = '';
                    visibleProducts++;
                } else {
                    row.style.display = 'none';
                }
            });

<<<<<<< HEAD
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
=======
            // Hiển thị thông báo nếu không có sản phẩm nào
            if (visibleProducts === 0) {
                Swal.fire({
                    title: 'Không tìm thấy sản phẩm!',
                    text: 'Không có sản phẩm nào phù hợp với điều kiện lọc',
                    icon: 'info',
                    confirmButtonText: 'Đóng'
                });
            }
        }

        function filterProducts() {
            applyFilters();
        }

        function resetFilters() {
            // Reset giá trị slider và input về mặc định
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
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

<<<<<<< HEAD
            // Áp dụng lại tất cả bộ lọc
            const visibleProducts = applyAllFilters();
=======
            // Hiển thị lại tất cả sản phẩm
            const productRows = document.querySelectorAll('#orderTable tbody tr');
            productRows.forEach(row => {
                row.style.display = '';
            });
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1

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

<<<<<<< HEAD
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

        function confirmDelete(button) {
            Swal.fire({
                title: 'Xác nhận xóa?',
                text: "Bạn có chắc chắn muốn xóa sản phẩm này không? Hành động này không thể hoàn tác!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Có, xóa!',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
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
=======
                    // Áp dụng cả hai bộ lọc
                    applyFilters();
                });
            });
>>>>>>> 5a20f9f40f8927cca6e44e85fa82181d1ef73bd1
        });
    </script>
@endsection 