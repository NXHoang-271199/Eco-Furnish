@extends('layouts.admin')

@section('title')
    Tạo mã giảm giá
@endsection

@section('JS')
    <script src="{{ asset('assets/admins/js/pages/form-validation.init.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');

            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false); 
            });
        });
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Bọc toàn bộ form vào div có nền trắng -->
                <div class="p-4 bg-white rounded ">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                        <h4 class="mb-sm-0">Thêm Voucher</h4>\
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
                    <form action="{{ route('vouchers.store') }}" method="POST" enctype="multipart/form-data"
                        class="needs-validation" novalidate>
                        @csrf

                        <!-- Mã Voucher -->
                        <div class="mb-4">
                            <label for="code" class="form-label ">Mã Voucher</label>
                            <input type="text" class="form-control @error('code') is-invalid @enderror" name="code"
                                value="{{ old('code') }}" required>
                            <div class="invalid-feedback">
                                @error('code')
                                    {{ $message }}
                                @else
                                    Vui lòng nhập mã voucher.
                                @enderror
                            </div>
                        </div>

                        <!-- Tỷ lệ chiết khấu -->
                        <div class="mb-4">
                            <label for="discount_percentage" class="form-label ">Tỷ lệ chiết khấu (%)</label>
                            <input type="number" name="discount_percentage"
                                class="form-control @error('discount_percentage') is-invalid @enderror"
                                value="{{ old('discount_percentage') }}" required>
                            <div class="invalid-feedback">
                                @error('discount_percentage')
                                    {{ $message }}
                                @else
                                    Vui lòng nhập tỷ lệ chiết khấu.
                                @enderror
                            </div>
                        </div>

                        <!-- Số tiền chiết khấu tối đa -->
                        <div class="mb-4">
                            <label for="max_discount_amount" class="form-label ">Số tiền chiết khấu tối đa</label>
                            <input type="number" class="form-control @error('max_discount_amount') is-invalid @enderror"
                                name="max_discount_amount" value="{{ old('max_discount_amount') }}" required>
                            <div class="invalid-feedback">
                                @error('max_discount_amount')
                                    {{ $message }}
                                @else
                                    Vui lòng nhập số tiền chiết khấu.
                                @enderror
                            </div>
                        </div>

                        <!-- Giá trị đơn hàng tối thiểu -->
                        <div class="mb-4">
                            <label for="min_order_value" class="form-label ">Giá trị đơn hàng tối thiểu</label>
                            <input type="number" class="form-control @error('min_order_value') is-invalid @enderror"
                                name="min_order_value" value="{{ old('min_order_value') }}" required>
                            <div class="invalid-feedback">
                                @error('min_order_value')
                                    {{ $message }}
                                @else
                                    Vui lòng nhập giá trị đơn hàng.
                                @enderror
                            </div>
                        </div>

                        <!-- Ngày bắt đầu -->
                        <div class="mb-4">
                            <label for="start_date" class="form-label ">Ngày bắt đầu</label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                name="start_date" value="{{ old('start_date') }}" required>
                            <div class="invalid-feedback">
                                @error('start_date')
                                    {{ $message }}
                                @else
                                    Vui lòng nhập ngày bắt đầu.
                                @enderror
                            </div>
                        </div>

                        <!-- Ngày kết thúc -->
                        <div class="mb-4">
                            <label for="end_date" class="form-label ">Ngày kết thúc</label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                name="end_date" value="{{ old('end_date') }}" required>
                            <div class="invalid-feedback">
                                @error('end_date')
                                    {{ $message }}
                                @else
                                    Vui lòng nhập ngày kết thúc.
                                @enderror
                            </div>
                        </div>

                        <!-- Giới hạn sử dụng -->
                        <div class="mb-4">
                            <label for="usage_limit" class="form-label ">Giới hạn sử dụng</label>
                            <input type="number" class="form-control @error('usage_limit') is-invalid @enderror"
                                name="usage_limit" value="{{ old('usage_limit') }}" required>
                            <div class="invalid-feedback">
                                @error('usage_limit')
                                    {{ $message }}
                                @else
                                    Vui lòng nhập số lần sử dụng.
                                @enderror
                            </div>
                        </div>

                        <!-- Nút Quay lại và Lưu -->
                        <div class="col-lg-12">
                            <div class="hstack gap-2 justify-content-end">
                                <button type="submit" class="btn btn-primary">Thêm mới</button>
                                <a href="{{ route('vouchers.index') }}" class="btn btn-soft-secondary">Hủy bỏ</a>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
