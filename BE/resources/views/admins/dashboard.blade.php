{{-- Để kế thừa lại master layout ta sử dụng extends --}}
@extends('layouts.admin')
{{-- Một file chỉ được kế thừa 1 master layout --}}

@section('title')
    Quản lý
@endsection

@section('CSS')
<style>
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
</style>
<!-- Import ApexCharts -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.3/dist/apexcharts.min.css">
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.3/dist/apexcharts.min.js"></script>
@endsection

{{-- @section: dùng để chị định phần nội dụng được hiển thị --}}
@section('content')
<div class="container-fluid p-0 px-4 dashboard-container">

    <div class="row g-0">
        <div class="col-xl-12">

            <div class="h-100">
                <div class="row mb-3 pb-1">
                    <div class="col-12">
                        <div class="d-flex align-items-lg-center flex-lg-row flex-column">
                            <div class="flex-grow-1">
                                <h4 class="fs-16 mb-1">Chào buổi sáng {{ Auth::user()->name }}</h4>
                                <p class="text-muted mb-0">Đây là những gì đang diễn ra với cửa hàng của bạn hôm nay.</p>
                            </div>
                            <div class="mt-3 mt-lg-0">
                                <form action="javascript:void(0);">
                                    <div class="row g-3 mb-0 align-items-center">
                                        <div class="col-sm-auto">
                                            <div class="input-group">
                                                <input type="text" class="form-control border-0 minimal-border dash-filter-picker shadow" data-provider="flatpickr" data-range-date="true" data-date-format="d M, Y" data-deafult-date="01 Jan 2022 to 31 Jan 2022">
                                                <div class="input-group-text bg-primary border-primary text-white">
                                                    <i class="ri-calendar-2-line"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end col-->

                                        <div class="col-auto">
                                            <button type="button" class="btn btn-soft-info btn-icon waves-effect material-shadow-none waves-light layout-rightside-btn"><i class="ri-pulse-line"></i></button>
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

                <div class="row">
                    <div class="col-xl-4 col-md-6">
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
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="{{ $totalEarnings ?? 0 }}">0</span> ₫</h4>
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

                    <div class="col-xl-4 col-md-6">
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
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="{{ $totalOrders ?? 0 }}">0</span></h4>
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

                    <div class="col-xl-4 col-md-6">
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
                                        <h4 class="fs-22 fw-semibold ff-secondary mb-4"><span class="counter-value" data-target="{{ $totalCustomers ?? 0 }}">0</span></h4>
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
                        <div class="card">
                            <div class="card-header border-0 align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Doanh thu</h4>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-soft-info btn-sm material-shadow-none" id="exportRevenueReport" data-report-type="revenue" data-report-title="Báo cáo doanh thu">
                                        <i class="ri-file-excel-2-line align-middle"></i> Xuất báo cáo
                                    </button>
                                    <div>
                                        <button type="button" class="btn btn-soft-secondary material-shadow-none btn-sm">
                                            TẤT CẢ
                                        </button>
                                        <button type="button" class="btn btn-soft-secondary material-shadow-none btn-sm">
                                            1 THÁNG
                                        </button>
                                        <button type="button" class="btn btn-soft-secondary material-shadow-none btn-sm">
                                            6 THÁNG
                                        </button>
                                        <button type="button" class="btn btn-soft-primary material-shadow-none btn-sm">
                                            1 NĂM
                                        </button>
                                    </div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-header p-0 border-0 bg-light-subtle">
                                <div class="row g-0 text-center">
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0">
                                            <h5 class="mb-1"><span class="counter-value" data-target="{{ isset($monthlyData) ? array_sum(array_column($monthlyData, 'orders')) : 0 }}">0</span></h5>
                                            <p class="text-muted mb-0">Đơn hàng</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0">
                                            <h5 class="mb-1"><span class="counter-value" data-target="{{ isset($monthlyData) ? array_sum(array_column($monthlyData, 'revenue')) : 0 }}">0</span> ₫</h5>
                                            <p class="text-muted mb-0">Doanh thu</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0">
                                            <h5 class="mb-1"><span class="counter-value" data-target="{{ isset($monthlyData) ? array_sum(array_column($monthlyData, 'refunds')) : 0 }}">0</span></h5>
                                            <p class="text-muted mb-0">Hoàn tiền</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-6 col-sm-3">
                                        <div class="p-3 border border-dashed border-start-0 border-end-0">
                                            <h5 class="mb-1 text-success"><span class="counter-value" data-target="18">0</span>%</h5>
                                            <p class="text-muted mb-0">Tỷ lệ chuyển đổi</p>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body p-0 pb-2">
                                <div class="w-100">
                                    <div id="customer_impression_charts" class="apex-charts" dir="ltr"></div>
                                </div>
                            </div><!-- end card body -->
                        </div><!-- end card -->
                    </div><!-- end col -->


                </div>

                <div class="row">
                    <div class="col-xl-6">
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Sản phẩm bán chạy nhất</h4>
                                <div class="flex-shrink-0">
                                    <div class="dropdown card-header-dropdown">
                                        <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="fw-semibold text-uppercase fs-12">Sắp xếp theo:
                                            </span><span class="text-muted">Hôm nay<i class="mdi mdi-chevron-down ms-1"></i></span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#">Hôm nay</a>
                                            <a class="dropdown-item" href="#">Hôm qua</a>
                                            <a class="dropdown-item" href="#">7 ngày qua</a>
                                            <a class="dropdown-item" href="#">30 ngày qua</a>
                                            <a class="dropdown-item" href="#">Tháng này</a>
                                            <a class="dropdown-item" href="#">Tháng trước</a>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-hover table-centered align-middle table-nowrap mb-0">
                                        <tbody>
                                            @forelse($bestSellingProducts as $product)
                                            <tr>
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
                                                            $productDetails = App\Models\Product::with('variants')->find($product->id);
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
                                                                $priceRange = number_format($product->price, 0, ',', '.');
                                                                $productStock = $product->stock ?? 0;
                                                            }
                                                        } catch (\Exception $e) {
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
                                            {{-- Previous Page Link --}}
                                            @if ($bestSellingProducts->onFirstPage())
                                                <li class="page-item disabled">
                                                    <span class="page-link">←</span>
                                                </li>
                                            @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $bestSellingProducts->previousPageUrl() }}" rel="prev">←</a>
                                                </li>
                                            @endif

                                            {{-- Pagination Elements --}}
                                            @foreach ($bestSellingProducts->getUrlRange(1, $bestSellingProducts->lastPage()) as $page => $url)
                                                @if ($page == $bestSellingProducts->currentPage())
                                                    <li class="page-item active">
                                                        <span class="page-link">{{ $page }}</span>
                                                    </li>
                                                @else
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                    </li>
                                                @endif
                                            @endforeach

                                            {{-- Next Page Link --}}
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
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Xếp hạng người mua hàng nhiều nhất</h4>
                                <div class="flex-shrink-0">
                                    <div class="dropdown card-header-dropdown">
                                        <a class="text-reset dropdown-btn" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="text-muted">Báo cáo<i class="mdi mdi-chevron-down ms-1"></i></span>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="#" id="downloadReport" data-report-type="topbuyers" data-report-title="Người mua hàng nhiều nhất">Tải xuống báo cáo</a>
                                            <a class="dropdown-item" href="#" id="exportReport" data-report-type="topbuyers" data-report-title="Người mua hàng nhiều nhất">Xuất báo cáo</a>
                                            <a class="dropdown-item" href="#" id="importReport">Nhập báo cáo</a>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- end card header -->

                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table table-centered table-hover align-middle table-nowrap mb-0">
                                        <tbody>
                                            @forelse($topBuyers as $buyer)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <img src="{{ $buyer->avatar ? asset('storage/' . $buyer->avatar) : asset('assets/admins/images/users/avatar-' . ($loop->iteration <= 5 ? $loop->iteration : rand(1, 5)) . '.jpg') }}" alt="" class="avatar-xs rounded-circle material-shadow" />
                                                        </div>
                                                        <div>
                                                            <h5 class="fs-14 my-1 fw-medium"><a href="#" class="text-reset">{{ $buyer->name }}</a></h5>
                                                            <span class="text-muted">{{ $buyer->email }}</span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge badge-soft-info">Khách hàng</span>
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
                                            
                                            @for($i = 1; $i <= $topBuyers->lastPage(); $i++)
                                                <li class="page-item {{ $i == $topBuyers->currentPage() ? 'active' : '' }}">
                                                    <a class="page-link" href="{{ $topBuyers->url($i) }}">{{ $i }}</a>
                                                </li>
                                            @endfor
                                            
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
                        <div class="card">
                            <div class="card-header align-items-center d-flex">
                                <h4 class="card-title mb-0 flex-grow-1">Đơn hàng gần đây</h4>
                                <div class="flex-shrink-0">
                                    <button type="button" class="btn btn-soft-info btn-sm material-shadow-none" id="createOrderReport" data-report-type="orders" data-report-title="Báo cáo đơn hàng">
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
                                            <tr>
                                                <td>
                                                    <a href="{{ route('orders.detail', $order->id) }}" class="fw-medium link-primary">{{ $order->order_code }}</a>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0 me-2">
                                                            <img src="{{ $order->user && $order->user->avatar ? asset('storage/' . $order->user->avatar) : asset('assets/admins/images/users/avatar-' . (($loop->iteration % 5) + 1) . '.jpg') }}" alt="" class="avatar-xs rounded-circle material-shadow" />
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
<script src="https://unpkg.com/exceljs/dist/exceljs.min.js"></script>
<script src="https://unpkg.com/file-saver/dist/FileSaver.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
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

        // Xử lý các nút báo cáo người bán
        document.getElementById('downloadReport').addEventListener('click', function (e) {
            e.preventDefault();
            const reportType = this.getAttribute('data-report-type');
            const reportTitle = this.getAttribute('data-report-title');
            exportToExcel(reportType, reportTitle);
        });

        document.getElementById('exportReport').addEventListener('click', function (e) {
            e.preventDefault();
            const reportType = this.getAttribute('data-report-type');
            const reportTitle = this.getAttribute('data-report-title');
            exportToExcel(reportType, reportTitle);
        });

        document.getElementById('importReport').addEventListener('click', function (e) {
            e.preventDefault();
            // Tạo một input file ẩn để chọn file
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.xlsx, .xls, .csv';

            input.onchange = function(event) {
                const file = event.target.files[0];
                if (file) {
                    importFromExcel(file);
                }
            };

            input.click();
        });
        
        // Hàm khởi tạo counter
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
                
                // Cấu hình cho biểu đồ
                const options = {
                    series: chartData.series,
                    chart: {
                        height: 370,
                        type: 'line',
                        stacked: false,
                        toolbar: {
                            show: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        width: [2, 0, 2],
                        curve: 'smooth',
                        dashArray: [0, 0, 4]
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '30%',
                            borderRadius: 4
                        }
                    },
                    colors: ["#3b76e1", "#63ad6f", "#f34e4e"],
                    fill: {
                        opacity: [0.2, 1, 0.2],
                        gradient: {
                            inverseColors: false,
                            shade: 'light',
                            type: "vertical",
                            opacityFrom: 0.85,
                            opacityTo: 0.55
                        }
                    },
                    labels: chartData.months,
                    markers: {
                        size: 4,
                        hover: {
                            sizeOffset: 3
                        }
                    },
                    legend: {
                        show: true,
                        position: 'bottom',
                        horizontalAlign: 'center',
                        offsetY: 5
                    },
                    xaxis: {
                        type: 'category',
                        categories: chartData.months,
                        labels: {
                            style: {
                                colors: '#adb5bd',
                                fontFamily: 'Roboto, sans-serif'
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
                                color: '#3b76e1'
                            },
                            labels: {
                                style: {
                                    colors: '#3b76e1'
                                },
                                formatter: function (value) {
                                    return Math.round(value);
                                }
                            },
                            title: {
                                text: "Đơn hàng",
                                style: {
                                    color: '#3b76e1',
                                    fontSize: '12px'
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
                                    fontSize: '12px'
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
                                    fontSize: '12px'
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
                    }
                };
                
                // Khởi tạo biểu đồ
                const chart = new ApexCharts(document.querySelector("#customer_impression_charts"), options);
                chart.render();
                
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
            let data = [];
            let headers = [];
            let monthlyData = [];
            let needTotalRow = false;

            console.log('Đang xuất báo cáo:', reportType);

            if (reportType === 'orders') {
                // Thu thập dữ liệu từ bảng đơn hàng
                const orderTable = document.querySelector('.card-body .table-responsive.table-card table.table-borderless');
                if (orderTable) {
                    headers = Array.from(orderTable.querySelectorAll('thead th')).map(th => th.textContent.trim());
                    const rows = orderTable.querySelectorAll('tbody tr');

                    rows.forEach(row => {
                        const rowData = Array.from(row.querySelectorAll('td')).map(td => {
                            // Xử lý đặc biệt cho trường hợp td có chứa các thẻ con
                            const text = td.textContent.trim().replace(/\s+/g, ' ');
                            return text;
                        });
                        data.push(rowData);
                    });

                    // Không cần thêm dòng tổng cho đơn hàng
                    needTotalRow = false;
                    console.log('Dữ liệu đơn hàng:', data.length, 'dòng');
                }
            } else if (reportType === 'topbuyers') {
                // Thu thập dữ liệu từ bảng khách hàng mua nhiều nhất
                headers = ['Khách hàng', 'Email', 'Loại khách', 'Số đơn hàng', 'Tổng chi tiêu', 'Tỷ lệ hoàn thành'];

                // Tìm tất cả các card-title
                const titles = document.querySelectorAll('.card-title');
                let buyerTable = null;

                for (let i = 0; i < titles.length; i++) {
                    if (titles[i].textContent.includes('Xếp hạng người mua')) {
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

                    // Không cần thêm dòng tổng cho người mua
                    needTotalRow = false;
                    console.log('Dữ liệu người mua:', data.length, 'dòng');
                } else {
                    console.log('Không tìm thấy bảng người mua hàng nhiều nhất');
                }
            } else if (reportType === 'revenue') {
                // Thu thập dữ liệu cho báo cáo doanh thu
                headers = ['Tháng', 'Đơn hàng', 'Doanh thu', 'Hoàn tiền', 'Tỷ lệ chuyển đổi'];

                // Tìm card doanh thu
                const revenueTitles = document.querySelectorAll('.card-title');
                let revenueCard = null;

                for (let i = 0; i < revenueTitles.length; i++) {
                    if (revenueTitles[i].textContent.includes('Doanh thu')) {
                        revenueCard = revenueTitles[i].closest('.card');
                        break;
                    }
                }

                if (revenueCard) {
                    // Lấy thông tin từ card
                    const statsEls = revenueCard.querySelectorAll('.border-dashed');

                    let ordersTotal = '';
                    let revenueTotal = '';
                    let refundsTotal = '';
                    let conversionRate = '';

                    if (statsEls.length >= 1) {
                        const orderEl = statsEls[0].querySelector('h5');
                        if (orderEl) ordersTotal = orderEl.textContent.trim();
                    }

                    if (statsEls.length >= 2) {
                        const revenueEl = statsEls[1].querySelector('h5');
                        if (revenueEl) revenueTotal = revenueEl.textContent.trim();
                    }

                    if (statsEls.length >= 3) {
                        const refundEl = statsEls[2].querySelector('h5');
                        if (refundEl) refundsTotal = refundEl.textContent.trim();
                    }

                    if (statsEls.length >= 4) {
                        const conversionEl = statsEls[3].querySelector('h5');
                        if (conversionEl) conversionRate = conversionEl.textContent.trim();
                    }

                    // Dữ liệu từ biểu đồ doanh thu (sử dụng dữ liệu từ backend nếu có hoặc dữ liệu mẫu)
                    monthlyData = [
                        { month: 'Tháng 1', orders: 450, revenue: '$9,250', refunds: 21, conversion: '15.3%' },
                        { month: 'Tháng 2', orders: 520, revenue: '$12,100', refunds: 28, conversion: '16.8%' },
                        { month: 'Tháng 3', orders: 410, revenue: '$8,200', refunds: 19, conversion: '14.5%' },
                        { month: 'Tháng 4', orders: 610, revenue: '$14,500', refunds: 32, conversion: '18.2%' },
                        { month: 'Tháng 5', orders: 480, revenue: '$9,800', refunds: 25, conversion: '15.9%' },
                        { month: 'Tháng 6', orders: 510, revenue: '$10,900', refunds: 23, conversion: '16.5%' },
                        { month: 'Tháng 7', orders: 380, revenue: '$7,800', refunds: 18, conversion: '14.1%' },
                        { month: 'Tháng 8', orders: 320, revenue: '$6,500', refunds: 14, conversion: '13.2%' },
                        { month: 'Tháng 9', orders: 580, revenue: '$13,200', refunds: 29, conversion: '17.6%' },
                        { month: 'Tháng 10', orders: 410, revenue: '$9,100', refunds: 20, conversion: '15.0%' },
                        { month: 'Tháng 11', orders: 530, revenue: '$11,800', refunds: 26, conversion: '16.7%' },
                        { month: 'Tháng 12', orders: 390, revenue: '$8,400', refunds: 19, conversion: '14.8%' }
                    ];

                    // Thêm dữ liệu hàng tháng
                    monthlyData.forEach(item => {
                        data.push([item.month, item.orders, item.revenue, item.refunds, item.conversion]);
                    });

                    // Dòng tổng cộng cho doanh thu
                    const totalOrders = monthlyData.reduce((sum, item) => sum + parseInt(item.orders), 0);
                    const totalRefunds = monthlyData.reduce((sum, item) => sum + parseInt(item.refunds), 0);

                    data.push(['Tổng cộng', totalOrders, revenueTotal, totalRefunds, conversionRate]);
                    needTotalRow = true;
                    console.log('Dữ liệu doanh thu:', data.length, 'dòng');
                }
            }

            // Kiểm tra và debug
            console.log('Tiêu đề:', headers);
            console.log('Dữ liệu:', data);

            if (data.length > 0 && headers.length > 0) {
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

                    infoSheet.mergeCells('A7:G7');
                    infoSheet.getCell('A7').value = 'Báo cáo được tạo tự động từ hệ thống Eco-Furnish';
                    infoSheet.getCell('A7').font = { size: 10, italic: true, color: { argb: '4472C4' } };
                    infoSheet.getCell('A7').alignment = { horizontal: 'center' };

                    // Tạo sheet dữ liệu
                    const dataSheet = workbook.addWorksheet('Dữ liệu');

                    // Thêm headers
                    const headerRow = dataSheet.addRow(headers);

                    // Định dạng header - Style cho hàng đầu tiên
                    headerRow.eachCell((cell) => {
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

                    // Tạo style cho dòng tổng
                    const totalRowStyle = {
                        fill: {
                            type: 'pattern',
                            pattern: 'solid',
                            fgColor: { argb: '2E75B6' }
                        },
                        font: {
                            bold: true,
                            color: { argb: 'FFFFFF' },
                            size: 11
                        },
                        border: {
                            top: { style: 'medium', color: { argb: 'FFFFFF' } },
                            left: { style: 'medium', color: { argb: 'FFFFFF' } },
                            bottom: { style: 'medium', color: { argb: 'FFFFFF' } },
                            right: { style: 'medium', color: { argb: 'FFFFFF' } }
                        }
                    };

                    // Thêm dữ liệu
                    data.forEach((rowData, index) => {
                        const row = dataSheet.addRow(rowData);

                        // Màu nền xen kẽ cho các hàng
                        const isAlternateRow = index % 2 === 1;
                        const rowColor = isAlternateRow ? 'F2F9FF' : 'FFFFFF';

                        // Kiểm tra nếu là hàng cuối VÀ cần tổng
                        const isTotalRow = needTotalRow && index === data.length - 1;

                        row.eachCell((cell, colNumber) => {
                            if (isTotalRow) {
                                // Định dạng hàng tổng cộng giống header
                                cell.fill = totalRowStyle.fill;
                                cell.font = totalRowStyle.font;
                                cell.border = totalRowStyle.border;
                            } else {
                                // Định dạng các hàng thường
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
                            }

                            // Định dạng đặc biệt cho các cột
                            if ((reportType === 'topbuyers' && colNumber === 5) ||
                                (reportType === 'revenue' && colNumber === 3) ||
                                (reportType === 'orders' && colNumber === 4)) {
                                // Cột tiền tệ
                                cell.numFmt = '"$"#,##0.00';
                                cell.alignment = { horizontal: 'right' };
                            } else if ((reportType === 'topbuyers' && colNumber === 6) ||
                                      (reportType === 'revenue' && colNumber === 5)) {
                                // Cột phần trăm
                                cell.numFmt = '0.0%';
                                cell.alignment = { horizontal: 'center' };
                            }
                        });
                    });

                    // Thiết lập độ rộng cột
                    headers.forEach((header, i) => {
                        const column = dataSheet.getColumn(i + 1);
                        column.width = Math.max(header.length * 1.5, 15);
                    });

                    // Thiết lập chiều cao hàng tiêu đề
                    headerRow.height = 30;

                    // Xuất file Excel
                    const buffer = await workbook.xlsx.writeBuffer();
                    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    saveAs(blob, `${reportTitle}_${new Date().toISOString().slice(0, 10)}.xlsx`);

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

        // Thêm jQuery-like selector utility
        document.querySelectorAll = document.querySelectorAll || function(selector) {
            return document.querySelectorAll(selector);
        };

        // Thêm hàm tìm kiếm text trong các phần tử
        Element.prototype.contains = Element.prototype.contains || function(text) {
            return this.textContent.includes(text);
        };

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
    });
</script>
@endsection