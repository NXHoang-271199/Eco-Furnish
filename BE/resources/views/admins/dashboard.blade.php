{{-- Để kế thừa lại admin layout ta sử dụng extends --}}
@extends('layouts.admin')
{{-- Một file chỉ được kế thừa 1 admin layout --}}

@section('title')
    Quản lý
@endsection

@section('CSS')
<style>
    /* Base styles */
    body, html {
        overflow-x: hidden !important;
        overflow-y: auto !important;
    }

    .container-fluid {
        overflow: hidden !important;
        position: relative;
        width: 100%;
    }

    .dashboard-container {
        padding-top: 60px !important;
        margin-top: 30px;
        overflow: hidden !important;
        position: relative;
        width: 100%;
    }
    .page-content {
        padding-top: 10px !important;
        overflow: hidden !important;
        position: relative;
        width: 100%;
    }
    @media (max-width: 768px) {
        .dashboard-container {
            padding-top: 80px !important;
        }
    }

    /* AOS animation container fix */
    [data-aos] {
        pointer-events: none;
    }
    [data-aos].aos-animate {
        pointer-events: auto;
    }

    /* Prevent horizontal scrollbar */
    .row {
        margin-right: 0;
        margin-left: 0;
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
@php
/**
 * Hàm định dạng ngày tháng từ chuỗi datetime
 * 
 * @param string|null $dateString Chuỗi ngày tháng
 * @return string Ngày tháng đã định dạng hoặc chuỗi rỗng
 */
function formatDateTime($dateString) {
    if (empty($dateString)) return '';
    
    try {
        $date = new \DateTime($dateString);
        return $date->format('d/m/Y H:i:s');
    } catch (\Exception $e) {
        return $dateString;
    }
}
@endphp

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
                                        <h5 class="{{ $earningsPercentage >= 0 ? 'text-success' : 'text-danger' }} fs-14 mb-0 d-none">
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
                                        <h5 class="{{ $ordersPercentage >= 0 ? 'text-success' : 'text-danger' }} fs-14 mb-0 d-none">
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
                                        <h5 class="{{ $customersPercentage >= 0 ? 'text-success' : 'text-danger' }} fs-14 mb-0 d-none">
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
                                                            <span class="text-muted">{{ $product->created_at ? formatDateTime($product->created_at) : 'N/A' }}</span>
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
                                            @if ($bestSellingProducts->total() > 0)
                                                Hiển thị từ <span class="fw-semibold">{{ $bestSellingProducts->firstItem() }}</span> đến <span class="fw-semibold">{{ $bestSellingProducts->lastItem() }}</span>
                                            @else
                                                Không tìm thấy kết quả nào
                                            @endif
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
                                                    <a class="page-link" href="{{ $bestSellingProducts->appends(request()->except('product_page'))->previousPageUrl() }}&product_page={{ $bestSellingProducts->currentPage() - 1 }}" rel="prev">←</a>
                                                </li>
                                            @endif

                                            {{-- Các nút số trang --}}
                                            @for($i = 1; $i <= $bestSellingProducts->lastPage(); $i++)
                                                <li class="page-item {{ $i == $bestSellingProducts->currentPage() ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ $bestSellingProducts->appends(request()->except('product_page'))->url(1) }}&product_page={{ $i }}">{{ $i }}</a>
                                                </li>
                                            @endfor

                                            {{-- Nút trang sau --}}
                                            @if ($bestSellingProducts->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $bestSellingProducts->appends(request()->except('product_page'))->nextPageUrl() }}&product_page={{ $bestSellingProducts->currentPage() + 1 }}" rel="next">→</a>
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
                                                    @php
                                                        // Phân loại khách hàng dựa trên số đơn hàng và tổng chi tiêu
                                                        $customerType = '';
                                                        $badgeClass = '';
                                                        $iconClass = '';
                                                        $animationClass = '';
                                                        
                                                        if ($buyer->orders_count >= 10 || $buyer->total_spent >= 20000000) {
                                                            $customerType = 'Người mua trung thành';
                                                            $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                                            $iconClass = 'ri-vip-crown-fill me-1';
                                                            $animationClass = 'badge-bounce';
                                                        } elseif ($buyer->orders_count >= 5 || $buyer->total_spent >= 10000000) {
                                                            $customerType = 'Khách hàng thân thiết';
                                                            $badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                                                            $iconClass = 'ri-star-fill me-1';
                                                            $animationClass = 'badge-pulse';
                                                        } elseif ($buyer->orders_count >= 3 || $buyer->total_spent >= 5000000) {
                                                            $customerType = 'Khách hàng tiềm năng';
                                                            $badgeClass = 'bg-info-subtle text-info border border-info-subtle';
                                                            $iconClass = 'ri-user-star-line me-1';
                                                            $animationClass = 'badge-fade';
                                                        } elseif ($buyer->orders_count >= 1) {
                                                            $customerType = 'Khách hàng mới';
                                                            $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                                            $iconClass = 'ri-user-add-line me-1';
                                                            $animationClass = 'badge-slide';
                                                        }
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $customerType }}</span>
                                                </td>
                                                <td>
                                                    <p class="mb-0">{{ $buyer->orders_count }} đơn hàng</p>
                                                </td>
                                                <td>
                                                    <h5 class="fs-14 mb-0">{{ number_format($buyer->total_spent, 0, ',', '.') }} ₫</h5>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="4" class="text-center">Không có dữ liệu người mua</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                @if($topBuyers->count() > 0)
                                <div class="align-items-center mt-4 pt-2 justify-content-between row text-center text-sm-start">
                                    <div class="col-sm">
                                        <div class="text-muted">
                                            @if ($topBuyers->total() > 0)
                                                Hiển thị từ <span class="fw-semibold">{{ $topBuyers->firstItem() }}</span> đến <span class="fw-semibold">{{ $topBuyers->lastItem() }}</span>
                                            @else
                                                Không tìm thấy người mua nào
                                            @endif
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
                                                    <a class="page-link" href="{{ $topBuyers->appends(request()->except('buyer_page'))->previousPageUrl() }}&buyer_page={{ $topBuyers->currentPage() - 1 }}" aria-label="Previous">
                                                        ←
                                                    </a>
                                                </li>
                                            @endif

                                            {{-- Các nút số trang --}}
                                            @for($i = 1; $i <= $topBuyers->lastPage(); $i++)
                                                <li class="page-item {{ $i == $topBuyers->currentPage() ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ $topBuyers->appends(request()->except('buyer_page'))->url(1) }}&buyer_page={{ $i }}">{{ $i }}</a>
                                                </li>
                                            @endfor

                                            {{-- Nút trang sau --}}
                                            @if($topBuyers->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $topBuyers->appends(request()->except('buyer_page'))->nextPageUrl() }}&buyer_page={{ $topBuyers->currentPage() + 1 }}" aria-label="Next">
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
                                <h4 class="card-title mb-0 flex-grow-1">Top 5 sản phẩm có lượt đánh giá cao nhất</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-info btn-sm" id="createTopRatedReport" data-report-type="toprated" data-report-title="Sản phẩm đánh giá cao">
                                        <i class="ri-file-list-3-line align-middle"></i> Tạo báo cáo
                                    </button>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-borderless table-centered align-middle table-nowrap mb-0">
                                        <thead class="text-muted table-light">
                                            <tr>
                                                <th scope="col">Sản phẩm</th>
                                                <th scope="col">Giá</th>
                                                <th scope="col">Tổng đánh giá</th>
                                                <th scope="col">Xếp hạng trung bình</th>
                                                <th scope="col">Trạng thái</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $topRatedProducts = App\Models\Product::withReviewStats()
                                                    ->has('reviews')
                                                    ->take(5)
                                                    ->get();
                                            @endphp
                                            @forelse($topRatedProducts as $product)
                                            <tr data-aos="fade-up" data-aos-duration="800" data-aos-delay="{{ 100 + $loop->index * 50 }}">
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-3">
                                                            <div class="avatar-sm bg-light rounded p-1">
                                                                <img src="{{ asset('storage/'.$product->image_thumnail) }}" alt="{{ $product->name }}" class="img-fluid d-block">
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <h5 class="fs-14 mb-1">
                                                                <a href="{{ route('products.show', $product->id) }}" class="text-dark">{{ $product->name }}</a>
                                                            </h5>
                                                            <p class="text-muted mb-0">Danh mục: <span class="fw-medium">{{ $product->category->name ?? 'N/A' }}</span></p>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        // Kiểm tra sản phẩm có biến thể không
                                                        $variants = $product->variants;
                                                        $hasVariants = $variants->count() > 0;

                                                        if ($hasVariants) {
                                                            $minPrice = $variants->min('price');
                                                            $maxPrice = $variants->max('price');

                                                            if ($minPrice != $maxPrice) {
                                                                echo number_format($minPrice, 0, ',', '.') . ' - ' . number_format($maxPrice, 0, ',', '.') . ' ₫';
                                                            } else {
                                                                echo number_format($minPrice, 0, ',', '.') . ' ₫';
                                                            }
                                                        } else {
                                                            echo number_format($product->price, 0, ',', '.') . ' ₫';
                                                        }
                                                    @endphp
                                                </td>
                                                <td>
                                                    <span class="badge bg-info-subtle text-info fs-12">{{ $product->total_reviews }} đánh giá</span>
                                                </td>
                                                <td>
                                                    <div class="text-warning fs-14 mb-0">
                                                        @php
                                                            $avgRating = $product->average_rating;
                                                            $fullStars = floor($avgRating);
                                                            $hasHalfStar = $avgRating - $fullStars >= 0.5;
                                                            $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                                        @endphp

                                                        @for($i = 0; $i < $fullStars; $i++)
                                                            <i class="ri-star-fill"></i>
                                                        @endfor

                                                        @if($hasHalfStar)
                                                            <i class="ri-star-half-fill"></i>
                                                        @endif

                                                        @for($i = 0; $i < $emptyStars; $i++)
                                                            <i class="ri-star-line"></i>
                                                        @endfor

                                                        <span class="ms-1">({{ number_format($avgRating, 1) }})</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    @php
                                                        // Sử dụng phương thức getTotalQuantityAttribute() có sẵn trong model Product
                                                        $totalStock = $product->total_quantity;
                                                        $statusClass = $totalStock > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                                                        $statusText = $totalStock > 0 ? 'Còn hàng' : 'Hết hàng';
                                                    @endphp
                                                    <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center">Không có sản phẩm nào có đánh giá</td>
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
                                    dashArray: 4,
                                    yAxisIndex: 2 // Sử dụng trục Y thứ 3 riêng biệt
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
                        
                        // Thêm hàm cập nhật thống kê tổng hợp
                        updateChartSummary(filteredData);

                        return filteredData;
                    }

                    // Nếu không có khoảng ngày, trả về dữ liệu gốc
                    const chartData = @json($chartData ?? null);
                    return chartData ? {
                        months: chartData.months,
                        series: chartData.series
                    } : { months: [], series: [] };
                }
                
                // Hàm cập nhật thống kê tổng hợp dựa trên dữ liệu đã lọc
                function updateChartSummary(filteredData) {
                    // Chỉ thực hiện nếu có dữ liệu đã lọc
                    if (!filteredData || !filteredData.series || filteredData.series.length < 3) return;
                    
                    let totalOrders = 0;
                    let totalRevenue = 0;
                    let totalRefunds = 0;
                    
                    // Tính tổng đơn hàng từ series 0
                    if (filteredData.series[0] && filteredData.series[0].data) {
                        totalOrders = filteredData.series[0].data.reduce((sum, val) => sum + (val || 0), 0);
                    }
                    
                    // Tính tổng doanh thu từ series 1
                    if (filteredData.series[1] && filteredData.series[1].data) {
                        totalRevenue = filteredData.series[1].data.reduce((sum, val) => sum + (val || 0), 0);
                    }
                    
                    // Tính tổng hoàn tiền từ series 2
                    if (filteredData.series[2] && filteredData.series[2].data) {
                        totalRefunds = filteredData.series[2].data.reduce((sum, val) => sum + (val || 0), 0);
                    }
                    
                    // Cập nhật giá trị hiển thị
                    const orderCounter = document.getElementById('chart-orders-counter');
                    if (orderCounter) {
                        orderCounter.setAttribute('data-target', totalOrders);
                        orderCounter.textContent = '0'; // Reset để animation chạy lại
                    }
                    
                    const revenueCounter = document.getElementById('chart-revenue-counter');
                    if (revenueCounter) {
                        revenueCounter.setAttribute('data-target', totalRevenue);
                        revenueCounter.textContent = '0'; // Reset để animation chạy lại
                    }
                    
                    const refundCounter = document.getElementById('chart-refunds-counter');
                    if (refundCounter) {
                        refundCounter.setAttribute('data-target', totalRefunds);
                        refundCounter.textContent = '0'; // Reset để animation chạy lại
                    }
                    
                    // Khởi động lại animation đếm
                    initCounters();
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
                            min: 0,
                            max: 12, // Đặt giá trị tối đa cho trục hoàn tiền
                            tickAmount: 6,
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
                                },
                                formatter: function (value) {
                                    return Math.round(value);
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
                        custom: function({ series, seriesIndex, dataPointIndex, w }) {
                            const seriesName = w.config.series[seriesIndex].name;
                            const colors = ["#4776E6", "#63ad6f", "#f34e4e"];
                            
                            // Lấy dữ liệu hiện tại
                            const month = w.globals.labels[dataPointIndex];
                            const orders = series[0][dataPointIndex];
                            const revenue = series[1][dataPointIndex];
                            const refunds = series[2][dataPointIndex];
                            
                            if (orders === 0 && revenue === 0 && refunds === 0) {
                                return '<div class="apexcharts-tooltip-title" style="font-weight: bold; margin-bottom: 5px; text-align: center;">Tháng ' + month + '</div>' +
                                       '<div style="padding: 10px; text-align: center;">Không có dữ liệu</div>';
                            }
                            
                            return '<div class="apexcharts-tooltip-title" style="font-weight: bold; margin-bottom: 5px; text-align: center;">Tháng ' + month + '</div>' +
                                   '<div style="padding: 5px 10px;">' +
                                   '<div style="display: flex; align-items: center; margin-bottom: 5px;">' +
                                   '<span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: ' + colors[0] + '; margin-right: 5px;"></span>' +
                                   '<span style="flex: 1;">Đơn hàng:</span>' +
                                   '<span style="font-weight: bold;">' + orders + ' đơn</span>' +
                                   '</div>' +
                                   '<div style="display: flex; align-items: center; margin-bottom: 5px;"> ' +
                                   '<span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: ' + colors[1] + '; margin-right: 5px;"></span>' +
                                   '<span style="flex: 1;">Doanh thu: </span>' +
                                   '<span style="font-weight: bold;"> ' + formatCurrency(revenue) + '</span>' +
                                   '</div>' +
                                   '<div style="display: flex; align-items: center;">' +
                                   '<span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: ' + colors[2] + '; margin-right: 5px;"></span>' +
                                   '<span style="flex: 1;">Hoàn tiền:</span>' +
                                   '<span style="font-weight: bold;">' + refunds + ' đơn</span>' +
                                   '</div>' +
                                   '</div>';
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
                
                // Thêm phần xử lý để điều chỉnh hiển thị tooltip biểu đồ
                document.querySelector("#customer_impression_charts").addEventListener('mouseover', function(e) {
                    const tooltipEl = document.querySelector('.apexcharts-tooltip');
                    if (tooltipEl) {
                        tooltipEl.style.padding = '0';
                        tooltipEl.style.boxShadow = '0 5px 15px rgba(0,0,0,0.15)';
                        tooltipEl.style.borderRadius = '8px';
                    }
                });

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
                headers = ['Khách hàng', 'Email', 'Loại', 'Đơn hàng', 'Chi tiêu'];

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

                        let type = '', orders = '', spent = '';

                        if (cells.length >= 2) type = cells[1].textContent.trim();
                        if (cells.length >= 3) orders = cells[2].textContent.trim();
                        if (cells.length >= 4) spent = cells[3].textContent.trim();

                        data.push([buyerName, email, type, orders, spent]);
                    });
                }
            } else if (reportType === 'products') {
                // Thu thập dữ liệu từ bảng sản phẩm bán chạy
                headers = ['Sản phẩm', 'Giá', 'Đơn hàng', 'Tồn kho', 'Tổng tiền'];

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
                        const createdDate = jsFormatDateTime(dateElement ? dateElement.textContent.trim() : '');

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

                        data.push([productName, price, orders, stock, totalAmount]);
                    });
                }
            } else if (reportType === 'revenue') {
                // Thu thập dữ liệu cho báo cáo doanh thu
                headers = ['Tháng/Ngày', 'Đơn hàng', 'Doanh thu', 'Hoàn tiền'];

                // Lấy dữ liệu gốc từ Blade
                const allMonthlyData = @json($monthlyData ?? []);

                // Lấy khoảng ngày đang được chọn
                const dateRangeInput = document.getElementById('dateRangePicker');
                const dateRange = dateRangeInput ? dateRangeInput.value : '';
                let startDate = null;
                let endDate = null;

                if (dateRange) {
                    const rangeParts = dateRange.split(' đến ');
                    startDate = new Date(rangeParts[0]);
                    // Đặt giờ về 00:00:00 để so sánh chính xác
                    startDate.setHours(0, 0, 0, 0);

                    if (rangeParts.length > 1) {
                        endDate = new Date(rangeParts[1]);
                    } else {
                        endDate = new Date(rangeParts[0]);
                    }
                    // Đặt giờ về 23:59:59 để bao gồm cả ngày cuối
                    endDate.setHours(23, 59, 59, 999);
                }

                console.log("Filtering revenue data for range:", startDate, endDate); // DEBUG

                // Lọc dữ liệu dựa trên khoảng ngày
                const filteredMonthlyData = allMonthlyData.filter(item => {
                    if (!startDate || !endDate) {
                        return true; // Nếu không có bộ lọc, lấy tất cả
                    }

                    // Chuyển đổi "Tháng X" hoặc định dạng ngày thành đối tượng Date
                    let itemDate;
                    if (item.month.startsWith('Th')) { // Xử lý định dạng "Tháng X"
                        const monthNumber = parseInt(item.month.replace('Th', ''));
                        if (!isNaN(monthNumber)) {
                            // Giả sử là năm hiện tại. Cần điều chỉnh nếu dữ liệu có thể qua nhiều năm.
                            const year = new Date().getFullYear();
                            // Lấy ngày đầu tiên của tháng đó
                            itemDate = new Date(year, monthNumber - 1, 1);
                        } else {
                            return false; // Không thể phân tích tháng
                        }
                    } else { // Thử phân tích các định dạng ngày khác nếu có
                       try {
                           // Cố gắng phân tích ngày trực tiếp nếu backend trả về định dạng khác
                           itemDate = new Date(item.month);
                           if (isNaN(itemDate.getTime())) { // Kiểm tra xem Date có hợp lệ không
                                return false;
                           }
                           itemDate.setHours(0,0,0,0); // Chuẩn hóa về đầu ngày
                       } catch (e) {
                           return false; // Không thể phân tích ngày
                       }
                    }

                    // Đối với định dạng "Tháng X", chúng ta cần kiểm tra xem *bất kỳ* ngày nào trong tháng đó
                    // có nằm trong khoảng thời gian lọc hay không. Hoặc đơn giản hơn, nếu bộ lọc chỉ trong 1 tháng,
                    // ta kiểm tra tháng đó. Nếu bộ lọc qua nhiều tháng, ta bao gồm các tháng nằm giữa.
                    // --- Logic đơn giản hóa: Kiểm tra ngày đầu tháng --- 
                    // (Cách này có thể không chính xác hoàn toàn nếu bộ lọc chỉ vài ngày giữa tháng,
                    // nhưng phù hợp nếu backend đã lọc sẵn theo tháng)
                    return itemDate >= startDate && itemDate <= endDate;
                });

                console.log("Filtered Monthly Data:", filteredMonthlyData); // DEBUG

                if (filteredMonthlyData && filteredMonthlyData.length > 0) {
                    filteredMonthlyData.forEach(item => {
                        // Sử dụng tên tháng/ngày từ dữ liệu đã lọc
                        const displayMonth = item.month;
                        data.push([
                            displayMonth,
                            item.orders.toString(),
                            item.revenue.toLocaleString('vi-VN') + ' ₫',
                            item.refunds.toString()
                        ]);
                    });
                } else if (!dateRange) { // Chỉ hiển thị dữ liệu mẫu nếu không có bộ lọc VÀ không có dữ liệu thực
                    // Dữ liệu mẫu nếu không có dữ liệu thực
                    for (let i = 1; i <= 12; i++) {
                        data.push([
                            'Tháng ' + i,
                            Math.floor(Math.random() * 500 + 300).toString(),
                            (Math.random() * 10000000 + 5000000).toLocaleString('vi-VN') + ' ₫',
                            Math.floor(Math.random() * 30).toString()
                        ]);
                    }
                } // Không thêm dòng nào nếu có bộ lọc nhưng không có dữ liệu
            } else if (reportType === 'toprated') {
                // Thu thập dữ liệu từ bảng sản phẩm đánh giá cao nhất
                headers = ['Sản phẩm', 'Danh mục', 'Giá', 'Tổng đánh giá', 'Xếp hạng TB', 'Trạng thái'];

                // Tìm bảng sản phẩm đánh giá cao
                const titles = document.querySelectorAll('.card-title');
                let ratedTable = null;

                for (let i = 0; i < titles.length; i++) {
                    if (titles[i].textContent.includes('Top 5 sản phẩm có lượt đánh giá cao nhất')) {
                        const ratedCard = titles[i].closest('.card');
                        if (ratedCard) {
                            ratedTable = ratedCard.querySelector('table');
                            break;
                        }
                    }
                }

                if (ratedTable) {
                    const rows = ratedTable.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        const cells = row.querySelectorAll('td');
                        let productName = '', category = '', price = '', totalReviews = '', avgRating = '', status = '';

                        if (cells.length > 0) {
                            const nameElement = cells[0].querySelector('.text-dark');
                            const categoryElement = cells[0].querySelector('.fw-medium');
                            productName = nameElement ? nameElement.textContent.trim() : '';
                            category = categoryElement ? categoryElement.textContent.trim() : '';
                        }
                        if (cells.length > 1) price = cells[1].textContent.trim();
                        if (cells.length > 2) totalReviews = cells[2].textContent.trim();
                        if (cells.length > 3) {
                            const ratingElement = cells[3].querySelector('.ms-1'); // Lấy phần text (x.x)
                            avgRating = ratingElement ? ratingElement.textContent.replace(/[\(\)]/g, '').trim() : '';
                        }
                        if (cells.length > 4) status = cells[4].textContent.trim();

                        data.push([productName, category, price, totalReviews, avgRating, status]);
                    });
                }
            } else if (reportType === 'topbuyers') {
                // Thu thập dữ liệu từ bảng người mua hàng nhiều nhất
                headers = ['Khách hàng', 'Email', 'Loại', 'Đơn hàng', 'Tổng chi tiêu'];

                // Tìm bảng người mua hàng
                const titles = document.querySelectorAll('.card-title');
                let buyerTable = null;

                for (let i = 0; i < titles.length; i++) {
                    if (titles[i].textContent.includes('Xếp hạng người mua hàng nhiều nhất')) { // Sửa lại tên tiêu đề cho chính xác
                        const buyerCard = titles[i].closest('.card');
                        if (buyerCard) {
                            buyerTable = buyerCard.querySelector('table');
                            break;
                        }
                    }
                }

                console.log('Buyer Table:', buyerTable); // DEBUG

                if (buyerTable) {
                    const rows = buyerTable.querySelectorAll('tbody tr');
                    console.log('Rows found:', rows.length); // DEBUG

                    rows.forEach((row, rowIndex) => {
                        console.log('Processing row:', rowIndex, row); // DEBUG
                        const cells = row.querySelectorAll('td');

                        // Lấy tên và email từ cấu trúc div > div > h5/span
                        const nameElement = cells[0]?.querySelector('h5 a'); // SỬA SELECTOR
                        const emailElement = cells[0]?.querySelector('span.text-muted'); // SỬA SELECTOR

                        const buyerName = nameElement ? nameElement.textContent.trim() : '';
                        const email = emailElement ? emailElement.textContent.trim() : '';

                        let type = '', orders = '', spent = '';

                        if (cells.length >= 2) type = cells[1].textContent.trim();
                        if (cells.length >= 3) orders = cells[2].textContent.trim(); // Đơn hàng là ở cột 3 (index 2)
                        if (cells.length >= 4) { // Tổng chi tiêu là ở cột 4 (index 3)
                            const spentElement = cells[3].querySelector('h5'); // SỬA SELECTOR
                            spent = spentElement ? spentElement.textContent.trim() : '';
                        }

                        console.log('Extracted data:', buyerName, email, type, orders, spent); // DEBUG

                        // Chỉ thêm hàng nếu có tên khách hàng (để tránh hàng trống)
                        if (buyerName) {
                           data.push([buyerName, email, type, orders, spent]);
                        }
                    });
                }
            } else if (reportType === 'products') {
                // Thu thập dữ liệu từ bảng sản phẩm bán chạy
                headers = ['Sản phẩm', 'Giá', 'Đơn hàng', 'Tồn kho', 'Tổng tiền'];

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
                        const createdDate = jsFormatDateTime(dateElement ? dateElement.textContent.trim() : '');

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

                        data.push([productName, price, orders, stock, totalAmount]);
                    });
                }
            }

            // Kiểm tra và debug
            console.log('Loại báo cáo:', reportType);
            console.log('Tiêu đề:', headers);
            console.log('Dữ liệu:', data);

            // Thu thập dữ liệu sản phẩm bán chạy riêng cho báo cáo doanh thu
            let productHeaders = ['Sản phẩm', 'Giá', 'Đơn hàng', 'Tồn kho', 'Tổng tiền', 'Ngày tạo'];
            let productData = [];

            if (reportType === 'revenue') {
                // Kiểm tra xem có đang dùng bộ lọc ngày hay không
                const dateRangeInput = document.getElementById('dateRangePicker');
                const isUsingDateFilter = dateRangeInput && dateRangeInput.value.trim() !== '';
                
                if (isUsingDateFilter) {
                    // Nếu có bộ lọc, lấy dữ liệu từ bảng trong trang
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
                            
                            // Xử lý đặc biệt cho ngày tạo khi có bộ lọc
                            let createdDate = '';
                            if (dateElement) {
                                // Lấy text gốc từ DOM
                                const dateText = dateElement.textContent.trim();
                                // Nếu có thể, cố gắng chuyển đổi sang định dạng dd/mm/yyyy hh:mm:ss
                                try {
                                    // Kiểm tra nếu date đã đúng định dạng dd/mm/yyyy
                                    if (/^\d{2}\/\d{2}\/\d{4}/.test(dateText)) {
                                        createdDate = dateText;
                                    } else {
                                        // Nếu là định dạng YYYY-MM-DD
                                        const date = new Date(dateText);
                                        if (!isNaN(date.getTime())) {
                                            const year = date.getFullYear();
                                            const month = String(date.getMonth() + 1).padStart(2, '0');
                                            const day = String(date.getDate()).padStart(2, '0');
                                            const hours = String(date.getHours()).padStart(2, '0');
                                            const minutes = String(date.getMinutes()).padStart(2, '0');
                                            const seconds = String(date.getSeconds()).padStart(2, '0');
                                            
                                            createdDate = `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
                                        } else {
                                            createdDate = dateText; // Giữ nguyên nếu không chuyển đổi được
                                        }
                                    }
                                } catch (e) {
                                    console.error('Lỗi khi định dạng ngày từ DOM:', e);
                                    createdDate = dateText; // Giữ nguyên nếu có lỗi
                                }
                            }

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
                } else {
                    // Nếu không có bộ lọc, lấy dữ liệu từ tất cả các sản phẩm đã order (từ tất cả các tab thời gian)
                    // Đầu tiên, lấy từ tab hiện tại
                    let currentProductsData = [];
                    
                    // Tìm bảng sản phẩm bán chạy bằng phương pháp lặp qua các tiêu đề
                    const allTitles = document.querySelectorAll('.card-title');
                    let currentProductsTable = null;
                    
                    for (let i = 0; i < allTitles.length; i++) {
                        if (allTitles[i].textContent.includes('Sản phẩm bán chạy nhất')) {
                            const productCard = allTitles[i].closest('.card');
                            if (productCard) {
                                currentProductsTable = productCard.querySelector('table');
                                break;
                            }
                        }
                    }
                    
                    if (currentProductsTable) {
                        const rows = currentProductsTable.querySelectorAll('tbody tr');
                        rows.forEach(row => {
                            const nameElement = row.querySelector('.fs-14.my-1 a');
                            const dateElement = row.querySelector('.text-muted');
                            
                            const cells = row.querySelectorAll('td');
                            const productName = nameElement ? nameElement.textContent.trim() : '';
                            const createdDate = jsFormatDateTime(dateElement ? dateElement.textContent.trim() : '');
                            
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
                            
                            currentProductsData.push({
                                name: productName,
                                price: price,
                                orders: orders,
                                stock: stock,
                                totalAmount: totalAmount,
                                createdDate: createdDate
                            });
                        });
                    }
                    
                    // Lấy dữ liệu từ server cho tất cả sản phẩm đã order
                    @php
                        // Lấy danh sách tất cả sản phẩm đã từng được order với tổng số lượng, tổng tiền và tồn kho
                        $allOrderedProducts = App\Models\Product::withTrashed()
                            ->select(
                                'products.id', 
                                'products.name', 
                                'products.price', 
                                'products.image_thumnail',
                                DB::raw('COUNT(DISTINCT order_items.order_id) as total_sold'), 
                                DB::raw('SUM(order_items.quantity * order_items.price) as total_amount'),
                                DB::raw('MIN(order_items.created_at) as first_order_date'), // Lấy ngày đặt hàng đầu tiên
                                DB::raw('CASE 
                                    WHEN COUNT(DISTINCT product_variants.id) > 0 THEN SUM(DISTINCT product_variants.quantity) 
                                    ELSE products.quantity 
                                END as total_stock')
                            )
                            ->leftJoin('product_variants', 'products.id', '=', 'product_variants.product_id')
                            ->leftJoin('order_items', function($join) {
                                $join->on('products.id', '=', 'order_items.product_id')
                                    ->orOn('product_variants.id', '=', 'order_items.product_variant_id');
                            })
                            ->leftJoin('orders', 'order_items.order_id', '=', 'orders.id')
                            ->whereNotNull('order_items.id')
                            ->where(function($query) {
                                $query->where('orders.payment_status', 'paid')
                                     ->orWhere('orders.payment_status', 1);
                            })
                            ->groupBy('products.id', 'products.name', 'products.price', 'products.image_thumnail', 'products.quantity')
                            ->orderByDesc('total_sold')
                            ->get();
                            
                        // Thêm thông tin giá và ngày tạo chính xác cho mỗi sản phẩm
                        foreach ($allOrderedProducts as $product) {
                            // Tìm order_item đầu tiên của sản phẩm này để lấy giá biến thể nếu có
                            $firstOrderItem = App\Models\OrderItem::where(function($query) use ($product) {
                                    $query->where('product_id', $product->id)
                                          ->orWhereHas('productVariant', function($q) use ($product) {
                                              $q->where('product_id', $product->id);
                                          });
                                })
                                ->whereHas('order', function($query) {
                                    $query->where(function($q) {
                                        $q->where('payment_status', 'paid')
                                          ->orWhere('payment_status', 1);
                                    });
                                })
                                ->orderBy('created_at', 'asc')
                                ->first();
                            
                            if ($firstOrderItem) {
                                // Nếu sản phẩm có biến thể, lấy giá từ biến thể đó
                                if ($firstOrderItem->product_variant_id) {
                                    $product->variant_price = $firstOrderItem->price;
                                }
                            }
                            
                            // Sử dụng ngày đặt hàng đầu tiên từ truy vấn tổng hợp
                            $product->created_at = $product->first_order_date;
                            
                            // Lấy lại thông tin tồn kho chính xác
                            $actualProduct = App\Models\Product::find($product->id);
                            if ($actualProduct) {
                                $product->total_stock = $actualProduct->total_quantity;
                            }
                        }
                    @endphp

                    // Chuyển đổi dữ liệu từ server thành mảng JavaScript để sử dụng trong báo cáo
                    const allOrderedProductsData = @json($allOrderedProducts ?? []);
                    
                    // Kết hợp dữ liệu từ tất cả các nguồn
                    if (allOrderedProductsData && allOrderedProductsData.length > 0) {
                        // Tạo một Map để theo dõi các sản phẩm đã được thêm vào
                        const processedProducts = new Map();
                        
                        allOrderedProductsData.forEach(product => {
                            // Kiểm tra xem sản phẩm đã có trong dữ liệu hiện tại chưa
                            const existingProduct = currentProductsData.find(p => p.name === product.name);
                            
                            // Nếu sản phẩm đã được xử lý, bỏ qua
                            if (processedProducts.has(product.id)) {
                                return;
                            }
                            
                            processedProducts.set(product.id, true);
                            
                            if (!existingProduct) {
                                // Nếu chưa có, chuyển đổi dữ liệu từ server sang định dạng mảng cho báo cáo Excel
                                // Lấy biến thể giá (nếu có)
                                let priceDisplay = '';
                                try {
                                    // Sử dụng giá biến thể nếu có, nếu không thì sử dụng giá sản phẩm
                                    const priceValue = product.variant_price 
                                        ? parseFloat(product.variant_price) 
                                        : parseFloat(product.price);
                                        
                                    if (!isNaN(priceValue)) {
                                        priceDisplay = priceValue.toLocaleString('vi-VN') + ' ₫';
                                    } else {
                                        priceDisplay = '0 ₫';
                                    }
                                } catch (e) {
                                    priceDisplay = '0 ₫';
                                }

                                // Format ngày tạo
                                let createdDate = '';
                                try {
                                    // Sử dụng ngày đặt hàng đầu tiên
                                    if (product.created_at) {
                                        createdDate = jsFormatDateTime(product.created_at);
                                    }
                                } catch (e) {
                                    console.error('Lỗi khi định dạng ngày:', e);
                                    createdDate = '';
                                }

                                // Lấy tồn kho chính xác từ dữ liệu PHP
                                let stockDisplay = product.total_stock !== undefined ? product.total_stock.toString() : '0';
                                
                                // Thêm sản phẩm vào danh sách
                                productData.push([
                                    product.name,
                                    priceDisplay,
                                    product.total_sold.toString(),
                                    stockDisplay,
                                    product.total_amount.toLocaleString('vi-VN') + ' ₫',
                                    createdDate
                                ]);
                            } else {
                                // Nếu sản phẩm đã có trong dữ liệu hiện tại (khi có bộ lọc)
                                productData.push([
                                    existingProduct.name,
                                    existingProduct.price,
                                    existingProduct.orders,
                                    product.total_stock !== undefined ? product.total_stock.toString() : existingProduct.stock,
                                    existingProduct.totalAmount,
                                    existingProduct.createdDate // Sử dụng thời gian từ DOM khi có bộ lọc
                                ]);
                            }
                        });

                        // Sắp xếp theo số lượng đơn hàng giảm dần
                        productData.sort((a, b) => {
                            const ordersA = parseInt(a[2].replace(/[^0-9]/g, '')) || 0;
                            const ordersB = parseInt(b[2].replace(/[^0-9]/g, '')) || 0;
                            return ordersB - ordersA;
                        });
                    }
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
                
                // Đảm bảo cập nhật thống kê tổng hợp biểu đồ sau khi trang đã tải
                setTimeout(function() {
                    // Lấy dữ liệu biểu đồ đã được lọc
                    const filteredData = filterChartData();
                    if (filteredData) {
                        // Cập nhật thống kê tổng hợp
                        updateChartSummary(filteredData);
                    }
                }, 500);
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
            
            // Cập nhật thống kê tổng hợp
            updateChartSummary(newData);
        }

        console.log('Biểu đồ đã được khởi tạo');
    });

    // Hàm tiện ích để định dạng ngày tháng trong JavaScript
    function jsFormatDateTime(dateStr) {
        // Nếu không có giá trị ngày tháng hoặc là null/undefined, trả về 'N/A'
        if (!dateStr) return '';
        
        try {
            // Xử lý trường hợp đầu vào đã được định dạng dd/mm/yyyy
            if (/^\d{2}\/\d{2}\/\d{4}/.test(dateStr)) {
                return dateStr; // Giữ nguyên định dạng đã có
            }
            
            // Nếu là chuỗi ngày tháng định dạng ISO (YYYY-MM-DD HH:MM:SS)
            if (/^\d{4}-\d{2}-\d{2}T|\d{4}-\d{2}-\d{2} /.test(dateStr)) {
                const date = new Date(dateStr);
                if (!isNaN(date.getTime())) {
                    // Định dạng dd/mm/yyyy hh:mm:ss
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    const hours = String(date.getHours()).padStart(2, '0');
                    const minutes = String(date.getMinutes()).padStart(2, '0');
                    const seconds = String(date.getSeconds()).padStart(2, '0');
                    
                    return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
                }
            }
            
            // Cố gắng chuyển đổi các định dạng khác
            const date = new Date(dateStr);
            if (!isNaN(date.getTime())) {
                // Định dạng dd/mm/yyyy hh:mm:ss
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');
                const seconds = String(date.getSeconds()).padStart(2, '0');
                
                return `${day}/${month}/${year} ${hours}:${minutes}:${seconds}`;
            }
            
            // Nếu không phải định dạng ngày tháng hợp lệ, trả về chuỗi ban đầu
            return dateStr;
        } catch (e) {
            console.error('Lỗi khi định dạng ngày tháng:', e, 'cho giá trị:', dateStr);
            // Nếu có lỗi, trả về chuỗi ban đầu
            return dateStr;
        }
    }
</script>
@endsection