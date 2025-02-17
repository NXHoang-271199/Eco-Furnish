@extends('admins.layouts.admin')

@section('title', 'Chi tiết sản phẩm')

@section('css')
    <!-- Swiper css -->
    <link href="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Chi tiết sản phẩm</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Sản phẩm</a></li>
                        <li class="breadcrumb-item active">Chi tiết sản phẩm</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row gx-lg-5">
                        <!-- Phần hình ảnh sản phẩm -->
                        <div class="col-xl-4 col-md-8 mx-auto">
                            <div class="product-img-slider sticky-side-div">
                                <div class="swiper product-thumbnail-slider p-2 rounded bg-light">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <img src="{{ asset('storage/' . $product->image_thumnail) }}" alt="{{ $product->name }}" class="img-fluid d-block" />
                                        </div>
                                        @if($product->images)
                                            @foreach(json_decode($product->images) as $image)
                                                <div class="swiper-slide">
                                                    <img src="{{ asset('storage/' . $image) }}" alt="{{ $product->name }}" class="img-fluid d-block" />
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="swiper-button-next"></div>
                                    <div class="swiper-button-prev"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Phần thông tin sản phẩm -->
                        <div class="col-xl-8">
                            <div class="mt-xl-0 mt-5">
                                <div class="row mt-4">
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="p-2 border border-dashed rounded">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1">Tên sản phẩm:</h5>
                                                    <p class="text-muted mb-0">{{ $product->name }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="p-2 border border-dashed rounded">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1">Mã sản phẩm:</h5>
                                                    <p class="text-muted mb-0">{{ $product->product_code }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="p-2 border border-dashed rounded">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1">Danh mục:</h5>
                                                    <p class="text-muted mb-0">{{ $product->category->name ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @if(!$product->variants->count())
                                    <div class="col-lg-6 col-sm-6">
                                        <div class="p-2 border border-dashed rounded">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-grow-1">
                                                    <h5 class="mb-1">Giá:</h5>
                                                    <p class="text-muted mb-0">{{ number_format($product->price) }} VNĐ</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>

                                @if($product->description)
                                <div class="mt-4">
                                    <h5 class="fs-14 mb-3">Mô tả sản phẩm:</h5>
                                    <div class="text-muted">
                                        {!! $product->description !!}
                                    </div>
                                </div>
                                @endif

                                @if($product->variants->count() > 0)
                                <div class="mt-4">
                                    <h5 class="fs-14 mb-3">Biến thể sản phẩm:</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-nowrap align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col">Mã SKU</th>
                                                    <th scope="col">Biến thể</th>
                                                    <th scope="col">Giá</th>
                                                    <th scope="col">Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($product->variants as $variant)
                                                <tr>
                                                    <td>{{ $variant->sku }}</td>
                                                    <td>
                                                        <div>
                                                            {{ $variant->variant->name ?? '' }}: 
                                                            {{ $variant->variantValue->value ?? '' }}
                                                        </div>
                                                    </td>
                                                    <td>{{ number_format($variant->price) }} VNĐ</td>
                                                    <td>
                                                        @if($variant->status)
                                                            <span class="badge bg-success">Đang bán</span>
                                                        @else
                                                            <span class="badge bg-danger">Ngừng bán</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                <div class="mt-4 d-flex gap-2">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">
                                        <i class="ri-pencil-fill align-bottom me-1"></i> Sửa
                                    </a>
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-light">
                                        <i class="ri-arrow-left-line align-bottom me-1"></i> Quay lại
                                    </a>
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
    <!-- Swiper js -->
    <script src="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Khởi tạo Swiper cho slider hình ảnh
            new Swiper(".product-thumbnail-slider", {
                spaceBetween: 10,
                slidesPerView: 1,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                autoplay: {
                    delay: 2500,
                    disableOnInteraction: false,
                },
            });
        });
    </script>
@endsection 