@extends('layouts.admin')

@section('title', 'Chi tiết sản phẩm')

@section('CSS')
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
            margin-top: 20px !important;
            width: 100% !important;
            padding: 15px !important;
            background: #f8f9fa !important;
            border-radius: 8px !important;
            border: 1px solid #e9ecef !important;
        }

        .gallery-section h5 {
            font-size: 14px !important;
            margin-bottom: 15px !important;
            color: #495057 !important;
            font-weight: 600 !important;
        }

        /* Reset any inherited styles */
        .gallery-container * {
            box-sizing: border-box;
        }

        .gallery-container {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, 80px) !important;
            gap: 10px !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            justify-content: start !important;
        }

        .thumbnail-wrapper {
            position: relative !important;
            width: 80px !important;
            height: 80px !important;
            min-width: 80px !important;
            min-height: 80px !important;
            max-width: 80px !important;
            max-height: 80px !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 6px !important;
            overflow: hidden !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            background: #fff !important;
            display: block !important;
            float: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .thumbnail-wrapper.add-photo {
            border: 2px dashed #0d6efd !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .thumbnail-wrapper:hover {
            border-color: #0d6efd !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }

        .thumbnail-wrapper.active {
            border: 2px solid #0d6efd !important;
        }

        .thumbnail {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .add-photo-icon {
            color: #0d6efd !important;
            font-size: 20px !important;
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
            
            .gallery-container {
                grid-template-columns: repeat(auto-fill, 70px) !important;
                gap: 8px !important;
            }
            
            .thumbnail-wrapper {
                width: 70px !important;
                height: 70px !important;
                min-width: 70px !important;
                min-height: 70px !important;
                max-width: 70px !important;
                max-height: 70px !important;
            }
        }

        @media (max-width: 480px) {
            .gallery-container {
                grid-template-columns: repeat(auto-fill, 60px) !important;
                gap: 6px !important;
            }
            
            .thumbnail-wrapper {
                width: 60px !important;
                height: 60px !important;
                min-width: 60px !important;
                min-height: 60px !important;
                max-width: 60px !important;
                max-height: 60px !important;
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
                        <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
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
                                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary">
                                                <i class="ri-pencil-fill align-bottom"></i> Sửa
                                            </a>
                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="delete-form" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
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
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Số lượng biến thể</th>
                                                            <td>{{ $product->variants->groupBy('sku')->count() }}</td>
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
            
            // Force horizontal layout
            const galleryContainer = document.querySelector('.gallery-container');
            if (galleryContainer) {
                
                // Force layout recalculation
                galleryContainer.style.display = 'flex';
                galleryContainer.style.flexDirection = 'row';
                galleryContainer.style.alignItems = 'center';
                
               
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
                    thumb.classList.remove('active');
                });
                
                const activeThumbnail = Array.from(thumbnails).find(thumb => {
                    const img = thumb.querySelector('img');
                    return img && img.src === src;
                });
                
                if (activeThumbnail) {
                    activeThumbnail.classList.add('active');
                }
            }
        }
    </script>
@endsection 