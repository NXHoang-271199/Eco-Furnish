@extends('layouts.admin')

@section('title', 'Danh sách sản phẩm')

@section('CSS')
    @include('partials.products.index_css')
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Sản phẩm</h4>
                <div class="page-title-right">
                    <button type="button" class="btn btn-primary" onclick="toggleFilter()">
                        <i class="ri-filter-2-line align-bottom me-1"></i> Bộ lọc
                    </button>
                </div>
            </div>
        </div>
    </div>

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
                                <a href="{{ route('trash.products') }}" class="btn btn-warning">
                                    <i class="ri-delete-bin-line align-bottom me-1"></i> Thùng rác
                                </a>
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
                                                                <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $product->id }})">
                                                                    <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Xóa
                                                                </button>
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
   @include('partials.products.index_js')
@endsection