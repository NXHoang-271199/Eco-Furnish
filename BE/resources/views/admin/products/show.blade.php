@extends('admins.layouts.admin')

@section('title', 'Chi tiết sản phẩm')

@section('css')
    <!-- Swiper css -->
    <link href="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .product-image-container {
            position: relative;
            background-color: #fff;
            border-radius: 8px;
            padding: 10px;
            max-width: 500px;
            margin: 0 auto;
        }

        .main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            background-color: #fff;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .gallery-section {
            margin-top: 20px;
            width: 100%;
        }

        .gallery-section h5 {
            font-size: 14px;
            margin-bottom: 15px;
            color: #495057;
        }

        /* Reset any inherited styles */
        .gallery-container * {
            box-sizing: border-box;
        }

        .gallery-container {
            display: flex;
            flex-direction: row;
            align-items: center;
            gap: 8px;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow-x: auto;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            list-style: none;
        }

        /* Force horizontal layout */
        .gallery-container::after {
            content: '';
            display: block;
            clear: both;
        }

        .thumbnail-wrapper {
            position: relative;
            display: inline-block;
            vertical-align: top;
            width: 80px;
            height: 80px;
            min-width: 80px;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s ease;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .thumbnail-wrapper.add-photo {
            border: 2px dashed #405189;
            background-color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .thumbnail-wrapper:hover {
            border-color: #405189;
            transform: translateY(-2px);
        }

        .thumbnail-wrapper.active {
            border-color: #405189;
            border-width: 2px;
        }

        .thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            padding: 4px;
        }

        .add-photo-icon {
            color: #405189;
            font-size: 24px;
        }

        /* Custom scrollbar styling */
        .gallery-container::-webkit-scrollbar {
            height: 4px;
        }

        .gallery-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .gallery-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-image {
                height: 300px;
            }
            
            .thumbnail-wrapper {
                width: 60px;
                height: 60px;
                min-width: 60px;
            }
        }

        @media (max-width: 480px) {
            .thumbnail-wrapper {
                width: 50px;
                height: 50px;
                min-width: 50px;
            }
        }
    </style>
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
                        <div class="col-xl-4 col-md-8 mx-auto">
                            <div class="product-image-container">
                                <img src="{{ asset('storage/' . $product->image_thumnail) }}" 
                                     alt="{{ $product->name }}" 
                                     class="main-image"
                                     id="main-product-image">
                                
                                <div class="gallery-section">
                                    <h5>Ảnh phụ sản phẩm</h5>
                                    <div class="gallery-container">
                                        <div class="thumbnail-wrapper active" onclick="changeMainImage('{{ asset('storage/' . $product->image_thumnail) }}')">
                                            <img src="{{ asset('storage/' . $product->image_thumnail) }}" 
                                                 alt="Main image" 
                                                 class="thumbnail">
                                        </div>
                                        @if($product->gallery)
                                            @foreach($product->gallery as $image)
                                                <div class="thumbnail-wrapper" onclick="changeMainImage('{{ asset('storage/' . $image->image_url) }}')">
                                                    <img src="{{ asset('storage/' . $image->image_url) }}" 
                                                         alt="Gallery image {{ $loop->iteration }}" 
                                                         class="thumbnail">
                                                </div>
                                            @endforeach
                                        @endif
                                        <div class="thumbnail-wrapper add-photo">
                                            <i class="fas fa-plus add-photo-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8">
                            <div class="mt-xl-0 mt-5">
                                <div class="d-flex">
                                    <div class="flex-grow-1">
                                        <h4 class="fs-20 mb-1">{{ $product->name }}</h4>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <div class="d-flex align-items-center gap-2">
                                            <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary">
                                                <i class="ri-pencil-fill align-bottom"></i> Sửa
                                            </a>
                                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">
                                            <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Xóa
                                            </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <div class="product-content mt-5">
                                    <nav>
                                        <ul class="nav nav-tabs nav-tabs-custom nav-success" id="nav-tab" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" id="nav-details-tab" data-bs-toggle="tab" href="#nav-details" role="tab">
                                                    Chi tiết
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="nav-variants-tab" data-bs-toggle="tab" href="#nav-variants" role="tab">
                                                    Biến thể
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                    <div class="tab-content border border-top-0 p-4" id="nav-tabContent">
                                        <div class="tab-pane fade show active" id="nav-details" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table mb-0">
                                                    <tbody>
                                                        <tr>
                                                            <th scope="row" style="width: 200px;">Mã sản phẩm</th>
                                                            <td>{{ $product->product_code }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Danh mục</th>
                                                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Giá bán</th>
                                                            <td>
                                                                @if($product->variants->count() > 0)
                                                                    {{ number_format($product->variants->min('price')) }} - 
                                                                    {{ number_format($product->variants->max('price')) }} VNĐ
                                                                @else
                                                                    {{ number_format($product->price) }} VNĐ
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Số lượng biến thể</th>
                                                            <td>{{ $product->variants->count() }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="nav-variants" role="tabpanel">
                                            @if($product->variants->count() > 0)
                                                <div class="table-responsive">
                                                    <table class="table mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Mã SKU</th>
                                                                <th>Màu sắc</th>
                                                                <th>Kích thước</th>
                                                                <th>Giá</th>
                                                                <th>Trạng thái</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @php
                                                                $groupedVariants = $product->variants->groupBy('sku');
                                                            @endphp
                                                            @foreach($groupedVariants as $sku => $variants)
                                                                @php
                                                                    $colorVariant = $variants->first(function($variant) {
                                                                        return $variant->variant && str_contains(strtolower($variant->variant->name), 'màu');
                                                                    });
                                                                    $sizeVariant = $variants->first(function($variant) {
                                                                        return $variant->variant && str_contains(strtolower($variant->variant->name), 'kích');
                                                                    });
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $sku }}</td>
                                                                    <td>{{ $colorVariant ? $colorVariant->variantValue->value : 'N/A' }}</td>
                                                                    <td>{{ $sizeVariant ? $sizeVariant->variantValue->value : 'N/A' }}</td>
                                                                    <td>{{ number_format($variants->first()->price) }} VNĐ</td>
                                                                    <td>
                                                                        @if($variants->first()->status)
                                                                            <span class="badge bg-success-subtle text-success">Đang bán</span>
                                                                        @else
                                                                            <span class="badge bg-danger-subtle text-danger">Ngừng bán</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <p class="text-muted mb-0">Sản phẩm không có biến thể</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($product->description)
                                <div class="mt-4">
                                    <h5 class="fs-14 mb-3">Mô tả sản phẩm :</h5>
                                    <div class="text-muted">
                                        {!! $product->description !!}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('JS')
    <!-- Swiper js -->
    <script src="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.js') }}"></script>
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded');
            
            // Force horizontal layout
            const galleryContainer = document.querySelector('.gallery-container');
            if (galleryContainer) {
                console.log('Gallery container found');
                // Force layout recalculation
                galleryContainer.style.display = 'flex';
                galleryContainer.style.flexDirection = 'row';
                galleryContainer.style.alignItems = 'center';
                
                // Log computed styles
                const computedStyle = window.getComputedStyle(galleryContainer);
                console.log('Display:', computedStyle.display);
                console.log('Flex-direction:', computedStyle.flexDirection);
                console.log('Width:', computedStyle.width);
                
                // Log children
                const children = galleryContainer.children;
                console.log('Number of thumbnails:', children.length);
                Array.from(children).forEach((child, index) => {
                    console.log(`Thumbnail ${index} display:`, window.getComputedStyle(child).display);
                });
            }

            // Initialize first thumbnail
            const firstThumbnail = document.querySelector('.thumbnail-wrapper');
            if (firstThumbnail) {
                firstThumbnail.classList.add('active');
            }
        });

        function changeMainImage(src) {
            const mainImage = document.getElementById('main-product-image');
            const thumbnails = document.querySelectorAll('.thumbnail-wrapper');
            
            if (mainImage) {
                mainImage.src = src;
                
                thumbnails.forEach(thumb => {
                    const thumbImg = thumb.querySelector('img');
                    if (thumbImg && thumbImg.src === src) {
                        thumb.classList.add('active');
                    } else {
                        thumb.classList.remove('active');
                    }
                });
            }
        }
    </script>
@endsection 