@extends('layouts.admin')

@section('title', 'Chi tiết sản phẩm')

@section('CSS')
    @include('partials.products.show_css')
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
                                        </div>
                                        <div class="tab-pane fade" id="nav-variants" role="tabpanel">
                                            @if($product->variants->isNotEmpty())
                                                <div class="table-responsive">
                                                    <table class="table mb-0">
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
                                                                        foreach ($firstVariant->variant_details as $variantId => $valueId) {
                                                                            $variantInfo = DB::table('variants')
                                                                                ->where('id', $variantId)
                                                                                ->first();
                                                                            $variantValueInfo = DB::table('variant_values')
                                                                                ->where('id', $valueId)
                                                                                ->first();
                                                                            
                                                                            if ($variantInfo && $variantValueInfo) {
                                                                                $variantDetails[] = $variantInfo->name . ': ' . $variantValueInfo->value;
                                                                            }
                                                                        }
                                                                    }
                                                                @endphp
                                                                <tr>
                                                                    <td>{{ $sku }}</td>
                                                                    <td>{{ implode(' - ', $variantDetails) }}</td>
                                                                    <td>
                                                                        @if($firstVariant->discount_price)
                                                                            <div class="text-decoration-line-through text-muted">
                                                                                <small>{{ number_format($firstVariant->price) }} VNĐ</small>
                                                                            </div>
                                                                            <div>
                                                                                <strong class="text-danger">{{ number_format($firstVariant->discount_price) }} VNĐ</strong>
                                                                            </div>
                                                                        @else
                                                                            {{ number_format($firstVariant->price) }} VNĐ
                                                                        @endif
                                                                    </td>
                                                                    <td>{{ number_format($firstVariant->quantity) }}</td>
                                                                    <td>
                                                                        @if($firstVariant->status)
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
    @include('partials.products.show_js')
@endsection
