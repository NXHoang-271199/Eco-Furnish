@extends('layouts.admin')

@section('title')
    Quản lý đánh giá sản phẩm
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách đánh giá sản phẩm</h3>
                        <div class="card-tools">
                            <form action="{{ route('reviews.index') }}" method="GET" class="input-group input-group-sm"
                                style="width: 250px;">
                                <input type="text" name="search" class="form-control float-right"
                                    placeholder="Tìm kiếm sản phẩm" value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th width="5%">STT</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Hình ảnh</th>
                                    <th>Xếp hạng trung bình</th>
                                    <th>Số lượt đánh giá</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $key => $product)
                                    <tr>
                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>
                                            @if ($product->thumbnail)
                                                <img src="{{ Storage::url($product->image_thumnail) }}"
                                                    alt="{{ $product->name }}" style="max-height: 50px;">
                                            @else
                                                <span class="text-muted">Không có ảnh</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="star-rating d-flex align-items-center" style="font-size: 18px;">
                                                @php
                                                    $fullStars = floor($product->average_rating); // Số sao đầy đủ
                                                    $halfStar = ($product->average_rating - $fullStars) >= 0.25 ? 1 : 0; // Xác định có nửa sao hay không
                                                    $emptyStars = 5 - ($fullStars + $halfStar); // Số sao rỗng còn lại
                                                @endphp

                                                @for ($i = 0; $i < $fullStars; $i++)
                                                    <i class="fas fa-star text-warning"></i>
                                                @endfor

                                                @if ($halfStar)
                                                    <i class="fas fa-star-half-alt text-warning"></i>
                                                @endif

                                                @for ($i = 0; $i < $emptyStars; $i++)
                                                    <i class="fas fa-star" style="color: #d3d3d3;"></i> <!-- Màu xám nhạt -->
                                                @endfor

                                                <!-- Hiển thị số trung bình bên phải với background, bo góc và kiểu chữ đẹp -->
                                                <span class="ms-2" style="font-size: 10px; color: white; font-weight: bold;
                                                        background-color: #299cdb; padding: 5px; border-radius: 5px;">
                                                    {{ number_format($product->average_rating, 1) }}/5
                                                </span>
                                            </div>




                                        </td>

                                        <td>
                                            <span class="badge bg-success" style="font-size: 12px">{{ $product->total_reviews }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('reviews.product', $product->id) }}"
                                                class="btn btn-sm btn-info">
                                                <i class="ri-star-fill text-warning"></i> Xem đánh giá
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Không có sản phẩm nào đánh gá</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer clearfix">
                        <div class="float-right">
                            {{ $products->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>
@endsection
@section('JS')
@endsection
