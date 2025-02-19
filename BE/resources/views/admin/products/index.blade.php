@extends('admins.layouts.admin')

@section('title', 'Danh sách sản phẩm')

@section('css')
    <!-- nouisliderute css -->
    <link rel="stylesheet" href="{{ asset('assets/admins/libs/nouislider/nouislider.min.css') }}">
    <!-- gridjs css -->
    <link rel="stylesheet" href="{{ asset('assets/admins/libs/gridjs/theme/mermaid.min.css') }}">
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Sản phẩm</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
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
                                @foreach($categories as $category)
                                <li>
                                    <a href="#" class="d-flex py-1 align-items-center">
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
                        <p class="text-muted text-uppercase fs-12 fw-medium mb-4">Giá</p>

                        <div id="product-price-range" data-slider-color="primary"></div>
                        <div class="formCost d-flex gap-2 align-items-center mt-3">
                            <input class="form-control form-control-sm" type="text" id="minCost" value="0" /> <span class="fw-semibold text-muted">đến</span> <input class="form-control form-control-sm" type="text" id="maxCost" value="1000" />
                        </div>
                    </div>

                    <div class="card-body">
                        <div>
                            <button type="button" class="btn btn-primary w-100" onclick="filterProducts()">
                                <i class="ri-filter-line align-bottom me-1"></i> Lọc
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
                                    <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                                        <i class="ri-add-line align-bottom me-1"></i> Thêm sản phẩm
                                    </a>
                                    <a href="{{ route('admin.variants.index') }}" class="btn btn-info">
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
                                                                    <a href="{{ route('admin.products.show', $product->id) }}" class="text-dark">{{ $product->name }}</a>
                                                                </h5>
                                                                <p class="text-muted mb-0">Mã: <span class="fw-medium">{{ $product->product_code }}</span></p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $product->category->name ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($product->variants->count() > 0)
                                                            {{ number_format($product->variants->min('price')) }} - 
                                                            {{ number_format($product->variants->max('price')) }} VNĐ
                                                        @else
                                                            {{ number_format($product->price) }} VNĐ
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="dropdown d-inline-block">
                                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="ri-more-fill align-middle"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end">
                                                                <li>
                                                                    <a href="{{ route('admin.products.show', $product->id) }}" class="dropdown-item">
                                                                        <i class="ri-eye-fill align-bottom me-2 text-muted"></i> Chi tiết
                                                                    </a>
                                                                </li>
                                                                <li>
                                                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="dropdown-item">
                                                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Sửa
                                                                    </a>
                                                                </li>
                                                                <li class="dropdown-divider"></li>
                                                                <li>
                                                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
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

@section('js')
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
        $(document).ready(function() {
         

            // Khởi tạo price range slider
            var priceRangeSlider = document.getElementById('product-price-range');
            if (priceRangeSlider) {
                noUiSlider.create(priceRangeSlider, {
                    start: [0, 1000],
                    connect: true,
                    step: 10,
                    range: {
                        'min': 0,
                        'max': 1000
                    }
                });

                const input0 = document.getElementById('minCost');
                const input1 = document.getElementById('maxCost');
                const inputs = [input0, input1];

                priceRangeSlider.noUiSlider.on('update', function(values, handle) {
                    inputs[handle].value = Math.round(values[handle]);
                });

                inputs.forEach(function(input, handle) {
                    input.addEventListener('change', function() {
                        priceRangeSlider.noUiSlider.setHandle(handle, this.value);
                    });
                });
            }
        });

        function filterProducts() {
            // Implement your filter logic here
            const minPrice = document.getElementById('minCost').value;
            const maxPrice = document.getElementById('maxCost').value;
            
            // Add your filter implementation
            console.log({minPrice, maxPrice});
        }
    </script>
@endsection 