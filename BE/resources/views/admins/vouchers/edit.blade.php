@extends('layouts.admin')

@section('title')
    Sửa Voucher {{ $voucher->code }}
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Bọc toàn bộ nội dung vào một div có nền trắng -->
                <div class="p-4 bg-white rounded shadow-sm">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Sửa Voucher</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                @foreach ($breadcrumbs as $breadcrumb)
                                    <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                        @if ($breadcrumb['url'])
                                            <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                                        @else
                                            {{ $breadcrumb['name'] }}
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                    <form action="{{ route('vouchers.update', $voucher->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Mã Voucher -->
                        <div class="mb-4">
                            <label for="code" class="form-label fw-bold">Mã Voucher</label>
                            <input type="text" class="form-control shadow-sm" name="code"
                                value="{{ old('code', $voucher->code) }}" required>
                            @error('code')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tỷ lệ chiết khấu -->
                        <div class="mb-4">
                            <label for="discount_percentage" class="form-label fw-bold">Tỷ lệ chiết khấu (%)</label>
                            <input type="number" name="discount_percentage" class="form-control shadow-sm"
                                value="{{ old('discount_percentage', $voucher->discount_percentage) }}" required>
                            @error('discount_percentage')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Số tiền chiết khấu tối đa -->
                        <div class="mb-4">
                            <label for="max_discount_amount" class="form-label fw-bold">Số tiền chiết khấu tối đa</label>
                            <input type="number" class="form-control shadow-sm" name="max_discount_amount"
                                value="{{ old('max_discount_amount', $voucher->max_discount_amount) }}" required>
                            @error('max_discount_amount')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Giá trị đơn hàng tối thiểu -->
                        <div class="mb-4">
                            <label for="min_order_value" class="form-label fw-bold">Giá trị đơn hàng tối thiểu</label>
                            <input type="number" class="form-control shadow-sm" name="min_order_value"
                                value="{{ old('min_order_value', $voucher->min_order_value) }}" required>
                            @error('min_order_value')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ngày bắt đầu -->
                        <div class="mb-4">
                            <label for="start_date" class="form-label fw-bold">Ngày bắt đầu</label>
                            <input type="date" class="form-control shadow-sm" name="start_date"
                                value="{{ old('start_date', date('Y-m-d', strtotime($voucher->start_date))) }}" required>
                            @error('start_date')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Ngày kết thúc -->
                        <div class="mb-4">
                            <label for="end_date" class="form-label fw-bold">Ngày kết thúc</label>
                            <input type="date" class="form-control shadow-sm" name="end_date"
                                value="{{ old('end_date', date('Y-m-d', strtotime($voucher->end_date))) }}" required>
                            @error('end_date')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Giới hạn sử dụng -->
                        <div class="mb-4">
                            <label for="usage_limit" class="form-label fw-bold">Giới hạn sử dụng</label>
                            <input type="number" class="form-control shadow-sm" name="usage_limit"
                                value="{{ old('usage_limit', $voucher->usage_limit) }}" required>
                            @error('usage_limit')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nút Lưu -->
                        <div class="col-lg-12">
                            <div class="hstack gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary">Cập nhật</button>
                                <a href="{{ route('vouchers.index') }}" class="btn btn-soft-secondary">Hủy bỏ</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
