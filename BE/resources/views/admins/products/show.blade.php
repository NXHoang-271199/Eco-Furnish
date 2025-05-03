@extends('layouts.admin')

@section('title', 'Chi tiết sản phẩm')

@section('CSS')
    @include('partials.products.show_css')
    <style>
        /* CSS nâng cao cho tab biến thể */
        .variant-table {
            border-radius: 12px;
            overflow: hidden;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        }

        .variant-table thead th {
            background-color: #f2f5fc;
            padding: 15px;
            font-weight: 600;
            color: #405189;
            border-bottom: 2px solid #eaedf7;
            transition: all 0.3s ease;
        }

        .variant-table tbody tr {
            transition: all 0.3s ease;
            position: relative;
        }

        .variant-table tbody tr:hover {
            background-color: #f8f9fa;
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(64, 81, 137, 0.07);
        }

        .variant-table tbody tr td {
            padding: 15px;
            border-bottom: 1px solid #eaedf7;
            vertical-align: middle;
        }

        .variant-table tbody tr:last-child td {
            border-bottom: none;
        }

        .variant-sku {
            font-weight: 600;
            font-family: 'Roboto Mono', monospace;
            color: #405189;
            border-radius: 5px;
            padding: 5px 8px;
            background-color: #e9ecef;
            font-size: 13px;
        }

        .variant-details {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .variant-detail-item {
            padding: 4px 8px;
            background-color: #f8f9fc;
            border-radius: 6px;
            font-size: 13px;
            color: #495057;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
        }

        .variant-detail-item:hover {
            background-color: #edf2ff;
            border-color: #cfd7e9;
        }

        .variant-detail-name {
            font-weight: 600;
            color: #405189;
            margin-right: 5px;
        }

        .variant-detail-value {
            color: #555;
        }

        .price-display {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .original-price {
            text-decoration: line-through;
            color: #adb5bd;
            font-size: 13px;
        }

        .discount-price {
            font-weight: 700;
            color: #fa5c7c;
            font-size: 15px;
        }

        .regular-price {
            font-weight: 600;
            color: #405189;
        }

        .quantity-badge {
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 30px;
            padding: 0 10px;
            border-radius: 20px;
            background-color: #eaedf7;
            color: #405189;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0.3px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }

        .status-badge:hover {
            transform: translateY(-2px);
        }

        .status-badge i {
            font-size: 10px;
        }

        .status-badge.in-stock {
            background-color: rgba(16, 196, 105, 0.1);
            color: #10c469;
        }

        .status-badge.out-of-stock {
            background-color: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }

        .status-badge.disabled {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        /* Hiệu ứng chuyển tab */
        .nav-tabs-custom .nav-item .nav-link {
            position: relative;
            padding: 15px 25px;
            font-weight: 600;
            color: #495057;
            transition: all 0.3s ease;
            overflow: hidden;
        }

        .nav-tabs-custom .nav-item .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: #405189;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
        }

        .nav-tabs-custom .nav-item .nav-link.active {
            color: #405189;
        }

        .nav-tabs-custom .nav-item .nav-link.active::after {
            transform: translateX(0);
        }

        .nav-tabs-custom .nav-item .nav-link:hover::after {
            transform: translateX(0);
            opacity: 0.5;
        }
        
        /* Hiệu ứng mờ dần cho hàng hết hàng */
        tr.opacity-50 {
            opacity: 0.65;
            background-color: #f9f9f9;
            position: relative;
        }

        tr.opacity-50::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: repeating-linear-gradient(
                45deg,
                rgba(0, 0, 0, 0.01),
                rgba(0, 0, 0, 0.01) 10px,
                rgba(0, 0, 0, 0.03) 10px,
                rgba(0, 0, 0, 0.03) 20px
            );
            pointer-events: none;
        }

        /* Hiệu ứng fade khi chuyển tab */
        .tab-pane.fade {
            transition: opacity 0.3s ease-in-out;
        }

        /* Nút chuyển tab làm nổi bật */
        .btn-switch-tab {
            position: absolute;
            right: 20px;
            bottom: -20px;
            z-index: 10;
            background-color: #405189;
            color: white;
            border: none;
            border-radius: 30px;
            padding: 10px 20px;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(64, 81, 137, 0.3);
            transition: all 0.3s ease;
        }

        .btn-switch-tab:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(64, 81, 137, 0.4);
        }

        /* Animation khi mở tab */
        @keyframes slideInUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .tab-pane.show {
            animation: slideInUp 0.4s ease-out forwards;
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
                                    <button class="gallery-nav-button prev" onclick="scrollGallery('prev')">
                                        <i class="ri-arrow-left-s-line"></i>
                                    </button>
                                    <button class="gallery-nav-button next" onclick="scrollGallery('next')">
                                        <i class="ri-arrow-right-s-line"></i>
                                    </button>
                                    <div class="gallery-container">
                                        <div class="thumbnail-wrapper active" onclick="changeMainImage('{{ asset('storage/' . $product->image_thumnail) }}', this)">
                                            <img src="{{ asset('storage/' . $product->image_thumnail) }}"
                                                 alt="Main image"
                                                 class="thumbnail">
                                        </div>
                                        @if($product->gallery)
                                            @foreach($product->gallery as $image)
                                                <div class="thumbnail-wrapper" onclick="changeMainImage('{{ asset('storage/' . $image->image_url) }}', this)">
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
                                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger" onclick="confirmDelete(this)">
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
                                                    <i class="ri-information-line me-1 align-middle"></i> Chi tiết
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" id="nav-variants-tab" data-bs-toggle="tab" href="#nav-variants" role="tab">
                                                    <i class="ri-list-check me-1 align-middle"></i> Biến thể
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                    <div class="tab-content border border-top-0 p-4 position-relative" id="nav-tabContent">
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
                                                            <td>{{ $product->category_name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th scope="row">Giá bán</th>
                                                            <td>
                                                                @if($product->variants->isEmpty())
                                                                    <div>
                                                                        <strong>Giá gốc:</strong> {{ number_format($product->price) }} VNĐ
                                                                    </div>
                                                                    @if($product->discount_price)
                                                                        <div class="mt-2">
                                                                            <strong>Giá khuyến mãi:</strong> {{ number_format($product->discount_price) }} VNĐ
                                                                        </div>
                                                                    @endif
                                                                @else
                                                                    <div class="mt-2">
                                                                        <strong>Giá biến thể:</strong>
                                                                        @php
                                                                            $minPrice = $product->variants->min('price');
                                                                            $maxPrice = $product->variants->max('price');
                                                                            $minDiscountPrice = $product->variants->min('discount_price');
                                                                            $maxDiscountPrice = $product->variants->max('discount_price');
                                                                            $hasDiscount = $product->variants->whereNotNull('discount_price')->count() > 0;
                                                                        @endphp
                                                                        @if($minPrice === $maxPrice)
                                                                            {{ number_format($minPrice) }} VNĐ
                                                                        @else
                                                                            {{ number_format($minPrice) }} - {{ number_format($maxPrice) }} VNĐ
                                                                        @endif
                                                                    </div>
                                                                    @if($hasDiscount)
                                                                        <div class="mt-2">
                                                                            <strong>Giá khuyến mãi biến thể:</strong>
                                                                            @if($minDiscountPrice === $maxDiscountPrice)
                                                                                <span class="text-danger">{{ number_format($minDiscountPrice) }} VNĐ</span>
                                                                            @else
                                                                                <span class="text-danger">{{ number_format($minDiscountPrice) }} - {{ number_format($maxDiscountPrice) }} VNĐ</span>
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @if($product->variants->isEmpty())
                                                            <tr>
                                                                <th scope="row">Số lượng sản phẩm</th>
                                                                <td>{{ number_format($product->quantity) }}</td>
                                                            </tr>
                                                        @endif
                                                        <tr>
                                                            <th scope="row">Số lượng biến thể</th>
                                                            <td>{{ $product->variants->groupBy('sku')->count() }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <button class="btn-switch-tab" onclick="document.getElementById('nav-variants-tab').click()">
                                                <i class="ri-arrow-right-line me-1"></i> Xem biến thể
                                            </button>
                                        </div>
                                        <div class="tab-pane fade" id="nav-variants" role="tabpanel">
                                            @if($product->variants->isNotEmpty())
                                                <div class="table-responsive">
                                                    <table class="table variant-table mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Mã SKU</th>
                                                                <th>Biến thể</th>
                                                                <th>Giá</th>
                                                                <th>Số lượng</th>
                                                                <th>Trạng thái</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($product->variants->groupBy('sku') as $sku => $variants)
                                                                @php
                                                                    $firstVariant = $variants->first();
                                                                    $variantDetails = [];
                                                                    
                                                                    if (!empty($firstVariant->variant_details)) {
                                                                        // Kiểm tra nếu variant_details là mảng các đối tượng có name và value
                                                                        if (is_array($firstVariant->variant_details) && isset($firstVariant->variant_details[0]) && 
                                                                            isset($firstVariant->variant_details[0]['name']) && 
                                                                            isset($firstVariant->variant_details[0]['value'])) {
                                                                            
                                                                            foreach ($firstVariant->variant_details as $detail) {
                                                                                $variantDetails[] = $detail;
                                                                            }
                                                                        }
                                                                        // Kiểm tra nếu là đối tượng với cặp khóa-giá trị
                                                                        else {
                                                                            foreach ($firstVariant->variant_details as $variantId => $valueId) {
                                                                                $variantInfo = DB::table('variants')
                                                                                    ->where('id', $variantId)
                                                                                    ->first();
                                                                                $variantValueInfo = DB::table('variant_values')
                                                                                    ->where('id', $valueId)
                                                                                    ->first();
                                                                                
                                                                                if ($variantInfo && $variantValueInfo) {
                                                                                    $variantDetails[] = [
                                                                                        'name' => $variantInfo->name,
                                                                                        'value' => $variantValueInfo->value
                                                                                    ];
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                @endphp
                                                                <tr @if($firstVariant->quantity <= 0) class="opacity-50" @endif>
                                                                    <td>
                                                                        <span class="variant-sku">{{ $sku }}</span>
                                                                    </td>
                                                                    <td>
                                                                        <div class="variant-details">
                                                                            @foreach($variantDetails as $detail)
                                                                                @if(isset($detail) && is_array($detail))
                                                                                    @if(isset($detail['is_deleted']) && $detail['is_deleted'])
                                                                                        <span class="variant-detail-item">
                                                                                            <span class="variant-detail-name">{{ $detail['name'] }}:</span>
                                                                                            <span class="variant-detail-value">{{ $detail['value'] }}</span>
                                                                                        </span>
                                                                                    @else
                                                                                        <span class="variant-detail-item">
                                                                                            <span class="variant-detail-name">{{ $detail['name'] }}:</span>
                                                                                            <span class="variant-detail-value">{{ $detail['value'] }}</span>
                                                                                        </span>
                                                                                    @endif
                                                                                @elseif(is_string($detail))
                                                                                    <span class="variant-detail-item">{{ $detail }}</span>
                                                                                @endif
                                                                            @endforeach
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="price-display">
                                                                            @if($firstVariant->discount_price)
                                                                                <div class="original-price">
                                                                                    {{ number_format($firstVariant->price) }} VNĐ
                                                                                </div>
                                                                                <div class="discount-price">
                                                                                    {{ number_format($firstVariant->discount_price) }} VNĐ
                                                                                </div>
                                                                            @else
                                                                                <div class="regular-price">
                                                                                    {{ number_format($firstVariant->price) }} VNĐ
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <span class="quantity-badge">
                                                                            {{ number_format($firstVariant->quantity) }}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        @if($firstVariant->quantity <= 0)
                                                                            <span class="status-badge out-of-stock">
                                                                                <i class="ri-error-warning-line"></i> Hết hàng
                                                                            </span>
                                                                        @elseif($firstVariant->status)
                                                                            <span class="status-badge in-stock">
                                                                                <i class="ri-checkbox-circle-line"></i> Đang bán
                                                                            </span>
                                                                        @else
                                                                            <span class="status-badge disabled">
                                                                                <i class="ri-close-circle-line"></i> Ngừng bán
                                                                            </span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <div class="avatar-md mx-auto mb-4">
                                                        <div class="avatar-title bg-light rounded-circle text-primary fs-24">
                                                            <i class="ri-information-line"></i>
                                                        </div>
                                                    </div>
                                                    <h5>Sản phẩm không có biến thể</h5>
                                                    <p class="text-muted mb-0">Sản phẩm này hiện chưa có biến thể nào được thiết lập.</p>
                                                </div>
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
    @include('partials.products.show_js')
    <script>
        // Script để xử lý hiệu ứng khi chuyển tab
        document.addEventListener('DOMContentLoaded', function() {
            const tabLinks = document.querySelectorAll('.nav-tabs-custom .nav-link');
            
            tabLinks.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Thêm hiệu ứng khi tab được click
                    tabLinks.forEach(t => t.classList.remove('pulse'));
                    this.classList.add('pulse');
                });
            });
            
            // Hiệu ứng hover cho các hàng trong bảng biến thể
            const variantRows = document.querySelectorAll('.variant-table tbody tr');
            variantRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('opacity-50')) {
                        this.style.transform = 'translateY(-2px)';
                        this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.05)';
                    }
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = 'none';
                });
            });
        });
    </script>
@endsection
