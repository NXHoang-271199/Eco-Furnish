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
            height: 250px;
            object-fit: contain;
            background-color: #f8f9fa;
            border-radius: 4px;
            margin-bottom: 10px;
            border: 1px solid #e9ecef;
        }

        .gallery-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
            gap: 5px;
            padding: 5px;
            background: #f8f9fa;
            border-radius: 4px;
            border: 1px solid #e9ecef;
        }

        .thumbnail-wrapper {
            aspect-ratio: 1;
            border-radius: 4px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.2s ease;
            background-color: #fff;
        }

        .thumbnail-wrapper:hover {
            border-color: #405189;
            transform: translateY(-2px);
        }

        .thumbnail-wrapper.active {
            border-color: #405189;
            box-shadow: 0 2px 4px rgba(64,81,137,0.2);
        }

        .thumbnail {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 2px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-image {
                height: 200px;
            }
            
            .gallery-container {
                grid-template-columns: repeat(auto-fill, minmax(40px, 1fr));
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
                                
                                @if($product->gallery && $product->gallery->count() > 0)
                                    <div class="gallery-container d-flex gap-2 overflow-auto">
                                        <div class="thumbnail-wrapper active" onclick="changeMainImage('{{ asset('storage/' . $product->image_thumnail) }}')">
                                            <img src="{{ asset('storage/' . $product->image_thumnail) }}" 
                                                 alt="Main image" 
                                                 class="thumbnail">
                                        </div>
                                        @foreach($product->gallery as $image)
                                            <div class="thumbnail-wrapper" onclick="changeMainImage('{{ asset('storage/' . $image->image_url) }}')">
                                                <img src="{{ asset('storage/' . $image->image_url) }}" 
                                                     alt="Gallery image {{ $loop->iteration }}" 
                                                     class="thumbnail">
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
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
                                            <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="ri-delete-bin-fill align-bottom"></i> Xóa
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

@section('js')
    <!-- Swiper js -->
    <script src="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.js') }}"></script>
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        function changeMainImage(src) {
            const mainImage = document.getElementById('main-product-image');
            const thumbnails = document.querySelectorAll('.thumbnail-wrapper');
            
            mainImage.src = src;
            
            // Update active state of thumbnails
            thumbnails.forEach(thumb => {
                const thumbImg = thumb.querySelector('img');
                if (thumbImg.src === src) {
                    thumb.classList.add('active');
                } else {
                    thumb.classList.remove('active');
                }
            });
        }

        // Initialize the first thumbnail as active
        $(document).ready(function() {
            const firstThumbnail = document.querySelector('.thumbnail-wrapper');
            if (firstThumbnail) {
                firstThumbnail.classList.add('active');
            }

            // Xử lý xóa sản phẩm
            $('.delete-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                
                Swal.fire({
                    title: 'Bạn có chắc chắn?',
                    text: "Bạn sẽ không thể khôi phục lại sản phẩm này!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Có, xóa nó!',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData(form[0]);
                        formData.append('_method', 'DELETE');
                        
                        $.ajax({
                            url: form.attr('action'),
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                Swal.fire({
                                    title: 'Đã xóa!',
                                    text: 'Sản phẩm đã được xóa thành công.',
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500,
                                    willClose: () => {
                                        window.location.href = "{{ route('admin.products.index') }}";
                                    }
                                });
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Lỗi!',
                                    text: 'Có lỗi xảy ra khi xóa sản phẩm.',
                                    icon: 'error',
                                    confirmButtonText: 'Đóng'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection 

@push('scripts')
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Xử lý xóa sản phẩm
            const deleteForm = document.querySelector('.delete-form');
            if (deleteForm) {
                deleteForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Bạn có chắc chắn?',
                        text: "Bạn sẽ không thể khôi phục lại sản phẩm này!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Có, xóa nó!',
                        cancelButtonText: 'Hủy',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteForm.submit();
                        }
                    });
                });
            }
        });
    </script>
@endpush 