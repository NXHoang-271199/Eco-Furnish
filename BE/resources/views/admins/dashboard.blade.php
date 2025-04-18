{{-- Để kế thừa lại admin layout ta sử dụng extends --}}
@extends('layouts.admin')
{{-- Một file chỉ được kế thừa 1 admin layout --}}

@section('title')
    Quản lý
@endsection

@section('CSS')
<style>
    /* Base styles */
    .dashboard-container {
        padding-top: 60px !important;
        margin-top: 30px;
    }
    .page-content {
        padding-top: 10px !important;
    }
    @media (max-width: 768px) {
        .dashboard-container {
            padding-top: 80px !important;
        }
    }

    /* Chart container styles */
    .chart-container {
        position: relative;
    }
    
    .chart-no-data-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }
    
    .chart-no-data-overlay.active {
        opacity: 1;
        visibility: visible;
    }
    
    .no-data-message {
        font-size: 20px;
        font-weight: 500;
        color: #6c757d;
        text-align: center;
        padding: 20px;
        background-color: rgba(255, 255, 255, 0.9);
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }
    
    .no-data-message i {
        font-size: 40px;
        color: #8E54E9;
        margin-bottom: 10px;
        display: block;
    }

    /* Material Design Shadows & Animations */
    .card {
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border: none !important;
        border-radius: 12px !important;
        overflow: hidden;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .card-animate {
        position: relative;
        overflow: hidden;
    }
    
    .card-animate::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 8px;
        height: 100%;
        background: linear-gradient(to bottom, #8E54E9, #4776E6);
        border-top-right-radius: 12px;
        border-bottom-right-radius: 12px;
    }
    
    /* Modern Stats Cards */
    .avatar-title {
        transition: all 0.3s ease;
    }
    
    .card-animate:hover .avatar-title {
        transform: scale(1.1);
    }
    
    .counter-value {
        background: linear-gradient(45deg, #8E54E9, #4776E6);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        font-weight: 700;
        position: relative;
    }
    
    /* Counter completion animation */
    .counter-complete {
        animation: counterPulse 0.6s ease;
    }
    
    @keyframes counterPulse {
        0% {
            transform: scale(1);
            text-shadow: 0 0 0 rgba(71, 118, 230, 0);
        }
        50% {
            transform: scale(1.1);
            text-shadow: 0 0 10px rgba(71, 118, 230, 0.5);
        }
        100% {
            transform: scale(1);
            text-shadow: 0 0 0 rgba(71, 118, 230, 0);
        }
    }
    
    /* Date Picker Styles */
    .date-picker-wrapper {
        position: relative;
        display: inline-flex;
        align-items: center;
    }

    .date-picker-display {
        background-color: #fff;
        border: 1px solid #ced4da;
        border-right: none;
        border-radius: 10px 0 0 10px;
        padding: 0.47rem 0.75rem;
        font-size: 0.875rem;
        color: #495057;
        cursor: pointer;
        min-width: 180px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .date-picker-display:hover {
        background-color: #f8f9fa;
    }

    .date-picker-icon {
        background: linear-gradient(45deg, #4776E6, #8E54E9);
        color: white;
        border: none;
        border-radius: 0 10px 10px 0;
        padding: 0.47rem 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .date-picker-icon:hover {
        background: linear-gradient(45deg, #4776E6, #8E54E9);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }
    
    /* Ẩn input gốc mà Flatpickr sử dụng */
    input#dateRangePicker.flatpickr-input {
        display: none !important;
    }

    /* Modern Flatpickr Calendar */
    .flatpickr-calendar {
        z-index: 9999 !important;
        background-color: white;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        border-radius: 12px;
        margin-top: 2px;
        width: 320px !important;
        font-size: 13px !important;
        border: none !important;
        padding: 10px;
        animation: flatpickrFadeInDown 0.3s cubic-bezier(0, 1, 0.5, 1);
    }
    
    @keyframes flatpickrFadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .flatpickr-day {
        height: 38px;
        line-height: 38px;
        margin: 2px;
        border-radius: 8px !important;
        transition: all 0.2s ease;
    }
    
    .flatpickr-day:hover {
        background-color: #f0f7ff !important;
        border-color: #f0f7ff !important;
    }
    
    .flatpickr-day.selected {
        background: linear-gradient(45deg, #4776E6, #8E54E9) !important;
        border-color: transparent !important;
        box-shadow: 0 4px 10px rgba(71, 118, 230, 0.3) !important;
    }
    
    .flatpickr-day.selected.startRange, .flatpickr-day.selected.endRange {
        background: linear-gradient(45deg, #4776E6, #8E54E9) !important;
        border-color: transparent !important;
    }
    
    .flatpickr-day.inRange {
        background-color: rgba(71, 118, 230, 0.15) !important;
        border-color: transparent !important;
    }
    
    .flatpickr-months .flatpickr-month {
        background: linear-gradient(45deg, #4776E6, #8E54E9) !important;
        color: white !important;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding-top: 5px;
    }
    
    .flatpickr-current-month {
        padding-top: 8px !important;
    }
    
    .flatpickr-current-month .flatpickr-monthDropdown-months {
        background-color: transparent !important;
        color: white !important;
        font-weight: 600;
    }
    
    .flatpickr-weekday {
        background-color: transparent;
        color: #555;
        font-weight: 600;
        padding: 5px 0;
    }
    
    /* Button styles */
    .btn-primary {
        background: linear-gradient(45deg, #4776E6, #8E54E9);
        border: none;
        box-shadow: 0 4px 10px rgba(71, 118, 230, 0.3);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background: linear-gradient(45deg, #3d6ad5, #7d44d5);
        box-shadow: 0 6px 15px rgba(71, 118, 230, 0.4);
        transform: translateY(-2px);
    }
    
    .btn-light {
        background: #f8f9fa;
        border: none;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }
    
    .btn-light:hover {
        background: #e9ecef;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }
    
    .btn-soft-info {
        background: rgba(71, 118, 230, 0.15);
        color: #4776E6;
        border: none;
        transition: all 0.3s ease;
    }
    
    .btn-soft-info:hover {
        background: rgba(71, 118, 230, 0.25);
        transform: translateY(-2px);
    }
    
    /* Greeting Section */
    .greeting-section {
        position: relative;
        /* overflow: hidden; */
        border-radius: 15px;
        padding: 5px 0;
        margin-bottom: 20px;
        background: linear-gradient(120deg, #f6f9ff, #f0f7ff);
    }
    
    .greeting-section::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 100%;
        height: 200%;
        background: linear-gradient(120deg, rgba(71, 118, 230, 0.05), rgba(142, 84, 233, 0.08));
        transform: rotate(-15deg);
        z-index: 0;
        border-radius: 50%;
    }
    
    .greeting-text {
        position: relative;
        z-index: 1;
    }
    
    /* Statistics Cards */
    .card-body {
        padding: 1.5rem;
    }
    
    .avatar-sm {
        border-radius: 10px;
    }
    
    .avatar-title.bg-success-subtle {
        background: linear-gradient(45deg, rgba(82, 182, 172, 0.15), rgba(124, 207, 158, 0.15)) !important;
    }
    
    .avatar-title.bg-info-subtle {
        background: linear-gradient(45deg, rgba(71, 118, 230, 0.15), rgba(142, 84, 233, 0.15)) !important;
    }
    
    .avatar-title.bg-warning-subtle {
        background: linear-gradient(45deg, rgba(245, 186, 88, 0.15), rgba(255, 161, 91, 0.15)) !important;
    }
    
    .text-success {
        color: #52b6ac !important;
    }
    
    .text-info {
        color: #4776E6 !important;
    }
    
    .text-warning {
        color: #f5ba58 !important;
    }
    
    /* Glowing effect for icons */
    .bx-dollar-circle, .bx-shopping-bag, .bx-user-circle {
        position: relative;
    }
    
    .bx-dollar-circle::after, .bx-shopping-bag::after, .bx-user-circle::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        border-radius: 50%;
        z-index: -1;
        opacity: 0;
        transition: all 0.5s ease;
    }
    
    .card-animate:hover .bx-dollar-circle::after {
        box-shadow: 0 0 20px rgba(82, 182, 172, 0.5);
        opacity: 1;
    }
    
    .card-animate:hover .bx-shopping-bag::after {
        box-shadow: 0 0 20px rgba(71, 118, 230, 0.5);
        opacity: 1;
    }
    
    .card-animate:hover .bx-user-circle::after {
        box-shadow: 0 0 20px rgba(245, 186, 88, 0.5);
        opacity: 1;
    }
    
    /* Alert styles */
    .alert-info {
        background: linear-gradient(45deg, rgba(71, 118, 230, 0.12), rgba(142, 84, 233, 0.12));
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(71, 118, 230, 0.1);
    }
    
    /* Modern Data Cards */
    .bg-light-subtle {
        background: linear-gradient(120deg, #f6f9ff, #f0f7ff) !important;
    }
    
    .border-dashed {
        border-style: dashed !important;
        border-color: rgba(71, 118, 230, 0.2) !important;
    }
    
    /* Table styling */
    .table-card {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .table th, .table td {
        padding: 1rem;
        vertical-align: middle;
    }
    
    .table-hover tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(71, 118, 230, 0.05);
        transform: translateY(-2px);
        box-shadow: 0 5px 10px rgba(0, 0, 0, 0.05);
    }
    
    .table-centered th, .table-centered td {
        text-align: center;
    }
    
    .avatar-xs {
        width: 2rem;
        height: 2rem;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }
    
    tr:hover .avatar-xs {
        transform: scale(1.15);
    }
    
    .badge {
        padding: 0.4rem 0.8rem;
        font-weight: 500;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .bg-success-subtle {
        background: rgba(82, 182, 172, 0.15) !important;
    }
    
    .bg-warning-subtle {
        background: rgba(245, 186, 88, 0.15) !important;
    }
    
    .bg-info-subtle {
        background: rgba(71, 118, 230, 0.15) !important;
    }
    
    .bg-danger-subtle {
        background: rgba(243, 78, 78, 0.15) !important;
    }
    
    /* Progress bar animation */
    .progress-bar {
        position: relative;
        overflow: hidden;
        border-radius: 8px;
        z-index: 1;
    }
    
    .progress-bar::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.2),
            transparent
        );
        z-index: 9;
        animation: progressShine 2s infinite;
    }
    
    @keyframes progressShine {
        0% {
            transform: translateX(-100%);
        }
        100% {
            transform: translateX(100%);
        }
    }
    
    /* Star rating animation */
    .text-warning i {
        margin-right: 1px;
        position: relative;
    }
    
    tr:hover .text-warning i {
        animation: starPulse 0.5s ease-in-out;
    }
    
    @keyframes starPulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.25);
        }
        100% {
            transform: scale(1);
        }
    }
    
    /* Pagination styling */
    .pagination {
        margin-bottom: 0;
    }
    
    .page-link {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        margin: 0 3px;
        border-radius: 6px;
        color: #4776E6;
        border: none;
        background-color: #f0f7ff;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
    }
    
    .page-link:hover {
        transform: translateY(-2px);
        background: rgba(71, 118, 230, 0.1);
        color: #4776E6;
    }
    
    .page-item.active .page-link {
        background: linear-gradient(45deg, #4776E6, #8E54E9);
        color: white;
        box-shadow: 0 4px 8px rgba(71, 118, 230, 0.3);
        z-index: 1;
    }
    
    .page-item.disabled .page-link {
        color: #adb5bd;
        background-color: #f8f9fa;
        box-shadow: none;
    }
    
    /* Scrollbar styling */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb {
        background: linear-gradient(45deg, #4776E6, #8E54E9);
        border-radius: 10px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(45deg, #3d6ad5, #7d44d5);
    }
</style>
<!-- Import ApexCharts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.3/dist/apexcharts.min.css">
<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- AOS library for scroll animations -->
<link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
@endsection

{{-- @section: dùng để chị định phần nội dụng được hiển thị --}}
@section('content')
<div class="container-fluid p-0 px-4 dashboard-container">

    <div class="row g-0">
        <div class="col-xl-12">

            <div class="h-100">
                <div class="greeting-section mb-3 pb-1" data-aos="fade-up" data-aos-duration="800">
                    <div class="col-12">
                        <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                            <div class="flex-grow-1 greeting-text p-3">
                                <h4 class="fs-16 mb-1">Chào buổi sáng {{ Auth::user()->name }}</h4>
                                <p class="text-muted mb-0">Đây là những gì đang diễn ra với cửa hàng của bạn hôm nay.</p>
                            </div>
                            <div class="mt-3 mt-lg-0 p-3">
                                <form action="{{ route('dashboard.filter') }}" method="GET" id="dateFilterForm">
                                    <div class="row g-3 mb-0 align-items-center">
                                        <div class="col-sm-auto">
                                            <div class="input-group date-picker-wrapper">
                                                <!-- Ẩn input chứa giá trị khoảng ngày -->
                                                <input type="hidden" id="dateRangePicker" name="date_range" value="{{ request('date_range') }}">
                                                
                                                <!-- Hiển thị khoảng ngày đã chọn -->
                                                <span id="dateRangeText" class="date-picker-display">
                                                    @if(!empty(request('date_range')))
                                                        {{ request('date_range') }}
                                                    @else
                                                        Chọn khoảng ngày
                                                    @endif
                                                </span>
                                                
                                                <!-- Icon calendar -->
                                                <button type="button" class="btn btn-primary date-picker-icon" id="datePickerToggle">
                                                    <i class="ri-calendar-2-line"></i>
                                                </button>
                                                
                                                <button type="submit" class="btn btn-primary ms-2">Áp dụng</button>
                                                <button type="button" id="resetDateFilter" class="btn btn-light ms-2">Đặt lại</button>
                                            </div>
                                        </div>
                                        <!--end col-->
                                    </div>
                                    <!--end row-->
                                </form>
                            </div>
                        </div><!-- end card header -->
                    </div>
                    <!--end col-->
                </div>
                <!--end row-->

                @if(isset($isFiltered) && $isFiltered)
                <div class="alert alert-info alert-dismissible fade show mb-4" role="alert" data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">
                    <i class="ri-filter-2-line me-1 align-middle fs-16"></i>
                    <strong>Dữ liệu đã được lọc</strong> - Đang hiển thị dữ liệu từ {{ $formattedDateRange }}
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light ms-2">Xem tất cả dữ liệu</a>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                @endif

                <div class="row">
                    <div class="col-xl-4 col-md-6" data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Tổng doanh thu</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <h5 class="{{ $earningsPercentage >= 0 ? 'text-success' : 'text-danger' }} fs-14 mb-0">
                                            <i class="ri-arrow-{{ $earningsPercentage >= 0 ? 'right-up' : 'right-down' }}-line fs-13 align-middle"></i> {{ $earningsPercentage >= 0 ? '+' : '' }}{{ number_format($earningsPercentage, 2) }} %
                                        </h5>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="@if(isset($isFiltered) && $isFiltered && isset($hasData) && !$hasData) 0 @else {{ $totalEarnings ?? 0 }} @endif">0</span> ₫</h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-success-subtle rounded fs-3">
                                            <i class="bx bx-dollar-circle text-success"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-4 col-md-6" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                     <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Đơn hàng</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <h5 class="{{ $ordersPercentage >= 0 ? 'text-success' : 'text-danger' }} fs-14 mb-0">
                                            <i class="ri-arrow-{{ $ordersPercentage >= 0 ? 'right-up' : 'right-down' }}-line fs-13 align-middle"></i> {{ $ordersPercentage >= 0 ? '+' : '' }}{{ number_format($ordersPercentage, 2) }} %
                                        </h5>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="@if(isset($isFiltered) && $isFiltered && isset($hasData) && !$hasData) 0 @else {{ $totalOrders ?? 0 }} @endif">0</span></h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-info-subtle rounded fs-3">
                                            <i class="bx bx-shopping-bag text-info"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    <div class="col-xl-4 col-md-6" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Khách hàng</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <h5 class="{{ $customersPercentage >= 0 ? 'text-success' : 'text-danger' }} fs-14 mb-0">
                                            <i class="ri-arrow-{{ $customersPercentage >= 0 ? 'right-up' : 'right-down' }}-line fs-13 align-middle"></i> {{ $customersPercentage >= 0 ? '+' : '' }}{{ number_format($customersPercentage, 2) }} %
                                        </h5>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="@if(isset($isFiltered) && $isFiltered && isset($hasData) && !$hasData) 0 @else {{ $totalCustomers ?? 0 }} @endif">0</span></h4>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-warning-subtle rounded fs-3">
                                            <i class="bx bx-user-circle text-warning"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->

                    {{-- <div class="col-xl-3 col-md-6">
                        <!-- card -->
                        <div class="card card-animate">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 overflow-hidden">
                                        <p class="text-uppercase fw-medium text-muted text-truncate mb-0">Số dư của tôi</p>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <h5 class="text-muted fs-14 mb-0">
                                            +0.00 %
                                        </h5>
                                    </div>
                                </div>
                                <div class="d-flex align-items-end justify-content-between mt-4">
                                    <div>
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4">$<span class="counter-value" data-target="165.89">0</span>k </h4>
                                        <a href="#" class="text-decoration-underline">Rút tiền</a>
                                    </div>
                                    <div class="avatar-sm flex-shrink-0">
                                        <span class="avatar-title bg-primary-subtle rounded fs-3">
                                            <i class="bx bx-wallet text-primary"></i>
                                        </span>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col --> --}}
                </div> <!-- end row-->

                <div class="row">
                    <div class="col">
                        <div class="card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">
                            <div class="card-header border-0 align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Doanh thu</h4>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-soft-info btn-sm" id="exportRevenueReport" data-report-type="revenue" data-report-title="Báo cáo doanh thu">
                                        <i class="ri-file-excel-2-line align-middle"></i> Xuất báo cáo
                                    </button>
                                    <!-- Xóa phần div chứa các nút lọc 1 tháng, 6 tháng, 1 năm -->
                                </div>
                            </div><!-- end card header -->

                            <div class="card-header p-0 border-0 bg-light-subtle">
                                <div class="row g-0 text-center">
                                    <div class="col-6 col-sm-4">
                                        <div class="p-3 border border-dashed border-start-0" data-aos="fade-right" data-aos-duration="800" data-aos-delay="200">
                                            <h5 class="mb-1"><span class="counter-value" data-target="@if(isset($isFiltered) && $isFiltered && isset($hasData) && !$hasData) 0 @else {{ isset($monthlyData) ? array_sum(array_column($monthlyData, 'orders')) : 0 }} @endif" id="chart-orders-counter">0</span></h5>
                                            <p class="text-muted mb-0">Đơn hàng</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-6 col-sm-4">
                                        <div class="p-3 border border-dashed border-start-0" data-aos="fade-right" data-aos-duration="800" data-aos-delay="300">
                                            <h5 class="mb-1"><span class="counter-value" data-target="@if(isset($isFiltered) && $isFiltered && isset($hasData) && !$hasData) 0 @else {{ isset($monthlyData) ? array_sum(array_column($monthlyData, 'revenue')) : 0 }} @endif" id="chart-revenue-counter">0</span> ₫</h5>
                                            <p class="text-muted mb-0">Doanh thu</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-12 col-sm-4">
                                        <div class="p-3 border border-dashed border-start-0 border-end-0" data-aos="fade-right" data-aos-duration="800" data-aos-delay="400">
                                            <h5 class="mb-1"><span class="counter-value" data-target="@if(isset($isFiltered) && $isFiltered && isset($hasData) && !$hasData) 0 @else {{ isset($monthlyData) ? array_sum(array_column($monthlyData, 'refunds')) : 0 }} @endif" id="chart-refunds-counter">0</span></h5>
                                            <p class="text-muted mb-0">Hoàn tiền</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body p-0 pb-2">
                                <div class="w-100 chart-container" data-aos="zoom-in" data-aos-duration="800" data-aos-delay="300">
                                    <div id="customer_impression_charts" class="apex-charts" dir="ltr"></div>
                                    <div class="chart-no-data-overlay" id="chartNoDataOverlay">
                                        <div class="no-data-message">
                                            <i class="ri-information-line"></i>
                                            Không có dữ liệu trong khoảng thời gian này
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->


                </div>

                <div class="row">
                    <div class="col-xl-6">
                        <div class="card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Sản phẩm bán chạy nhất</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-info btn-sm me-2" id="exportProductsReport" data-report-type="products" data-report-title="Sản phẩm bán chạy">
                                        <i class="ri-file-excel-2-line align-middle"></i> Tạo báo cáo
                                    </button>
                                    <div class="dropdown card-header-dropdown d-inline-block">
                                        <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="fw-semibold text-uppercase fs-12">Sắp xếp theo:
                                            </span><span class="text-muted">
                                            @php
                                                $sortLabels = isset($sortLabels) ? $sortLabels : [
                                                    'today' => 'Hôm nay',
                                                    'yesterday' => 'Hôm qua',
                                                    'week' => '7 ngày qua',
                                                    'month' => '30 ngày qua',
                                                    'current_month' => 'Tháng này',
                                                    'last_month' => 'Tháng trước',
                                                    'custom' => 'Tùy chỉnh'
                                                ];
                                                $currentSort = $currentSort ?? 'today';
                                                echo $sortLabels[$currentSort];
                                            @endphp
                                            <i class="mdi mdi-chevron-down ms-1"></i></span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item {{ ($currentSort ?? '') == 'today' ? 'active' : '' }}" href="{{ route('dashboard') }}?sort=today">Hôm nay</a>
                                            <a class="dropdown-item {{ ($currentSort ?? '') == 'yesterday' ? 'active' : '' }}" href="{{ route('dashboard') }}?sort=yesterday">Hôm qua</a>
                                            <a class="dropdown-item {{ ($currentSort ?? '') == 'week' ? 'active' : '' }}" href="{{ route('dashboard') }}?sort=week">7 ngày qua</a>
                                            <a class="dropdown-item {{ ($currentSort ?? '') == 'month' ? 'active' : '' }}" href="{{ route('dashboard') }}?sort=month">30 ngày qua</a>
                                            <a class="dropdown-item {{ ($currentSort ?? '') == 'current_month' ? 'active' : '' }}" href="{{ route('dashboard') }}?sort=current_month">Tháng này</a>
                                            <a class="dropdown-item {{ ($currentSort ?? '') == 'last_month' ? 'active' : '' }}" href="{{ route('dashboard') }}?sort=last_month">Tháng trước</a>
                                            @if(($currentSort ?? '') == 'custom')
                                            <a class="dropdown-item active" href="#">Tùy chỉnh</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-hover table-centered align-middle table-nowrap mb-0">
                                        <tbody>
                                            @forelse($bestSellingProducts as $product)
                                            <tr data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{ 100 + $loop->index * 50 }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-light rounded p-1 me-2">
                                                            <img src="{{ asset('storage/'.$product->image_thumnail) }}" alt="{{ $product->name }}" class="img-fluid d-block" />
                                                        </div>
                                                        <div>
                                                            <h5 class="fs-14 my-1"><a href="{{ route('products.show', $product->id) }}" class="text-reset">{{ $product->name }}</a></h5>
                                                            <span class="text-muted">{{ $product->created_at ?? 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        // Lưu giá trị từ dữ liệu gốc
                                                        $originalTotalSold = $product->total_sold ?? 0;
                                                        $originalTotalAmount = $product->total_amount ?? 0;
                                                        
                                                        // Kiểm tra xem sản phẩm có biến thể không
                                                        $hasVariants = false;
                                                        $priceRange = '';
                                                        $productStock = 0;
                                                        
                                                        try {
                                                            // Lưu ý rằng dòng này sẽ ghi đè biến $product gốc
                                                            $productDetails = App\Models\Product::withTrashed()->with(['variants' => function($query) {
                                                                $query->withTrashed();
                                                            }])->find($product->id);
                                                            
                                                            if ($productDetails) {
                                                                $variants = $productDetails->variants;
                                                                $hasVariants = $variants->count() > 0;
                                                                
                                                                if ($hasVariants) {
                                                                    $minPrice = $variants->min('price');
                                                                    $maxPrice = $variants->max('price');
                                                                    $productStock = $variants->sum('quantity');
                                                                    
                                                                    if ($minPrice != $maxPrice) {
                                                                        $priceRange = number_format($minPrice, 0, ',', '.') . ' - ' . number_format($maxPrice, 0, ',', '.');
                                                                    } else {
                                                                        $priceRange = number_format($minPrice, 0, ',', '.');
                                                                    }
                                                                } else {
                                                                    $priceRange = number_format($productDetails->price, 0, ',', '.');
                                                                    $productStock = $productDetails->quantity;
                                                                }
                                                            } else {
                                                                // Nếu không tìm thấy sản phẩm, sử dụng giá từ dữ liệu gốc
                                                                $priceRange = number_format($product->price, 0, ',', '.');
                                                                $productStock = $product->stock ?? 0;
                                                            }
                                                        } catch (\Exception $e) {
                                                            // Nếu có lỗi, hiển thị giá từ dữ liệu gốc
                                                            $priceRange = number_format($product->price, 0, ',', '.');
                                                            $productStock = $product->stock ?? 0;
                                                        }
                                                    @endphp
                                                    <h5 class="fs-14 my-1 fw-normal">{{ $priceRange }} ₫</h5>
                                                    <span class="text-muted">Giá</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 my-1 fw-normal">{{ $originalTotalSold }}</h5>
                                                    <span class="text-muted">Đơn hàng</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 my-1 fw-normal">{{ $productStock }}</h5>
                                                    <span class="text-muted">Tồn kho</span>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 my-1 fw-normal">{{ number_format($originalTotalAmount, 0, ',', '.') }} ₫</h5>
                                                    <span class="text-muted">Tổng tiền</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No products data available</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="align-items-center mt-4 pt-2 justify-content-between row text-center text-sm-start">
                                    <div class="col-sm">
                                        <div class="text-muted">
                                            Hiển thị <span class="fw-semibold">{{ $bestSellingProducts->count() }}</span> trong số <span class="fw-semibold">{{ $bestSellingProducts->total() }}</span> kết quả
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        <ul class="pagination pagination-separated pagination-sm mb-0 justify-content-center">
                                            {{-- Nút trang trước --}}
                                            @if ($bestSellingProducts->onFirstPage())
                                                <li class="page-item disabled">
                                                    <span class="page-link">←</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $bestSellingProducts->previousPageUrl() }}" rel="prev">←</a>
                                                </li>
                                            @endif

                                            {{-- Các nút số trang --}}
                                            @foreach ($bestSellingProducts->getUrlRange(1, $bestSellingProducts->lastPage()) as $page => $url)
                                                <li class="page-item {{ $page == $bestSellingProducts->currentPage() ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                </li>
                                            @endforeach

                                            {{-- Nút trang sau --}}
                                            @if ($bestSellingProducts->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $bestSellingProducts->nextPageUrl() }}" rel="next">→</a>
                                                </li>
                                            @else
                                                <li class="page-item disabled">
                                                    <span class="page-link">→</span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6">
                        <div class="card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Xếp hạng người mua hàng nhiều nhất</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-info btn-sm" id="createTopBuyersReport" data-report-type="topbuyers" data-report-title="Người mua hàng nhiều nhất">
                                        <i class="ri-file-excel-2-line align-middle"></i> Tạo báo cáo
                                    </button>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-centered table-hover align-middle table-nowrap mb-0">
                                        <tbody>
                                            @forelse($topBuyers as $buyer)
                                            <tr data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{ 100 + $loop->index * 50 }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <img src="{{ $buyer->avatar ? asset('storage/' . $buyer->avatar) : asset('assets/admins/images/users/avatar-' . ($loop->iteration <= 5 ? $loop->iteration : rand(1, 5)) . '.jpg') }}" alt="" class="avatar-xs rounded-circle" />
                                                        </div>
                                                        <div>
                                                            <h5 class="fs-14 my-1 fw-medium"><a href="#" class="text-reset">{{ $buyer->name }}</a></h5>
                                                            <span class="text-muted">{{ $buyer->email }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info-subtle text-info">Khách hàng</span>
                                                </td>
                                                <td>
                                                    <p class="mb-0">{{ $buyer->orders_count }} đơn hàng</p>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 mb-0">{{ number_format($buyer->total_spent, 0, ',', '.') }} ₫</h5>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        @php
                                                            $percent = min(round(($buyer->orders_count / ($topBuyerStats->max_orders ?: 1)) * 100), 100);
                                                            $trend = rand(-5, 10);
                                                            $trendClass = $trend >= 0 ? 'success' : 'danger';
                                                            $barClass = $percent > 80 ? 'bg-success' : ($percent > 50 ? 'bg-info' : ($percent > 30 ? 'bg-warning' : ''));
                                                        @endphp
                                                        <div class="flex-shrink-0">
                                                            <span class="badge badge-soft-{{ $trendClass }} rounded-pill">{{ $trend >= 0 ? '+' : '' }}{{ $trend }}%</span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="progress animated-progress custom-progress progress-label h-6">
                                                                <div class="progress-bar {{ $barClass }}" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
                                                                    <div class="label">{{ $percent }}%</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Không có dữ liệu người mua</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if($topBuyers->count() > 0)
                                <div class="align-items-center mt-4 pt-2 justify-content-between row text-center text-sm-start">
                                    <div class="col-sm">
                                        <div class="text-muted">
                                            Hiển thị <span class="fw-semibold">{{ $topBuyers->count() }}</span> trong số <span class="fw-semibold">{{ $topBuyerStats->total }}</span> kết quả
                                        </div>
                                    </div>
                                    <div class="col-sm-auto">
                                        <ul class="pagination pagination-separated pagination-sm justify-content-center justify-content-sm-end mb-0">
                                            {{-- Nút trang trước --}}
                                            @if($topBuyers->onFirstPage())
                                                <li class="page-item disabled">
                                                    <span class="page-link">←</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $topBuyers->previousPageUrl() }}" aria-label="Previous">
                                                        ←
                                                    </a>
                                                </li>
                                            @endif
                                            
                                            {{-- Các nút số trang --}}
                                            @for($i = 1; $i <= $topBuyers->lastPage(); $i++)
                                                <li class="page-item {{ $i == $topBuyers->currentPage() ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ $topBuyers->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endfor
                                            
                                            {{-- Nút trang sau --}}
                                            @if($topBuyers->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $topBuyers->nextPageUrl() }}" aria-label="Next">
                                                        →
                                                    </a>
                                                </li>
                                            @else
                                                <li class="page-item disabled">
                                                    <span class="page-link">→</span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div> <!-- end row-->

                <div class="row">
                    <div class="col">
                        <div class="card" data-aos="fade-up" data-aos-duration="800" data-aos-delay="200">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Đơn hàng gần đây</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-info btn-sm" id="createOrderReport" data-report-type="orders" data-report-title="Báo cáo đơn hàng">
                                        <i class="ri-file-list-3-line align-middle"></i> Tạo báo cáo
                                    </button>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                        <thead class="text-muted table-light">
                                            <tr>
                                                <th scope="col">Mã đơn hàng</th>
                                                <th scope="col">Khách hàng</th>
                                                <th scope="col">Sản phẩm</th>
                                                <th scope="col">Số tiền</th>
                                                <th scope="col">Nhà cung cấp</th>
                                                <th scope="col">Trạng thái</th>
                                                <th scope="col">Đánh giá</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($recentOrders as $order)
                                            <tr data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{ 100 + $loop->index * 50 }}">
                                                <td>
                                                    <a href="{{ route('orders.detail', $order->id) }}" class="fw-medium link-primary">{{ $order->order_code }}</a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <img src="{{ $order->user && $order->user->avatar ? asset('storage/' . $order->user->avatar) : asset('assets/admins/images/users/avatar-' . (($loop->iteration % 5) + 1) . '.jpg') }}" alt="" class="avatar-xs rounded-circle" />
                                                        </div>
                                                        <div class="flex-grow-1">{{ $order->user_name ?? ($order->user->name ?? 'N/A') }}</div>
                                                    </div>
                                                </td>
                                                <td>{{ $order->orderItems->first()->product->name ?? 'Multiple Products' }}</td>
                                                <td>
                                                    <span class="text-success">{{ number_format($order->orderItems->sum(function($item) { return $item->price * $item->quantity; }), 0, ',', '.') }} ₫</span>
                                                </td>
                                                <td>{{ $order->paymentMethod->name ?? 'N/A' }}</td>
                                                <td>
                                                    @php
                                                        $statusClass = [
                                                            'pending' => 'bg-warning-subtle text-warning',
                                                            'processing' => 'bg-info-subtle text-info',
                                                            'completed' => 'bg-success-subtle text-success',
                                                            'cancelled' => 'bg-danger-subtle text-danger',
                                                            'paid' => 'bg-success-subtle text-success',
                                                            'unpaid' => 'bg-danger-subtle text-danger',
                                                        ];
                                                        $orderStatusClass = $statusClass[$order->order_status] ?? 'bg-secondary-subtle text-secondary';
                                                        $paymentStatusClass = $statusClass[$order->payment_status] ?? 'bg-secondary-subtle text-secondary';
                                                    @endphp
                                                    <span class="badge {{ $orderStatusClass }}">{{ ucfirst($order->order_status) }}</span>
                                                </td>
                                                <td>
                                                    @php
                                                        $rating = $order->avg_rating ?? 0;
                                                        $starCount = (int)$rating;
                                                        $hasHalfStar = $rating - $starCount >= 0.5;
                                                        $emptyStarCount = 5 - $starCount - ($hasHalfStar ? 1 : 0);
                                                    @endphp
                                                    
                                                    <div>
                                                        <span class="fs-14 fw-medium">{{ $rating }}</span>
                                                        <span class="text-warning align-middle fs-11 ms-1">
                                                            @for($i = 0; $i < $starCount; $i++)
                                                                <i class="ri-star-fill"></i>
                                                            @endfor
                                                            
                                                            @if($hasHalfStar)
                                                                <i class="ri-star-half-fill"></i>
                                                            @endif
                                                            
                                                            @for($i = 0; $i < $emptyStarCount; $i++)
                                                                <i class="ri-star-line"></i>
                                                            @endfor
                                                        </span>
                                                        <span class="text-muted fs-11 ms-1">({{ $order->ratings_count ?? 0 }})</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No recent orders available</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table><!-- end table -->
                                </div>
                            </div>
                        </div> <!-- .card-->
                    </div> <!-- .col-->
                </div> <!-- end row-->

            </div> <!-- end .h-100-->

        </div> <!-- end col -->

        {{-- <div class="col-xl-3 layout-rightside-col px-0">
            <div class="overlay"></div>
            <div class="layout-rightside w-100">
                <div class="card h-100 rounded-0">
                    <div class="card-body p-0">
                        <div class="p-3 mt-2">
                            <h6 class="text-muted mb-3 text-uppercase fw-semibold">Top 10 Danh mục</h6>

                            <ol class="ps-3 text-muted">
                                @forelse($topCategories as $category)
                                <li class="py-1">
                                    <a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="text-muted">{{ $category->name }} <span class="float-end">({{ $category->products_count }})</span></a>
                                </li>
                                @empty
                                <li class="py-1">Không có dữ liệu danh mục</li>
                                @endforelse
                            </ol>
                            <div class="mt-3 text-center">
                                <a href="{{ route('categories.index') }}" class="text-muted text-decoration-underline">Xem tất cả danh mục</a>
                            </div>
                        </div>
                        <div class="p-3">
                            <h6 class="text-muted mb-3 text-uppercase fw-semibold">Đánh giá sản phẩm</h6>
                            <!-- Swiper -->
                            <div class="swiper vertical-swiper" style="height: 250px;">
                                <div class="swiper-wrapper">
                                    @forelse($productReviews as $review)
                                    <div class="swiper-slide">
                                        <div class="card border border-dashed shadow-none">
                                            <div class="card-body">
                                                <div class="d-flex">
                                                    <div class="flex-shrink-0 avatar-sm">
                                                        <div class="avatar-title bg-light rounded material-shadow">
                                                            <img src="{{ asset('storage/' . optional($review->product)->image_thumnail) }}" alt="" height="30">
                                                        </div>
                                                    </div>
                                                    <div class="flex-grow-1 ms-3">
                                                        <div>
                                                            <p class="text-muted mb-1 fst-italic text-truncate-two-lines"> " {{ $review->content }} "</p>
                                                            <div class="fs-11 align-middle text-warning">
                                                                <i class="ri-star-fill"></i>
                                                                <i class="ri-star-fill"></i>
                                                                <i class="ri-star-fill"></i>
                                                                <i class="ri-star-fill"></i>
                                                                <i class="ri-star-fill"></i>
                                                            </div>
                                                        </div>
                                                        <div class="text-end mb-0 text-muted">
                                                            - bởi <cite title="Source Title">{{ optional($review->user)->name }}</cite>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="swiper-slide">
                                        <div class="card border border-dashed shadow-none">
                                            <div class="card-body">
                                                <p class="text-muted">Không có đánh giá nào</p>
                                            </div>
                                        </div>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="p-3">
                            <h6 class="text-muted mb-3 text-uppercase fw-semibold">Đánh giá khách hàng</h6>
                            <div class="bg-light px-3 py-2 rounded-2 mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="fs-16 align-middle text-warning">
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-fill"></i>
                                            <i class="ri-star-half-fill"></i>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0">
                                        <h6 class="mb-0">4.5 trên 5</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center">
                                <div class="text-muted">Tổng <span class="fw-medium">5.50k</span> đánh giá</div>
                            </div>

                            <div class="mt-3">
                                <div class="row align-items-center g-2">
                                    <div class="col-auto">
                                        <div class="p-1">
                                            <h6 class="mb-0">5 sao</h6>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-1">
                                            <div class="progress animated-progress progress-sm">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 50.16%" aria-valuenow="50.16" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="p-1">
                                            <h6 class="mb-0 text-muted">2758</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-center g-2">
                                    <div class="col-auto">
                                        <div class="p-1">
                                            <h6 class="mb-0">4 sao</h6>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-1">
                                            <div class="progress animated-progress progress-sm">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 29.32%" aria-valuenow="29.32" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="p-1">
                                            <h6 class="mb-0 text-muted">1063</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-center g-2">
                                    <div class="col-auto">
                                        <div class="p-1">
                                            <h6 class="mb-0">3 sao</h6>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-1">
                                            <div class="progress animated-progress progress-sm">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: 18.12%" aria-valuenow="18.12" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="p-1">
                                            <h6 class="mb-0 text-muted">997</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-center g-2">
                                    <div class="col-auto">
                                        <div class="p-1">
                                            <h6 class="mb-0">2 sao</h6>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-1">
                                            <div class="progress animated-progress progress-sm">
                                                <div class="progress-bar bg-success" role="progressbar" style="width: 4.98%" aria-valuenow="4.98" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="p-1">
                                            <h6 class="mb-0 text-muted">227</h6>
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-center g-2">
                                    <div class="col-auto">
                                        <div class="p-1">
                                            <h6 class="mb-0">1 sao</h6>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="p-1">
                                            <div class="progress animated-progress progress-sm">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: 7.42%" aria-valuenow="7.42" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="p-1">
                                            <h6 class="mb-0 text-muted">408</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end card-->
            </div> <!-- end .rightbar-->

        </div> <!-- end col --> --}}
    </div>

</div>
@endsection

@section('JS')
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.3/dist/apexcharts.min.js"></script>
<!-- Flatpickr -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/vn.js"></script>
<!-- ExcelJS và FileSaver -->
<script src="https://unpkg.com/exceljs/dist/exceljs.min.js"></script>
<script src="https://unpkg.com/file-saver/dist/FileSaver.min.js"></script>
<!-- AOS Animation -->
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Khởi tạo AOS animation
        AOS.init({
            duration: 800,
            once: true,
            mirror: false,
            easing: 'ease-out-cubic'
        });

        // Khởi tạo date picker
        const datePickerInput = document.getElementById('dateRangePicker'); // Input ẩn
        const dateRangeText = document.getElementById('dateRangeText'); // Span hiển thị
        const datePickerToggle = document.getElementById('datePickerToggle'); // Button icon lịch
        
        if (!datePickerInput || !dateRangeText || !datePickerToggle) {
            console.error('Không tìm thấy các phần tử cần thiết cho date picker');
        } else {
            // Khởi tạo flatpickr
            const fp = flatpickr(datePickerInput, {
                mode: "range",
                dateFormat: "Y-m-d",
                locale: "vn",
                rangeSeparator: " đến ",
                maxDate: "today",
                showMonths: 1, // Chỉ hiển thị 1 tháng
                static: true,
                disableMobile: true,
                position: "auto", 
                appendTo: document.body, // Đính kèm vào body thay vì element
                onOpen: function() {
                    console.log('Date picker đã mở');
                },
                onClose: function() {
                    console.log('Date picker đã đóng');
                },
                onChange: function(selectedDates, dateStr, instance) {
                    console.log('Ngày đã chọn:', dateStr);
                    
                    // Cập nhật text hiển thị với định dạng tiếng Việt
                    if (dateStr && selectedDates.length > 0) {
                        let formattedText = '';
                        
                        if (selectedDates.length === 1) {
                            // Nếu chỉ chọn 1 ngày
                            const day = selectedDates[0].getDate().toString().padStart(2, '0');
                            const month = (selectedDates[0].getMonth() + 1).toString().padStart(2, '0');
                            const year = selectedDates[0].getFullYear();
                            formattedText = `${day}/${month}/${year}`;
                        } else if (selectedDates.length === 2) {
                            // Nếu chọn khoảng ngày
                            const startDay = selectedDates[0].getDate().toString().padStart(2, '0');
                            const startMonth = (selectedDates[0].getMonth() + 1).toString().padStart(2, '0');
                            const startYear = selectedDates[0].getFullYear();
                            
                            const endDay = selectedDates[1].getDate().toString().padStart(2, '0');
                            const endMonth = (selectedDates[1].getMonth() + 1).toString().padStart(2, '0');
                            const endYear = selectedDates[1].getFullYear();
                            
                            formattedText = `${startDay}/${startMonth}/${startYear} - ${endDay}/${endMonth}/${endYear}`;
                        }
                        
                        dateRangeText.textContent = formattedText;
                    } else {
                        dateRangeText.textContent = 'Chọn khoảng ngày';
                    }
                }
            });
            
            // Thêm sự kiện click vào button icon để mở date picker
            datePickerToggle.addEventListener('click', function() {
                fp.open();
            });
            
            // Thêm sự kiện click vào span text để mở date picker
            dateRangeText.addEventListener('click', function() {
                fp.open();
            });
            
            // Xử lý sự kiện nút Reset
            document.getElementById('resetDateFilter').addEventListener('click', function() {
                fp.clear();
                dateRangeText.textContent = 'Chọn khoảng ngày';
                datePickerInput.value = '';
                document.getElementById('dateFilterForm').submit();
            });
        }

        // Khởi tạo biểu đồ doanh thu
        initRevenueChart();
        
        // Khởi tạo counter cho các số liệu thống kê
        initCounters();
        
        // Xử lý nút tạo báo cáo đơn hàng
        document.getElementById('createOrderReport').addEventListener('click', function () {
            const reportType = this.getAttribute('data-report-type');
            const reportTitle = this.getAttribute('data-report-title');
            exportToExcel(reportType, reportTitle);
        });

        // Xử lý nút xuất báo cáo doanh thu
        document.getElementById('exportRevenueReport').addEventListener('click', function () {
            const reportType = this.getAttribute('data-report-type');
            const reportTitle = this.getAttribute('data-report-title');
            exportToExcel(reportType, reportTitle);
        });

        // Xử lý nút xuất báo cáo sản phẩm bán chạy
        document.getElementById('exportProductsReport').addEventListener('click', function () {
            const reportType = this.getAttribute('data-report-type');
            const reportTitle = this.getAttribute('data-report-title');
            exportToExcel(reportType, reportTitle);
        });

        // Xử lý các nút báo cáo người mua hàng nhiều nhất
        document.getElementById('createTopBuyersReport').addEventListener('click', function () {
            const reportType = this.getAttribute('data-report-type');
            const reportTitle = this.getAttribute('data-report-title');
            exportToExcel(reportType, reportTitle);
        });
        
        // Hàm khởi tạo counter với animation
        function initCounters() {
            const counterElements = document.querySelectorAll('.counter-value');
            
            counterElements.forEach(function(element) {
                const target = parseInt(element.getAttribute('data-target')) || 0;
                const duration = 2000; // Thời gian hiệu ứng (ms)
                const frameRate = 30; // Số lần cập nhật mỗi giây
                const increment = target / (duration / 1000 * frameRate);
                
                let current = 0;
                const timer = setInterval(function() {
                    current += increment;
                    
                    // Cập nhật giá trị hiển thị
                    if (current >= target) {
                        // Định dạng số với dấu phân cách hàng nghìn khi đạt giá trị mục tiêu
                        const formattedValue = formatNumberWithCommas(target);
                        element.textContent = formattedValue;
                        
                        // Thêm hiệu ứng nhấp nháy khi hoàn thành
                        element.classList.add('counter-complete');
                        setTimeout(() => element.classList.remove('counter-complete'), 600);
                        
                        clearInterval(timer);
                    } else {
                        // Hiển thị số nguyên trong quá trình đếm
                        element.textContent = Math.floor(current);
                    }
                }, 1000 / frameRate);
            });
        }
        
        // Hàm định dạng số với dấu phân cách hàng nghìn theo chuẩn Việt Nam
        function formatNumberWithCommas(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
        
        // Hàm khởi tạo biểu đồ doanh thu
        function initRevenueChart() {
            try {
                // Lấy dữ liệu cho biểu đồ từ backend
                const chartData = @json($chartData ?? null);
                
                if (!chartData || !chartData.months || !chartData.series) {
                    console.error('Không có dữ liệu biểu đồ');
                    return;
                }

                console.log('Dữ liệu biểu đồ:', chartData);
                
                // Tạo một biến để lưu trữ các dữ liệu đã lọc
                let filteredData = {
                    months: [...chartData.months],
                    series: JSON.parse(JSON.stringify(chartData.series)),
                    rawData: {...chartData.rawData}
                };
                
                // Hàm lọc dữ liệu theo thời gian
                function filterChartData(period) {
                    const dateRangeInput = document.getElementById('dateRangePicker');
                    const dateRange = dateRangeInput ? dateRangeInput.value : '';
                    
                    // Nếu có khoảng ngày được chọn, sử dụng khoảng ngày đó
                    if (dateRange) {
                        const rangeParts = dateRange.split(' đến ');
                        const startDate = new Date(rangeParts[0]);
                        const endDate = rangeParts.length > 1 ? new Date(rangeParts[1]) : new Date(rangeParts[0]);
                        
                        // Lấy dữ liệu gốc từ biểu đồ
                        const chartData = @json($chartData ?? null);
                        if (!chartData || !chartData.rawData) return { months: [], series: [] };
                        
                        const filteredData = {
                            months: [],
                            series: [
                                {
                                    name: 'Đơn hàng',
                                    type: 'line',
                                    data: [],
                                    color: '#4776E6'
                                },
                                {
                                    name: 'Doanh thu',
                                    type: 'column',
                                    data: [],
                                    color: '#63ad6f'
                                },
                                {
                                    name: 'Hoàn tiền',
                                    type: 'line',
                                    data: [],
                                    color: '#f34e4e',
                                    dashArray: 4
                                }
                            ]
                        };
                        
                        // Xử lý dữ liệu từng tháng
                        chartData.months.forEach((month, index) => {
                            // Giả sử tháng có định dạng "Th1", "Th2", etc.
                            const monthNumber = parseInt(month.replace('Th', ''));
                            const year = new Date().getFullYear(); // Hoặc lấy năm từ dữ liệu nếu có
                            const monthDate = new Date(year, monthNumber - 1, 15); // ngày 15 của tháng
                            
                            // Kiểm tra nếu tháng này nằm trong khoảng ngày được chọn
                            if (monthDate >= startDate && monthDate <= endDate) {
                                filteredData.months.push(month);
                                filteredData.series[0].data.push(chartData.rawData.orders[index]);
                                filteredData.series[1].data.push(chartData.rawData.revenue[index]);
                                filteredData.series[2].data.push(chartData.rawData.refunds[index]);
                            }
                        });
                        
                        // Nếu không tìm thấy dữ liệu nào trong khoảng thời gian, hiển thị dữ liệu từ backend đã được lọc
                        if (filteredData.months.length === 0) {
                            @if(isset($isFiltered) && $isFiltered && isset($formattedDateRange))
                                filteredData.months = ['{{ $formattedDateRange }}'];
                                
                                @if(isset($hasData) && $hasData && isset($totalOrders) && isset($totalEarnings) && $totalOrders > 0)
                                    filteredData.series[0].data = [{{ $totalOrders ?? 0 }}];
                                    filteredData.series[1].data = [{{ $totalEarnings ?? 0 }}];
                                    filteredData.series[2].data = [{{ isset($totalRefunds) ? $totalRefunds : 0 }}];
                                @else
                                    // Không có dữ liệu trong khoảng ngày này
                                    filteredData.months = ['{{ $formattedDateRange }}'];
                                    filteredData.series[0].data = [0];
                                    filteredData.series[1].data = [0];
                                    filteredData.series[2].data = [0];
                                    filteredData.noData = true; // Đánh dấu là không có dữ liệu
                                @endif
                            @else
                                // Nếu không có dữ liệu được lọc, hiển thị trống
                                filteredData.months = ['Không có dữ liệu'];
                                filteredData.series[0].data = [0];
                                filteredData.series[1].data = [0];
                                filteredData.series[2].data = [0];
                                filteredData.noData = true; // Đánh dấu là không có dữ liệu
                            @endif
                        }
                        
                        return filteredData;
                    }
                    
                    // Nếu không có khoảng ngày, trả về dữ liệu gốc
                    const chartData = @json($chartData ?? null);
                    return chartData ? {
                        months: chartData.months,
                        series: chartData.series
                    } : { months: [], series: [] };
                }
                
                // Tạo biểu đồ với dữ liệu ban đầu
                // Nếu đang có bộ lọc được áp dụng, sử dụng dữ liệu đã lọc
                let initialChartData;
                
                @if(isset($isFiltered) && $isFiltered)
                    initialChartData = filterChartData();
                @else
                    initialChartData = {
                        months: chartData.months,
                        series: chartData.series
                    };
                @endif
                
                // Kiểm tra nếu không có dữ liệu và cập nhật trạng thái overlay
                function updateNoDataOverlay(chartData) {
                    const chartNoDataOverlay = document.getElementById('chartNoDataOverlay');
                    if (!chartNoDataOverlay) return;
                    
                    if (chartData.noData || 
                        (chartData.series[0].data.length === 1 && 
                         chartData.series[0].data[0] === 0 && 
                         chartData.series[1].data[0] === 0 && 
                         chartData.series[2].data[0] === 0)) {
                        chartNoDataOverlay.classList.add('active');
                    } else {
                        chartNoDataOverlay.classList.remove('active');
                    }
                }
                
                // Cập nhật trạng thái ban đầu của overlay
                updateNoDataOverlay(initialChartData);
                
                // Cấu hình cho biểu đồ
                const options = {
                    series: initialChartData.series,
                    chart: {
                        height: 370,
                        type: 'line',
                        stacked: false,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 800,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        },
                        dropShadow: {
                            enabled: true,
                            enabledOnSeries: [0, 2], // Chỉ áp dụng cho series 'Đơn hàng' và 'Hoàn tiền'
                            top: 5,
                            left: 0,
                            blur: 3,
                            opacity: 0.2
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        width: [3, 0, 3],
                        curve: 'smooth',
                        dashArray: [0, 0, 4]
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '30%',
                            borderRadius: 6,
                            dataLabels: {
                                position: 'top'
                            }
                        }
                    },
                    colors: ["#4776E6", "#63ad6f", "#f34e4e"],
                    fill: {
                        type: ['gradient', 'solid', 'gradient'],
                        gradient: {
                            shade: 'light',
                            type: "vertical",
                            shadeIntensity: 0.5,
                            gradientToColors: ["#8E54E9", undefined, "#ff6b6b"],
                            inverseColors: false,
                            opacityFrom: 0.8,
                            opacityTo: 0.5
                        }
                    },
                    labels: initialChartData.months,
                    markers: {
                        size: 5,
                        strokeWidth: 0,
                        hover: {
                            size: 8
                        }
                    },
                    legend: {
                        show: true,
                        position: 'bottom',
                        horizontalAlign: 'center',
                        offsetY: 5,
                        fontFamily: 'Roboto, sans-serif',
                        itemMargin: {
                            horizontal: 15,
                            vertical: 10
                        }
                    },
                    xaxis: {
                        type: 'category',
                        categories: initialChartData.months,
                        labels: {
                            style: {
                                colors: '#adb5bd',
                                fontFamily: 'Roboto, sans-serif',
                                fontWeight: 400
                            }
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    yaxis: [
                        {
                            // Đơn hàng
                            seriesName: 'Đơn hàng',
                            opposite: false,
                            axisTicks: {
                                show: true
                            },
                            axisBorder: {
                                show: true,
                                color: '#4776E6'
                            },
                            labels: {
                                style: {
                                    colors: '#4776E6'
                                },
                                formatter: function (value) {
                                    return Math.round(value);
                                }
                            },
                            title: {
                                text: "Đơn hàng",
                                style: {
                                    color: '#4776E6',
                                    fontSize: '12px',
                                    fontFamily: 'Roboto, sans-serif',
                                    fontWeight: 500
                                }
                            }
                        },
                        {
                            // Doanh thu
                            seriesName: 'Doanh thu',
                            axisTicks: {
                                show: true
                            },
                            axisBorder: {
                                show: true,
                                color: '#63ad6f'
                            },
                            labels: {
                                style: {
                                    colors: '#63ad6f'
                                },
                                formatter: function (value) {
                                    return formatCurrency(value);
                                }
                            },
                            title: {
                                text: "Doanh thu",
                                style: {
                                    color: '#63ad6f',
                                    fontSize: '12px',
                                    fontFamily: 'Roboto, sans-serif',
                                    fontWeight: 500
                                }
                            }
                        },
                        {
                            // Hoàn tiền
                            seriesName: 'Hoàn tiền',
                            opposite: true,
                            axisTicks: {
                                show: true
                            },
                            axisBorder: {
                                show: true,
                                color: '#f34e4e'
                            },
                            labels: {
                                style: {
                                    colors: '#f34e4e'
                                }
                            },
                            title: {
                                text: "Hoàn tiền",
                                style: {
                                    color: '#f34e4e',
                                    fontSize: '12px',
                                    fontFamily: 'Roboto, sans-serif',
                                    fontWeight: 500
                                }
                            }
                        }
                    ],
                    grid: {
                        borderColor: '#f1f1f1',
                        padding: {
                            bottom: 15
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        theme: 'dark',
                        style: {
                            fontSize: '12px',
                            fontFamily: 'Roboto, sans-serif'
                        },
                        y: {
                            formatter: function (value, { seriesIndex, dataPointIndex, w }) {
                                const seriesName = w.config.series[seriesIndex].name;
                                
                                if (seriesName === 'Doanh thu') {
                                    return formatCurrency(value);
                                } else if (seriesName === 'Đơn hàng') {
                                    return value + " đơn";
                                } else if (seriesName === 'Hoàn tiền') {
                                    return value + " đơn";
                                }
                                return value;
                            }
                        }
                    },
                    states: {
                        hover: {
                            filter: {
                                type: 'lighten',
                                value: 0.05
                            }
                        },
                        active: {
                            filter: {
                                type: 'darken',
                                value: 0.1
                            }
                        }
                    }
                };
                
                // Khởi tạo biểu đồ
                const chart = new ApexCharts(document.querySelector("#customer_impression_charts"), options);
                chart.render();
                
                // Hàm cập nhật thống kê tổng hợp khi lọc
                function updateStatistics(period) {
                    // Lấy khoảng ngày từ input date picker
                    const dateRangeInput = document.getElementById('dateRangePicker');
                    const dateRange = dateRangeInput ? dateRangeInput.value : '';
                    
                    let totalOrders = 0;
                    let totalRevenue = 0;
                    let totalRefunds = 0;
                    
                    // Nếu đang xem dữ liệu đã lọc, lấy thông tin từ backend
                    @if(isset($isFiltered) && $isFiltered)
                        @if(isset($hasData) && !$hasData)
                            totalOrders = 0;
                            totalRevenue = 0;
                            totalRefunds = 0;
                        @elseif(isset($totalOrders) && isset($totalEarnings))
                            totalOrders = {{ $totalOrders }};
                            totalRevenue = {{ $totalEarnings }};
                            totalRefunds = {{ isset($totalRefunds) ? $totalRefunds : 0 }};
                        @endif
                    @elseif(isset($chartData) && isset($chartData['rawData']))
                        // Nếu không có khoảng ngày được chọn, sử dụng tất cả dữ liệu
                        totalOrders = {{ array_sum($chartData['rawData']['orders'] ?? [0]) }};
                        totalRevenue = {{ array_sum($chartData['rawData']['revenue'] ?? [0]) }};
                        totalRefunds = {{ array_sum($chartData['rawData']['refunds'] ?? [0]) }};
                    @endif
                    
                    // Cập nhật giá trị hiển thị trên giao diện
                    const orderCounter = document.getElementById('chart-orders-counter');
                    if (orderCounter) {
                        orderCounter.setAttribute('data-target', totalOrders);
                        orderCounter.textContent = '0';
                    }
                    
                    // Cập nhật doanh thu
                    const revenueCounter = document.getElementById('chart-revenue-counter');
                    if (revenueCounter) {
                        revenueCounter.setAttribute('data-target', totalRevenue);
                        revenueCounter.textContent = '0';
                    }
                    
                    // Cập nhật hoàn tiền
                    const refundCounter = document.getElementById('chart-refunds-counter');
                    if (refundCounter) {
                        refundCounter.setAttribute('data-target', totalRefunds);
                        refundCounter.textContent = '0';
                    }
                    
                    // Khởi động lại counter animation
                    initCounters();
                }
                
                console.log('Biểu đồ đã được khởi tạo');
            } catch (error) {
                console.error('Lỗi khởi tạo biểu đồ:', error);
            }
        }

        // Hàm định dạng tiền tệ theo chuẩn Việt Nam
        function formatCurrency(amount) {
            // Định dạng số tiền với dấu phân cách hàng nghìn theo chuẩn VN
            return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".") + ' ₫';
        }

        // Hàm xuất dữ liệu sang Excel với định dạng đẹp sử dụng ExcelJS
        async function exportToExcel(reportType, reportTitle) {
            // Tạo mảng dữ liệu và định nghĩa tiêu đề cho các cột
            let headers = [];
            let data = [];
            
            // Lấy giá trị khoảng ngày từ date picker
            const dateRangeInput = document.getElementById('dateRangePicker');
            const dateRange = dateRangeInput ? dateRangeInput.value : '';
            console.log('Khoảng ngày đã chọn:', dateRange);
            
            // Tạo text thông báo khoảng ngày đã lọc
            let dateRangeInfo = '';
            if (dateRange) {
                const rangeParts = dateRange.split(' đến ');
                if (rangeParts.length === 2) {
                    const startDate = new Date(rangeParts[0]);
                    const endDate = new Date(rangeParts[1]);
                    const formatDate = (date) => {
                        return date.getDate().toString().padStart(2, '0') + '/' + 
                               (date.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                               date.getFullYear();
                    };
                    dateRangeInfo = `Khoảng ngày: ${formatDate(startDate)} - ${formatDate(endDate)}`;
                } else if (rangeParts.length === 1) {
                    const singleDate = new Date(rangeParts[0]);
                    const formatDate = (date) => {
                        return date.getDate().toString().padStart(2, '0') + '/' + 
                               (date.getMonth() + 1).toString().padStart(2, '0') + '/' + 
                               date.getFullYear();
                    };
                    dateRangeInfo = `Ngày: ${formatDate(singleDate)}`;
                }
            } else {
                dateRangeInfo = 'Tất cả dữ liệu';
            }
            
            // Thu thập dữ liệu dựa vào loại báo cáo
            if (reportType === 'buyers') {
                // Thu thập dữ liệu từ bảng người mua hàng nhiều nhất
                headers = ['Khách hàng', 'Email', 'Loại', 'Đơn hàng', 'Chi tiêu', 'Tỷ lệ'];
                
                // Tìm bảng người mua hàng
                const titles = document.querySelectorAll('.card-title');
                let buyerTable = null;

                for (let i = 0; i < titles.length; i++) {
                    if (titles[i].textContent.includes('Người mua hàng nhiều nhất')) {
                        const buyerCard = titles[i].closest('.card');
                        if (buyerCard) {
                            buyerTable = buyerCard.querySelector('table');
                            break;
                        }
                    }
                }

                if (buyerTable) {
                    const rows = buyerTable.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        const nameElement = row.querySelector('.fw-medium');
                        const emailElement = row.querySelector('.text-muted');

                        // Lấy dữ liệu từ các ô dựa vào cấu trúc thẻ td
                        const cells = row.querySelectorAll('td');
                        const buyerName = nameElement ? nameElement.textContent.trim() : '';
                        const email = emailElement ? emailElement.textContent.trim() : '';

                        let type = '', orders = '', spent = '', rate = '';

                        if (cells.length >= 2) type = cells[1].textContent.trim();
                        if (cells.length >= 3) orders = cells[2].textContent.trim();
                        if (cells.length >= 4) spent = cells[3].textContent.trim();
                        if (cells.length >= 5) {
                            // Lấy tỷ lệ từ progress bar nếu có
                            const progressBar = cells[4].querySelector('.progress-bar');
                            if (progressBar) {
                                const labelElement = progressBar.querySelector('.label');
                                if (labelElement) {
                                    rate = labelElement.textContent.trim();
                                } else {
                                    rate = progressBar.getAttribute('aria-valuenow') + '%';
                                }
                            } else {
                                rate = cells[4].textContent.trim();
                            }
                        }

                        data.push([buyerName, email, type, orders, spent, rate]);
                    });
                }
            } else if (reportType === 'products') {
                // Thu thập dữ liệu từ bảng sản phẩm bán chạy
                headers = ['Sản phẩm', 'Giá', 'Đơn hàng', 'Tồn kho', 'Tổng tiền', 'Ngày tạo'];
                
                // Tìm bảng sản phẩm bán chạy
                const titles = document.querySelectorAll('.card-title');
                let productTable = null;
                
                for (let i = 0; i < titles.length; i++) {
                    if (titles[i].textContent.includes('Sản phẩm bán chạy nhất')) {
                        const productCard = titles[i].closest('.card');
                        if (productCard) {
                            productTable = productCard.querySelector('table');
                            break;
                        }
                    }
                }
                
                if (productTable) {
                    const rows = productTable.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        const nameElement = row.querySelector('.fs-14.my-1 a');
                        const dateElement = row.querySelector('.text-muted');
                        
                        // Lấy dữ liệu từ các ô
                        const cells = row.querySelectorAll('td');
                        const productName = nameElement ? nameElement.textContent.trim() : '';
                        const createdDate = dateElement ? dateElement.textContent.trim() : '';
                        
                        let price = '', orders = '', stock = '', totalAmount = '';
                        
                        if (cells.length >= 2) {
                            const priceElement = cells[1].querySelector('.fs-14.my-1.fw-normal');
                            price = priceElement ? priceElement.textContent.trim() : '';
                        }
                        
                        if (cells.length >= 3) {
                            const ordersElement = cells[2].querySelector('.fs-14.my-1.fw-normal');
                            orders = ordersElement ? ordersElement.textContent.trim() : '';
                        }
                        
                        if (cells.length >= 4) {
                            const stockElement = cells[3].querySelector('.fs-14.my-1.fw-normal');
                            stock = stockElement ? stockElement.textContent.trim() : '';
                        }
                        
                        if (cells.length >= 5) {
                            const amountElement = cells[4].querySelector('.fs-14.my-1.fw-normal');
                            totalAmount = amountElement ? amountElement.textContent.trim() : '';
                        }
                        
                        data.push([productName, price, orders, stock, totalAmount, createdDate]);
                    });
                }
            } else if (reportType === 'revenue') {
                // Thu thập dữ liệu cho báo cáo doanh thu
                headers = ['Tháng', 'Đơn hàng', 'Doanh thu', 'Hoàn tiền', 'Tỷ lệ chuyển đổi'];

                // Lấy dữ liệu từ biểu đồ (dùng dữ liệu mẫu nếu không có dữ liệu thực)
                const monthlyData = @json($monthlyData ?? []);
                
                if (monthlyData && monthlyData.length > 0) {
                    // Dữ liệu thực từ backend
                    monthlyData.forEach(item => {
                        data.push([
                            item.month, 
                            item.orders.toString(), 
                            item.revenue.toLocaleString('vi-VN') + ' ₫', 
                            item.refunds.toString(),
                            '15%' // Giá trị mẫu cho tỷ lệ chuyển đổi
                        ]);
                    });
                } else {
                    // Dữ liệu mẫu nếu không có dữ liệu thực
                    for (let i = 1; i <= 12; i++) {
                        data.push([
                            'Tháng ' + i,
                            Math.floor(Math.random() * 500 + 300).toString(),
                            (Math.random() * 10000000 + 5000000).toLocaleString('vi-VN') + ' ₫',
                            Math.floor(Math.random() * 30).toString(),
                            Math.floor(Math.random() * 10 + 10) + '%'
                        ]);
                    }
                }
            }

            // Kiểm tra và debug
            console.log('Tiêu đề:', headers);
            console.log('Dữ liệu:', data);

            // Thu thập dữ liệu sản phẩm bán chạy riêng cho báo cáo doanh thu
            let productHeaders = ['Sản phẩm', 'Giá', 'Đơn hàng', 'Tồn kho', 'Tổng tiền', 'Ngày tạo'];
            let productData = [];
            
            if (reportType === 'revenue') {
                // Tìm bảng sản phẩm bán chạy
                const titles = document.querySelectorAll('.card-title');
                let productTable = null;
                
                for (let i = 0; i < titles.length; i++) {
                    if (titles[i].textContent.includes('Sản phẩm bán chạy nhất')) {
                        const productCard = titles[i].closest('.card');
                        if (productCard) {
                            productTable = productCard.querySelector('table');
                            break;
                        }
                    }
                }
                
                if (productTable) {
                    const rows = productTable.querySelectorAll('tbody tr');
                    
                    rows.forEach(row => {
                        const nameElement = row.querySelector('.fs-14.my-1 a');
                        const dateElement = row.querySelector('.text-muted');
                        
                        // Lấy dữ liệu từ các ô
                        const cells = row.querySelectorAll('td');
                        const productName = nameElement ? nameElement.textContent.trim() : '';
                        const createdDate = dateElement ? dateElement.textContent.trim() : '';
                        
                        let price = '', orders = '', stock = '', totalAmount = '';
                        
                        if (cells.length >= 2) {
                            const priceElement = cells[1].querySelector('.fs-14.my-1.fw-normal');
                            price = priceElement ? priceElement.textContent.trim() : '';
                        }
                        
                        if (cells.length >= 3) {
                            const ordersElement = cells[2].querySelector('.fs-14.my-1.fw-normal');
                            orders = ordersElement ? ordersElement.textContent.trim() : '';
                        }
                        
                        if (cells.length >= 4) {
                            const stockElement = cells[3].querySelector('.fs-14.my-1.fw-normal');
                            stock = stockElement ? stockElement.textContent.trim() : '';
                        }
                        
                        if (cells.length >= 5) {
                            const amountElement = cells[4].querySelector('.fs-14.my-1.fw-normal');
                            totalAmount = amountElement ? amountElement.textContent.trim() : '';
                        }
                        
                        productData.push([productName, price, orders, stock, totalAmount, createdDate]);
                    });
                }
            }

            if ((data.length > 0 && headers.length > 0) || (reportType === 'revenue' && productData.length > 0)) {
                try {
                    // Tạo workbook mới
                    const workbook = new ExcelJS.Workbook();
                    workbook.creator = 'Eco-Furnish';
                    workbook.lastModifiedBy = '{{ Auth::user()->name }}';
                    workbook.created = new Date();
                    workbook.modified = new Date();

                    // Tạo sheet thông tin
                    const infoSheet = workbook.addWorksheet('Thông tin báo cáo');

                    // Thiết lập style cho tiêu đề
                    const titleStyle = {
                        font: { size: 18, bold: true, color: { argb: '2E75B6' } },
                        alignment: { horizontal: 'center', vertical: 'middle' },
                        fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: 'EBF1F9' } },
                        border: {
                            top: { style: 'medium', color: { argb: '2E75B6' } },
                            left: { style: 'medium', color: { argb: '2E75B6' } },
                            bottom: { style: 'medium', color: { argb: '2E75B6' } },
                            right: { style: 'medium', color: { argb: '2E75B6' } }
                        }
                    };

                    // Tiêu đề báo cáo
                    infoSheet.mergeCells('A1:G1');
                    const titleCell = infoSheet.getCell('A1');
                    titleCell.value = 'BÁO CÁO ' + reportTitle.toUpperCase();
                    Object.assign(titleCell, titleStyle);

                    // Thông tin báo cáo
                    infoSheet.mergeCells('A3:D3');
                    infoSheet.getCell('A3').value = 'Ngày xuất báo cáo: ' + new Date().toLocaleDateString('vi-VN');
                    infoSheet.getCell('A3').font = { size: 11 };

                    infoSheet.mergeCells('A4:D4');
                    infoSheet.getCell('A4').value = 'Người xuất báo cáo: {{ Auth::user()->name }}';
                    infoSheet.getCell('A4').font = { size: 11 };
                    
                    // Thêm thông tin về khoảng ngày đã lọc
                    infoSheet.mergeCells('A5:D5');
                    infoSheet.getCell('A5').value = dateRangeInfo;
                    infoSheet.getCell('A5').font = { size: 11, color: { argb: '4472C4' } };

                    infoSheet.mergeCells('A7:G7');
                    infoSheet.getCell('A7').value = 'Báo cáo được tạo tự động từ hệ thống Eco-Furnish';
                    infoSheet.getCell('A7').font = { size: 10, italic: true, color: { argb: '4472C4' } };
                    infoSheet.getCell('A7').alignment = { horizontal: 'center' };

                    // Tạo sheet dữ liệu
                    const dataSheet = workbook.addWorksheet('Dữ liệu');

                    // Hàm tạo style header
                    const createHeaderStyle = (row) => {
                        row.eachCell((cell) => {
                        cell.fill = {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: '2E75B6' }
                        };
                        cell.font = {
                            bold: true,
                            color: { argb: 'FFFFFF' },
                            size: 12
                        };
                        cell.alignment = {
                            horizontal: 'center',
                            vertical: 'middle'
                        };
                        cell.border = {
                            top: { style: 'medium', color: { argb: 'FFFFFF' } },
                            left: { style: 'medium', color: { argb: 'FFFFFF' } },
                            bottom: { style: 'medium', color: { argb: 'FFFFFF' } },
                            right: { style: 'medium', color: { argb: 'FFFFFF' } }
                        };
                    });
                    };
                    
                    // Hàm tạo style dữ liệu
                    const createDataStyle = (row, index) => {
                        const isAlternateRow = index % 2 === 1;
                        const rowColor = isAlternateRow ? 'F2F9FF' : 'FFFFFF';

                        row.eachCell((cell) => {
                            cell.fill = {
                                type: 'pattern',
                                pattern: 'solid',
                                fgColor: { argb: rowColor }
                            };
                            cell.border = {
                                top: { style: 'thin', color: { argb: 'D0D7E5' } },
                                left: { style: 'thin', color: { argb: 'D0D7E5' } },
                                bottom: { style: 'thin', color: { argb: 'D0D7E5' } },
                                right: { style: 'thin', color: { argb: 'D0D7E5' } }
                            };
                        });
                    };

                    // Hàm thiết lập độ rộng cột
                    const setColumnWidth = (sheet, headers) => {
                    headers.forEach((header, i) => {
                            const column = sheet.getColumn(i + 1);
                        column.width = Math.max(header.length * 1.5, 15);
                    });
                    };

                    // Thêm headers cho sheet dữ liệu
                    const headerRow = dataSheet.addRow(headers);
                    createHeaderStyle(headerRow);
                    headerRow.height = 30;

                    // Thêm dữ liệu cho sheet dữ liệu
                    data.forEach((rowData, index) => {
                        const row = dataSheet.addRow(rowData);
                        createDataStyle(row, index);
                    });
                    
                    // Thiết lập độ rộng cột cho sheet dữ liệu
                    setColumnWidth(dataSheet, headers);
                    
                    // Nếu báo cáo là báo cáo doanh thu, thêm sheet sản phẩm
                    if (reportType === 'revenue' && productData.length > 0) {
                        // Tạo sheet sản phẩm
                        const productSheet = workbook.addWorksheet('Sản phẩm');
                        
                        // Thêm headers cho sheet sản phẩm
                        const productHeaderRow = productSheet.addRow(productHeaders);
                        createHeaderStyle(productHeaderRow);
                        productHeaderRow.height = 30;
                        
                        // Thêm dữ liệu cho sheet sản phẩm
                        productData.forEach((rowData, index) => {
                            const row = productSheet.addRow(rowData);
                            createDataStyle(row, index);
                        });
                        
                        // Thiết lập độ rộng cột cho sheet sản phẩm
                        setColumnWidth(productSheet, productHeaders);
                    }

                    // Xuất file Excel
                    const buffer = await workbook.xlsx.writeBuffer();
                    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    
                    // Thêm khoảng ngày vào tên file nếu có
                    let fileName = `${reportTitle}_`;
                    if (dateRange) {
                        const rangeParts = dateRange.split(' đến ');
                        if (rangeParts.length > 0) {
                            fileName += rangeParts[0].replace(/-/g, '') + '_';
                            if (rangeParts.length > 1) {
                                fileName += rangeParts[1].replace(/-/g, '') + '_';
                            }
                        }
                    }
                    fileName += `${new Date().toISOString().slice(0, 10)}.xlsx`;
                    
                    saveAs(blob, fileName);

                    // Hiển thị thông báo
                    Toastify({
                        text: "Báo cáo đã được tải xuống!",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#4caf50",
                    }).showToast();
                } catch (error) {
                    console.error('Error exporting Excel:', error);
                    Toastify({
                        text: "Lỗi khi xuất báo cáo: " + error.message,
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#f44336",
                    }).showToast();
                }
            } else {
                Toastify({
                    text: "Không có dữ liệu để xuất báo cáo!",
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#f44336",
                }).showToast();
            }
        }

        // Hàm nhập dữ liệu từ Excel
        async function importFromExcel(file) {
            try {
                const reader = new FileReader();

                reader.onload = async function(e) {
                    const data = e.target.result;
                    const workbook = new ExcelJS.Workbook();
                    await workbook.xlsx.load(data);

                    const worksheet = workbook.getWorksheet(1);
                    if (!worksheet) {
                        throw new Error('Không thể đọc dữ liệu từ file Excel');
                    }

                    const jsonData = [];
                    worksheet.eachRow({ includeEmpty: false }, function(row, rowNumber) {
                        if (rowNumber > 1) { // Bỏ qua hàng tiêu đề
                            const rowData = {};
                            row.eachCell({ includeEmpty: true }, function(cell, colNumber) {
                                const headerCell = worksheet.getRow(1).getCell(colNumber);
                                rowData[headerCell.value] = cell.value;
                            });
                            jsonData.push(rowData);
                        }
                    });

                    if (jsonData.length > 0) {
                        console.log('Dữ liệu nhập:', jsonData);

                        // Hiển thị thông báo thành công
                        Toastify({
                            text: "Đã nhập dữ liệu thành công!",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#4caf50",
                        }).showToast();
                    } else {
                        Toastify({
                            text: "Không có dữ liệu trong file Excel!",
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            backgroundColor: "#f44336",
                        }).showToast();
                    }
                };

                reader.onerror = function() {
                    Toastify({
                        text: "Lỗi khi đọc file!",
                        duration: 3000,
                        close: true,
                        gravity: "top",
                        position: "right",
                        backgroundColor: "#f44336",
                    }).showToast();
                };

                reader.readAsArrayBuffer(file);
            } catch (error) {
                console.error('Error importing Excel:', error);
                Toastify({
                    text: "Lỗi khi nhập file: " + error.message,
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    backgroundColor: "#f44336",
                }).showToast();
            }
        }

        // Cập nhật biểu đồ khi form được submit
        document.getElementById('dateFilterForm').addEventListener('submit', function(e) {
            // Ngăn form submit mặc định vì ta sẽ xử lý AJAX
            e.preventDefault();
            
            // Lấy giá trị ngày đã chọn
            const dateRange = document.getElementById('dateRangePicker').value;
            
            if (dateRange) {
                // Chuyển hướng đến URL với tham số date_range
                window.location.href = "{{ route('dashboard.filter') }}?date_range=" + encodeURIComponent(dateRange);
            } else {
                // Nếu không có khoảng ngày, chuyển về trang dashboard mặc định
                window.location.href = "{{ route('dashboard') }}";
            }
        });
        
        // Chạy updateStatistics khi trang được tải
        document.addEventListener('DOMContentLoaded', function() {
            // Nếu có khoảng ngày trong url, áp dụng cho thống kê ngay khi tải trang
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('date_range')) {
                updateStatistics();
            }
            
            // Refresh AOS animations sau khi trang đã tải hoàn toàn
            setTimeout(function() {
                AOS.refresh();
            }, 500);
        });
        
        // Sự kiện khi tab hoặc cửa sổ được kích hoạt lại
        document.addEventListener('visibilitychange', function() {
            if (document.visibilityState === 'visible') {
                // Refresh AOS animations khi quay lại tab
                AOS.refresh();
            }
        });

        // Khởi tạo biểu đồ
        const chart = new ApexCharts(document.querySelector("#customer_impression_charts"), options);
        chart.render();
        
        // Hàm cập nhật biểu đồ khi dữ liệu thay đổi
        function updateChart(newData) {
            chart.updateOptions({
                labels: newData.months,
                xaxis: {
                    categories: newData.months
                }
            });
            chart.updateSeries(newData.series);
            
            // Cập nhật trạng thái overlay "Không có dữ liệu"
            updateNoDataOverlay(newData);
        }
        
        console.log('Biểu đồ đã được khởi tạo');
    });
</script>
@endsection