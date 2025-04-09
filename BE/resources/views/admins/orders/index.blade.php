@extends('layouts.admin')

@section('title')
    Quản lý đơn hàng
@endsection

@section('CSS')
<style>
    .cursor-pointer {
        cursor: pointer;
    }
    
    .form-check-input:not(:disabled) {
        cursor: pointer;
    }
    
    /* Đảm bảo nút cập nhật trạng thái không bị mờ khi đã chọn checkbox */
    #btnBulkUpdateStatus:not(:disabled) {
        opacity: 1 !important;
        pointer-events: auto !important;
        cursor: pointer !important;
    }
    
    /* Làm rõ trạng thái checkbox đã chọn */
    .form-check-input:checked {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
    }
    
    /* Tăng vùng click cho checkbox */
    .form-check-label {
        padding: 8px;
        margin-left: -8px;
        display: inline-block;
    }
    
    /* Hiệu ứng hover cho checkbox */
    .form-check:hover .form-check-input:not(:checked):not(:disabled) {
        border-color: #0d6efd;
    }
    
    /* Style cho "Chọn tất cả" */
    .select-all-checkbox:checked {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
    }
</style>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold text-primary mb-0">Danh Sách Đơn Hàng</h4>
                    <p class="text-muted mb-0">Quản lý và cập nhật trạng thái đơn hàng</p>
                </div>
                <div>
                    <span class="badge bg-info text-white p-2">
                        <i class="fas fa-shopping-cart me-1"></i>
                        Tổng đơn: {{ array_sum(array_map(function($orders) { return $orders->total(); }, $groupedOrders)) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Search Form with Modern Design -->
        <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <form action="{{ route('orders.index') }}" method="GET">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0 ps-0"
                                    placeholder="Tìm kiếm theo mã đơn hàng hoặc tên người nhận"
                                    value="{{ request()->input('search') }}">
                                <button type="submit" class="btn btn-primary px-4">
                                    <span class="d-none d-md-inline-block">Tìm kiếm</span>
                                    <i class="fas fa-search d-inline-block d-md-none"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <button id="btnBulkUpdateStatus" class="btn btn-success fw-medium" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal" data-action="update-bulk-status" disabled>
                            <i class="fas fa-tasks me-1"></i> Cập nhật trạng thái hàng loạt <span id="selected-count-badge" class="badge bg-light text-dark ms-1 d-none">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal cập nhật trạng thái hàng loạt -->
        <div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-labelledby="bulkUpdateModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkUpdateModalLabel">Cập nhật trạng thái hàng loạt</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="bulkUpdateForm" action="{{ route('orders.bulkUpdateStatus') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="bulkOrderStatus" class="form-label">Chọn trạng thái mới</label>
                                <select id="bulkOrderStatus" name="order_status" class="form-select" required>
                                    <option value="">-- Chọn trạng thái --</option>
                                    <option value="Chưa Xác Nhận">Chưa Xác Nhận</option>
                                    <option value="Đã Xác Nhận">Đã Xác Nhận</option>
                                    <option value="Đang Chuẩn Bị Hàng">Đang Chuẩn Bị Hàng</option>
                                    <option value="Đang Giao">Đang Giao</option>
                                    <option value="Đã Giao">Đã Giao</option>
                                    <option value="Đã Nhận">Đã Nhận</option>
                                    <option value="Hoàn Hàng">Hoàn Hàng</option>
                                    <option value="Hủy Đơn">Hủy Đơn</option>
                                </select>
                            </div>
                            <div id="selectedOrdersContainer" class="d-none">
                                <!-- Đây là nơi chứa các input ẩn cho order IDs -->
                            </div>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i> Đã chọn <span id="selectedOrderCount">0</span> đơn hàng
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="button" id="submitBulkUpdate" class="btn btn-primary">Cập nhật</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal hiển thị kết quả cập nhật trạng thái hàng loạt -->
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="resultModalLabel">Cập nhật thành công!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Thống kê kết quả -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="avatar-sm mx-auto mb-3 rounded-circle bg-soft-success">
                                            <i class="fas fa-check-circle fa-2x text-success mt-2"></i>
                                        </div>
                                        <h5 class="fw-bold text-success mb-1" id="successCount">0</h5>
                                        <p class="text-muted mb-0">Thành công</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="avatar-sm mx-auto mb-3 rounded-circle bg-soft-danger">
                                            <i class="fas fa-times-circle fa-2x text-danger mt-2"></i>
                                        </div>
                                        <h5 class="fw-bold text-danger mb-1" id="errorCount">0</h5>
                                        <p class="text-muted mb-0">Lỗi</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="avatar-sm mx-auto mb-3 rounded-circle bg-soft-primary">
                                            <i class="fas fa-tasks fa-2x text-primary mt-2"></i>
                                        </div>
                                        <h5 class="fw-bold text-primary mb-1" id="totalCount">0</h5>
                                        <p class="text-muted mb-0">Tổng đơn hàng</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Danh sách kết quả -->
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white">
                                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-content" type="button" role="tab" aria-controls="all-content" aria-selected="true">
                                            <i class="fas fa-list me-1"></i> Tất cả <span class="badge bg-primary rounded-pill ms-1" id="all-count">0</span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="success-tab" data-bs-toggle="tab" data-bs-target="#success-content" type="button" role="tab" aria-controls="success-content" aria-selected="false">
                                            <i class="fas fa-check-circle me-1"></i> Thành công <span class="badge bg-success rounded-pill ms-1" id="success-count">0</span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="error-tab" data-bs-toggle="tab" data-bs-target="#error-content" type="button" role="tab" aria-controls="error-content" aria-selected="false">
                                            <i class="fas fa-times-circle me-1"></i> Lỗi <span class="badge bg-danger rounded-pill ms-1" id="error-count">0</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content">
                                    <!-- Tab Tất cả -->
                                    <div class="tab-pane fade show active" id="all-content" role="tabpanel" aria-labelledby="all-tab">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col">Mã đơn hàng</th>
                                                        <th scope="col">Người nhận</th>
                                                        <th scope="col">Trạng thái trước</th>
                                                        <th scope="col">Trạng thái mới</th>
                                                        <th scope="col">Kết quả</th>
                                                        <th scope="col">Ghi chú</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="all-results">
                                                    <!-- Dữ liệu sẽ được thêm bằng JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- Tab Thành công -->
                                    <div class="tab-pane fade" id="success-content" role="tabpanel" aria-labelledby="success-tab">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col">Mã đơn hàng</th>
                                                        <th scope="col">Người nhận</th>
                                                        <th scope="col">Trạng thái trước</th>
                                                        <th scope="col">Trạng thái mới</th>
                                                        <th scope="col">Ghi chú</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="success-results">
                                                    <!-- Dữ liệu sẽ được thêm bằng JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    
                                    <!-- Tab Lỗi -->
                                    <div class="tab-pane fade" id="error-content" role="tabpanel" aria-labelledby="error-tab">
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th scope="col">#</th>
                                                        <th scope="col">Mã đơn hàng</th>
                                                        <th scope="col">Người nhận</th>
                                                        <th scope="col">Trạng thái</th>
                                                        <th scope="col">Lỗi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="error-results">
                                                    <!-- Dữ liệu sẽ được thêm bằng JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modern Status Tabs -->
        <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden">
            <div class="card-body p-0">
                <ul class="nav nav-pills nav-fill sticky-top bg-white border-bottom" id="orderStatusTabs" role="tablist">
                @foreach ($groupedOrders as $status => $statusOrders)
                    @php $slug = Str::slug($status) @endphp
                    <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-0 border-0 py-3 position-relative {{ $loop->first ? 'active' : '' }}"
                                id="pills-{{ Str::slug($slug) }}-tab" 
                                data-bs-toggle="pill"
                                data-bs-target="#pills-{{ Str::slug($slug) }}" 
                                type="button" 
                                role="tab"
                            aria-controls="pills-{{ Str::slug($slug) }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fs-6 fw-bold">{{ $status }}</span>
                                    <span class="badge rounded-pill bg-{{ getStatusBadgeColor($status) }} text-dark mt-1">{{ $statusOrders->total() }}</span>
                                </div>
                                @if($loop->first)
                                <span class="position-absolute bottom-0 start-0 end-0 height-3 bg-primary" style="height: 3px"></span>
                                @endif
                        </button>
                    </li>
                @endforeach
            </ul>
            </div>
        </div>

        <!-- Tabs Content with Modern Cards -->
        <div class="tab-content" id="pills-tabContent">
            @foreach ($groupedOrders as $status => $orders)
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pills-{{ Str::slug($status) }}"
                    role="tabpanel" aria-labelledby="pills-{{ Str::slug($status) }}-tab">
                    @if ($orders->count() > 0)
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card shadow-sm border-0 mb-2">
                                    <div class="card-body py-2">
                                        <div class="form-check">
                                            <input class="form-check-input select-all-checkbox cursor-pointer" type="checkbox" 
                                                id="selectAll-{{ Str::slug($status) }}" 
                                                data-tab="{{ Str::slug($status) }}">
                                            <label class="form-check-label cursor-pointer" for="selectAll-{{ Str::slug($status) }}">
                                                <span class="fw-medium">Chọn tất cả đơn hàng</span>
                                                <small class="text-muted">(trừ đơn đã hoàn tất/hủy/hoàn hàng)</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                @forelse ($orders as $order)
                                    <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden order-card">
                                        <div class="card-body p-0">
                                            <div class="row g-0">
                                                <!-- Order Image Column -->
                                                <div class="col-md-2 d-flex align-items-center justify-content-center bg-light p-3">
                                                    <div class="form-check me-2 cursor-pointer">
                                                        <label class="form-check-label cursor-pointer w-100">
                                                            <input class="form-check-input order-checkbox cursor-pointer" type="checkbox" 
                                                                value="{{ $order->id }}" 
                                                                data-order-code="{{ $order->order_code }}"
                                                                data-current-status="{{ $order->order_status }}"
                                                                {{ $order->order_status === 'Đã Nhận' || $order->order_status === 'Hủy Đơn' || $order->order_status === 'Hoàn Hàng' ? 'disabled' : '' }}>
                                                        </label>
                                                    </div>
                                                    @if ($order->orderItems->isNotEmpty())
                                                        <div class="position-relative">
                                                        <img src="{{ Storage::url($order->orderItems->first()->image_url) }}"
                                                                class="img-fluid rounded" style="max-height: 120px; object-fit: contain;" alt="Product">
                                                            @if($order->orderItems->count() > 1)
                                                                <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-primary">
                                                                    +{{ $order->orderItems->count() - 1 }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center bg-light rounded" style="width: 120px; height: 120px;">
                                                            <i class="fas fa-shopping-bag fa-3x text-muted"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Order Details Column -->
                                                <div class="col-md-8 p-4">
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <div>
                                                            <h5 class="fw-bold mb-0">Đơn hàng #{{ $order->order_code }}</h5>
                                                            @if ($order->orderItems->isNotEmpty())
                                                                <p class="text-muted mb-1">
                                                                    <i class="fas fa-box-open me-1"></i>
                                                                    {{ Str::limit($order->orderItems->first()->product_name, 50) }}
                                                                    @if($order->orderItems->count() > 1)
                                                                        <small>(và {{ $order->orderItems->count() - 1 }} sản phẩm khác)</small>
                                                                    @endif
                                                                </p>
                                                            @endif
                                                            <p class="text-muted mb-0">
                                                                <i class="far fa-calendar-alt me-1"></i> 
                                                                Ngày đặt: {{ $order->created_at->format('d/m/Y H:i:s') }}
                                                            </p>
                                                        </div>
                                                        <div>
                                                            <span role="status">{!! getOrderStatusBadge($order->order_status) !!}</span>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <p class="mb-1">
                                                                <span class="text-muted"><i class="far fa-user me-1"></i> Người nhận:</span> 
                                                                <span class="fw-medium">{{ $order->user_name }}</span>
                                                            </p>
                                                            <p class="mb-1">
                                                                <span class="text-muted"><i class="fas fa-money-bill-wave me-1"></i> Thanh toán:</span>
                                                                <span class="fw-medium badge {{ $order->payment_status == 1 ? 'bg-success' : ($order->payment_status == 2 ? 'bg-warning' : 'bg-danger') }}">
                                                                    {{ $order->payment_status == 1 ? 'Đã thanh toán' : ($order->payment_status == 2 ? 'Chờ thanh toán' : 'Chưa thanh toán') }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="mb-1">
                                                                <span class="text-muted"><i class="far fa-check-circle me-1"></i> Xác nhận:</span>
                                                                <span class="fw-medium">
                                                            @if (
                                                                $order->order_status === 'Đã Xác Nhận' ||
                                                                    $order->order_status === 'Đang Chuẩn Bị Hàng' ||
                                                                    $order->order_status === 'Đang Giao' ||
                                                                    $order->order_status === 'Đã Giao' ||
                                                                    $order->order_status === 'Đã Nhận' ||
                                                                    $order->order_status === 'Hoàn Hàng' ||
                                                                    $order->order_status === 'Hủy Đơn' ||
                                                                    $order->payment_status === '2')
                                                                {{ $order->updated_at ? $order->updated_at->format('d/m/Y H:i') : 'Chưa xác nhận' }}
                                                            @else
                                                                        <span class="text-warning">Chưa xác nhận</span>
                                                            @endif
                                                        </span>
                                                    </p>
                                                            <p class="mb-1">
                                                                <span class="text-muted"><i class="fas fa-tags me-1"></i> Tổng tiền:</span>
                                                                <span class="fw-bold text-danger">{{ number_format($order->total_price, 0, ',', '.') }} đ</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    
                                                    @if ($order->refundRequest->isNotEmpty())
                                                        <div class="alert alert-warning p-2 mb-0">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                                <div>
                                                                    <p class="fw-bold mb-1">Yêu cầu hoàn hàng:</p>
                                                                    <p class="mb-1">{{ $order->refundRequest->first()->reason ?? 'Chưa có lý do' }}</p>
                                                                </div>
                                                                
                                                                @if ($order->refundRequest->first()->status === 'Chờ Duyệt')
                                                                    <div class="ms-auto">
                                                                        <form action="{{ route('order.refund.approve', ['orderId' => $order->id, 'refundRequestId' => $order->refundRequest->first()->id]) }}" method="POST" style="display:inline-block;">
                                                                            @csrf
                                                                            <button type="submit" class="btn btn-sm btn-success">
                                                                                <i class="fas fa-check me-1"></i> Duyệt
                                                                            </button>
                                                                        </form>
                                                                        <form action="{{ route('order.refund.reject', ['orderId' => $order->id, 'refundRequestId' => $order->refundRequest->first()->id]) }}" method="POST" style="display:inline-block;">
                                                                            @csrf
                                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                                <i class="fas fa-times me-1"></i> Từ chối
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                @else
                                                                    <span class="ms-auto badge {{ $order->refundRequest->first()->status == 'Đã Duyệt' ? 'bg-success' : 'bg-danger' }}">
                                                                        {{ $order->refundRequest->first()->status }}
                                                        </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <!-- Action Column -->
                                                <div class="col-md-2 bg-light p-4 d-flex flex-column justify-content-center">
                                                    <div class="mb-3">
                                                        <form action="{{ route('order.updateStatus', $order->id) }}" method="POST">
                                                        @csrf
                                                            <input type="hidden" name="current_status" value="{{ $order->order_status }}">
                                                            <select name="order_status" class="form-select mb-2 status-select border-0 shadow-sm fw-medium" style="background-color: #f8f9fa;" onchange="this.form.submit()"
                                                            {{ $order->order_status === 'Đã Nhận' || $order->order_status === 'Hủy Đơn' || $order->order_status === 'Hoàn Hàng' ? 'disabled' : '' }}>
                                                                <option value="Chưa Xác Nhận" {{ $order->order_status === 'Chưa Xác Nhận' ? 'selected' : '' }}>Chưa Xác Nhận</option>
                                                                <option value="Đã Xác Nhận" {{ $order->order_status === 'Đã Xác Nhận' ? 'selected' : '' }}>Đã Xác Nhận</option>
                                                                <option value="Đang Chuẩn Bị Hàng" {{ $order->order_status === 'Đang Chuẩn Bị Hàng' ? 'selected' : '' }}>Đang Chuẩn Bị Hàng</option>
                                                                <option value="Đang Giao" {{ $order->order_status === 'Đang Giao' ? 'selected' : '' }}>Đang Giao</option>
                                                                <option value="Đã Giao" {{ $order->order_status === 'Đã Giao' ? 'selected' : '' }}>Đã Giao</option>
                                                                <option value="Đã Nhận" {{ $order->order_status === 'Đã Nhận' ? 'selected' : '' }}>Đã Nhận</option>
                                                                <option value="Hoàn Hàng" {{ $order->order_status === 'Hoàn Hàng' ? 'selected' : '' }}>Hoàn Hàng</option>
                                                                <option value="Hủy Đơn" {{ $order->order_status === 'Hủy Đơn' ? 'selected' : '' }}>Hủy Đơn</option>
                                                        </select>
                                                    </form>
                                                    </div>
                                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                                                        <i class="fas fa-eye me-2"></i> Xem chi tiết
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="card shadow-sm border-0 text-center p-5">
                                        <div class="py-5">
                                            <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                                            <h5 class="fw-bold">Không có đơn hàng nào.</h5>
                                            <p class="text-muted">Không có đơn hàng nào trong trạng thái này.</p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                        
                        <!-- Pagination with Modern Design -->
                        <div class="d-flex justify-content-between align-items-center my-4">
                            <div class="text-muted small">
                                Hiển thị {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} trên tổng số {{ $orders->total() }} đơn hàng
                            </div>
                            <div>
                            {{ $orders->links('pagination::bootstrap-5') }}
                            </div>
                        </div>
                    @else
                        <div class="card shadow-sm border-0 text-center p-5">
                            <div class="py-5">
                                <i class="fas fa-shopping-cart fa-4x text-muted mb-4"></i>
                                <h5 class="fw-bold">Không có đơn hàng nào.</h5>
                                <p class="text-muted">Không có đơn hàng nào trong trạng thái này.</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection

@section('JS')
<script>
    // Định nghĩa biến toàn cục ở đầu file
    const BASE_URL = window.location.origin;
    let CSRF_TOKEN;
    
    // Đảm bảo DOM đã sẵn sàng
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM đã sẵn sàng, bắt đầu khởi tạo JavaScript');
        
        // Lấy CSRF token từ meta tag
        CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Ghi log để debug
        console.log('BASE_URL:', BASE_URL);
        
        initOrderBulkActions();
    });
    
    // Đảm bảo cả trang đã tải hoàn toàn (bao gồm cả hình ảnh)
    window.addEventListener('load', function() {
        console.log('Trang đã tải hoàn toàn');
        checkAllCheckboxes();
    });
    
    function checkAllCheckboxes() {
        // Kiểm tra lại trạng thái checkbox sau khi trang đã tải hoàn toàn
        const anyChecked = document.querySelector('.order-checkbox:checked');
        if (anyChecked) {
            console.log('Có checkbox đã được chọn sau khi trang tải hoàn toàn:', anyChecked);
            updateSelectedOrders();
        }
    }
    
    function initOrderBulkActions() {
        // Thêm hiệu ứng khi chuyển tab
        const tabButtons = document.querySelectorAll('[data-bs-toggle="pill"]');
        
        tabButtons.forEach(button => {
            button.addEventListener('shown.bs.tab', function (event) {
                // Xóa active indicator cho tất cả các tab
                tabButtons.forEach(btn => {
                    btn.querySelector('.position-absolute')?.remove();
                });
                
                // Thêm active indicator cho tab đang active
                const activeIndicator = document.createElement('span');
                activeIndicator.className = 'position-absolute bottom-0 start-0 end-0 bg-primary';
                activeIndicator.style.height = '3px';
                activeIndicator.style.transition = 'all 0.3s ease';
                event.target.appendChild(activeIndicator);
            });
        });
        
        // Thêm hiệu ứng hover cho card
        const orderCards = document.querySelectorAll('.order-card');
        orderCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 0.125rem 0.25rem rgba(0,0,0,0.075)';
            });
        });
        
        // Thêm hiệu ứng cho select status
        const statusSelects = document.querySelectorAll('.status-select');
        statusSelects.forEach(select => {
            select.addEventListener('change', function() {
                this.classList.add('border-primary');
                setTimeout(() => {
                    this.classList.remove('border-primary');
                }, 1000);
            });
        });

        // Xử lý chọn nhiều checkbox và cập nhật trạng thái hàng loạt
        const bulkUpdateBtn = document.getElementById('btnBulkUpdateStatus');
        const selectedOrderCount = document.getElementById('selectedOrderCount');
        const selectedCountBadge = document.getElementById('selected-count-badge');
        const selectedOrdersContainer = document.getElementById('selectedOrdersContainer');
        const bulkUpdateForm = document.getElementById('bulkUpdateForm');
        
        // Đảm bảo rằng nút bắt đầu với trạng thái vô hiệu hóa
        if (bulkUpdateBtn) {
            bulkUpdateBtn.disabled = true;
            bulkUpdateBtn.classList.add('disabled');
        }
        
        // Sử dụng event delegation thay vì gắn sự kiện trực tiếp vào từng checkbox
        document.addEventListener('click', function(event) {
            if (event.target && event.target.classList.contains('order-checkbox')) {
                // Nếu người dùng click vào checkbox, cập nhật trạng thái
                setTimeout(updateSelectedOrders, 50);
            }
        });
        
        // Cũng xử lý sự kiện change cho những checkbox có thể được chọn bằng cách khác
        document.addEventListener('change', function(event) {
            if (event.target && event.target.classList.contains('order-checkbox')) {
                setTimeout(updateSelectedOrders, 50);
            }
        });
        
        // Xử lý nút "Chọn tất cả"
        document.querySelectorAll('.select-all-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const tabId = this.dataset.tab;
                const isChecked = this.checked;
                
                // Tìm tab hiện tại
                const tabPane = document.getElementById('pills-' + tabId);
                if (!tabPane) return;
                
                // Chọn/bỏ chọn tất cả checkbox trong tab, trừ những đơn đã hoàn tất/hủy/hoàn hàng
                tabPane.querySelectorAll('.order-checkbox:not(:disabled)').forEach(orderCheckbox => {
                    orderCheckbox.checked = isChecked;
                });
                
                // Cập nhật trạng thái
                setTimeout(updateSelectedOrders, 50);
            });
        });
        
        // Thêm sự kiện cho các tab để đảm bảo cập nhật trạng thái nút khi chuyển tab
        document.querySelectorAll('[data-bs-toggle="pill"]').forEach(tabButton => {
            tabButton.addEventListener('shown.bs.tab', function () {
                // Đợi một chút để DOM cập nhật sau khi chuyển tab
                setTimeout(updateSelectedOrders, 300);
            });
        });
        
        // Kiểm tra trạng thái checkbox ngay khi trang đã khởi tạo
        setTimeout(updateSelectedOrders, 500);
    }
    
    // Cập nhật số lượng đơn hàng đã chọn và kích hoạt/vô hiệu hóa nút cập nhật hàng loạt
    function updateSelectedOrders() {
        const bulkUpdateBtn = document.getElementById('btnBulkUpdateStatus');
        const selectedOrderCount = document.getElementById('selectedOrderCount');
        const selectedCountBadge = document.getElementById('selected-count-badge');
        const selectedOrdersContainer = document.getElementById('selectedOrdersContainer');
        
        if (!bulkUpdateBtn || !selectedOrdersContainer) {
            console.error('Không tìm thấy các phần tử cần thiết');
            return;
        }
        
        const selectedCheckboxes = document.querySelectorAll('.order-checkbox:checked');
        const count = selectedCheckboxes.length;
        
        console.log('Số lượng đơn hàng đã chọn:', count); // Debug log
        
        // Cập nhật số lượng đã chọn trong modal
        if (selectedOrderCount) {
            selectedOrderCount.textContent = count;
        }
        
        // Cập nhật badge trên nút
        if (selectedCountBadge) {
            selectedCountBadge.textContent = count;
            if (count > 0) {
                selectedCountBadge.classList.remove('d-none');
            } else {
                selectedCountBadge.classList.add('d-none');
            }
        }
        
        // Kích hoạt/vô hiệu hóa nút cập nhật hàng loạt
        if (count > 0) {
            bulkUpdateBtn.disabled = false;
            bulkUpdateBtn.classList.remove('disabled');
            bulkUpdateBtn.classList.add('btn-success');
            bulkUpdateBtn.classList.remove('btn-secondary');
            console.log('Nút đã được kích hoạt');
        } else {
            bulkUpdateBtn.disabled = true;
            bulkUpdateBtn.classList.add('disabled');
            bulkUpdateBtn.classList.remove('btn-success');
            bulkUpdateBtn.classList.add('btn-secondary');
            console.log('Nút đã bị vô hiệu hóa');
        }
        
        // Xóa tất cả input ẩn cũ
        selectedOrdersContainer.innerHTML = '';
        
        // Thêm input ẩn cho mỗi đơn hàng đã chọn
        selectedCheckboxes.forEach(checkbox => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'order_ids[]';
            input.value = checkbox.value;
            
            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'current_statuses[' + checkbox.value + ']';
            statusInput.value = checkbox.dataset.currentStatus;
            
            selectedOrdersContainer.appendChild(input);
            selectedOrdersContainer.appendChild(statusInput);
        });
    }
    
    // Xử lý submit form cập nhật trạng thái hàng loạt bằng AJAX
    document.getElementById('submitBulkUpdate')?.addEventListener('click', function(e) {
        e.preventDefault();
        
        try {
            const form = document.getElementById('bulkUpdateForm');
            if (!form) {
                throw new Error('Không tìm thấy form cập nhật trạng thái');
            }
            
            const selectedStatus = document.getElementById('bulkOrderStatus')?.value;
            
            if (!selectedStatus) {
                Swal.fire({
                    title: 'Lỗi!',
                    text: 'Vui lòng chọn trạng thái mới.',
                    icon: 'error',
                    confirmButtonText: 'Đóng'
                });
                return;
            }
            
            // Lấy dữ liệu từ form
            const formData = new FormData(form);
            
            // Hiển thị loading
            Swal.fire({
                title: 'Đang xử lý...',
                html: 'Vui lòng đợi trong giây lát...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            console.log('Gửi request đến:', form.action);
            
            // Gửi AJAX request
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': CSRF_TOKEN || ''
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                // Đóng Swal
                Swal.close();
                
                if (!data || !data.success) {
                    Swal.fire({
                        title: 'Lỗi!',
                        text: data?.message || 'Đã xảy ra lỗi khi cập nhật trạng thái.',
                        icon: 'error',
                        confirmButtonText: 'Đóng'
                    });
                    return;
                }
                
                // Cập nhật UI với kết quả
                displayResults(data);
                
                // Ẩn modal cập nhật trạng thái
                try {
                    const bulkUpdateModalEl = document.getElementById('bulkUpdateModal');
                    if (bulkUpdateModalEl) {
                        const bulkUpdateModal = bootstrap.Modal.getInstance(bulkUpdateModalEl);
                        if (bulkUpdateModal) {
                            bulkUpdateModal.hide();
                        } else {
                            // Nếu không lấy được instance, thử tạo mới
                            new bootstrap.Modal(bulkUpdateModalEl).hide();
                        }
                    }
                } catch (modalError) {
                    console.error('Lỗi khi đóng modal:', modalError);
                    // Thử dùng jQuery nếu có
                    if (typeof $ !== 'undefined') {
                        $('#bulkUpdateModal').modal('hide');
                    }
                }
                
                // Hiển thị modal kết quả
                try {
                    const resultModalElement = document.getElementById('resultModal');
                    if (resultModalElement) {
                        let resultModal;
                        try {
                            // Thử lấy instance
                            resultModal = bootstrap.Modal.getInstance(resultModalElement);
                            if (!resultModal) {
                                // Nếu không có, tạo mới
                                resultModal = new bootstrap.Modal(resultModalElement);
                            }
                            resultModal.show();
                        } catch (bootstrapError) {
                            console.error('Lỗi bootstrap khi hiển thị modal:', bootstrapError);
                            // Thử tạo modal theo cách khác
                            if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !== 'undefined') {
                                new bootstrap.Modal(resultModalElement).show();
                            } else if (typeof $ !== 'undefined') {
                                $('#resultModal').modal('show');
                            }
                        }
                    } else {
                        console.error('Không tìm thấy modal kết quả');
                        // Hiển thị kết quả bằng Swal
                        Swal.fire({
                            title: 'Thành công!',
                            text: `Đã cập nhật ${data.successCount} đơn hàng thành công, ${data.errorCount} đơn hàng lỗi.`,
                            icon: 'success',
                            confirmButtonText: 'Đóng'
                        });
                    }
                } catch (modalError) {
                    console.error('Lỗi khi hiển thị modal kết quả:', modalError);
                    // Thông báo thành công dù không hiển thị được modal
                    Swal.fire({
                        title: 'Thành công!',
                        text: `Đã cập nhật ${data.successCount} đơn hàng thành công, ${data.errorCount} đơn hàng lỗi.`,
                        icon: 'success',
                        confirmButtonText: 'Đóng'
                    });
                }
                
                // Cập nhật lại trạng thái đơn hàng trên UI
                updateOrderStatusUI(data.results);
            })
            .catch(error => {
                console.error('Lỗi:', error);
                Swal.fire({
                    title: 'Lỗi!',
                    text: 'Đã xảy ra lỗi khi cập nhật trạng thái: ' + error.message,
                    icon: 'error',
                    confirmButtonText: 'Đóng'
                });
            });
        } catch (error) {
            console.error('Lỗi khi xử lý form:', error);
            Swal.fire({
                title: 'Lỗi!',
                text: 'Đã xảy ra lỗi: ' + error.message,
                icon: 'error',
                confirmButtonText: 'Đóng'
            });
        }
    });
    
    // Hàm hiển thị kết quả cập nhật trong modal
    function displayResults(data) {
        try {
            // Ghi log dữ liệu để debug
            console.log('Kết quả nhận được:', data);
            
            // Đảm bảo biến BASE_URL đã được định nghĩa
            const baseUrl = BASE_URL || window.location.origin;
            console.log('Base URL đang sử dụng:', baseUrl);
            
            // Cập nhật thống kê
            document.getElementById('successCount').textContent = data.successCount;
            document.getElementById('errorCount').textContent = data.errorCount;
            document.getElementById('totalCount').textContent = data.totalCount;
            
            // Cập nhật số lượng trên các tab
            document.getElementById('all-count').textContent = data.totalCount;
            document.getElementById('success-count').textContent = data.successCount;
            document.getElementById('error-count').textContent = data.errorCount;
            
            // Hiển thị dữ liệu kết quả
            const allResults = document.getElementById('all-results');
            const successResults = document.getElementById('success-results');
            const errorResults = document.getElementById('error-results');
            
            // Xóa dữ liệu cũ
            allResults.innerHTML = '';
            successResults.innerHTML = '';
            errorResults.innerHTML = '';
            
            // Thêm dữ liệu mới
            let index = 1;
            let successIndex = 1;
            let errorIndex = 1;
            
            for (const [orderId, result] of Object.entries(data.results)) {
                // Tab Tất cả
                const allRow = document.createElement('tr');
                const order = result.order || {};
                
                if (result.success) {
                    // Đã chuyển từ 'Đã Xác Nhận' sang 'Đang Chuẩn Bị Hàng'
                    const statusParts = result.message.match(/Đã chuyển từ '(.*)' sang '(.*)'/);
                    const oldStatus = statusParts ? statusParts[1] : (order.order_status || 'Không xác định');
                    const newStatus = statusParts ? statusParts[2] : 'Không xác định';
                    
                    // Tạo URL an toàn
                    let orderUrl = '#';
                    if (order && order.id) {
                        orderUrl = `${baseUrl}/admin/orders/${order.id}`;
                    }
                    
                    allRow.innerHTML = `
                        <td>${index}</td>
                        <td><a href="${orderUrl}" class="fw-medium">${order.order_code || 'N/A'}</a></td>
                        <td>${order.user_name || 'N/A'}</td>
                        <td>${getStatusBadgeHTML(oldStatus)}</td>
                        <td>${getStatusBadgeHTML(newStatus)}</td>
                        <td><span class="badge bg-success rounded-pill">Thành công</span></td>
                        <td><span class="text-success">${result.message}</span></td>
                    `;
                    
                    // Tab Thành công
                    const successRow = document.createElement('tr');
                    successRow.innerHTML = `
                        <td>${successIndex}</td>
                        <td><a href="${orderUrl}" class="fw-medium">${order.order_code || 'N/A'}</a></td>
                        <td>${order.user_name || 'N/A'}</td>
                        <td>${getStatusBadgeHTML(oldStatus)}</td>
                        <td>${getStatusBadgeHTML(newStatus)}</td>
                        <td><span class="text-success">${result.message}</span></td>
                    `;
                    
                    successResults.appendChild(successRow);
                    successIndex++;
                } else {
                    // Lỗi
                    // Tạo URL an toàn
                    let orderUrl = '#';
                    if (order && order.id) {
                        orderUrl = `${baseUrl}/admin/orders/${order.id}`;
                    }
                    
                    allRow.innerHTML = `
                        <td>${index}</td>
                        <td>${order && order.order_code ? `<a href="${orderUrl}" class="fw-medium">${order.order_code}</a>` : 'N/A'}</td>
                        <td>${order && order.user_name ? order.user_name : 'N/A'}</td>
                        <td>${order && order.order_status ? getStatusBadgeHTML(order.order_status) : '<span class="badge bg-secondary">N/A</span>'}</td>
                        <td><span class="badge bg-secondary">Không đổi</span></td>
                        <td><span class="badge bg-danger rounded-pill">Lỗi</span></td>
                        <td><span class="text-danger">${result.message}</span></td>
                    `;
                    
                    // Tab Lỗi
                    const errorRow = document.createElement('tr');
                    errorRow.innerHTML = `
                        <td>${errorIndex}</td>
                        <td>${order && order.order_code ? `<a href="${orderUrl}" class="fw-medium">${order.order_code}</a>` : 'N/A'}</td>
                        <td>${order && order.user_name ? order.user_name : 'N/A'}</td>
                        <td>${order && order.order_status ? getStatusBadgeHTML(order.order_status) : '<span class="badge bg-secondary">N/A</span>'}</td>
                        <td><span class="text-danger">${result.message}</span></td>
                    `;
                    
                    errorResults.appendChild(errorRow);
                    errorIndex++;
                }
                
                allResults.appendChild(allRow);
                index++;
            }
        } catch (e) {
            console.error('Lỗi khi hiển thị kết quả:', e);
            Swal.fire({
                title: 'Lỗi!',
                text: 'Đã xảy ra lỗi khi hiển thị kết quả: ' + e.message,
                icon: 'error',
                confirmButtonText: 'Đóng'
            });
        }
    }
    
    // Hàm cập nhật trạng thái đơn hàng trên UI
    function updateOrderStatusUI(results) {
        try {
            if (!results) {
                console.error('Không có kết quả để cập nhật UI');
                return;
            }
            
            for (const [orderId, result] of Object.entries(results)) {
                if (!result.success) continue;
                
                // Tìm các card đơn hàng có ID tương ứng và cập nhật trạng thái
                const orderCheckbox = document.querySelector(`.order-checkbox[value="${orderId}"]`);
                if (orderCheckbox) {
                    const orderCard = orderCheckbox.closest('.order-card');
                    if (orderCard) {
                        // Cập nhật badge trạng thái
                        const statusBadge = orderCard.querySelector('.badge[role="status"]');
                        if (statusBadge) {
                            statusBadge.outerHTML = getStatusBadgeHTML(result.order.new_status);
                        } else {
                            // Nếu không tìm thấy badge bằng role, thử tìm bằng cách khác
                            const allDivs = orderCard.querySelectorAll('div');
                            for (const div of allDivs) {
                                const badges = div.querySelectorAll('.badge');
                                if (badges.length > 0) {
                                    // Tìm được div chứa badge
                                    badges[0].outerHTML = getStatusBadgeHTML(result.order.new_status);
                                    break;
                                }
                            }
                        }
                        
                        // Cập nhật data-current-status cho checkbox
                        orderCheckbox.dataset.currentStatus = result.order.new_status;
                        
                        // Nếu đơn hàng đã chuyển sang Đã Nhận, Hủy Đơn hoặc Hoàn Hàng, vô hiệu hóa checkbox
                        if (['Đã Nhận', 'Hủy Đơn', 'Hoàn Hàng'].includes(result.order.new_status)) {
                            orderCheckbox.checked = false;
                            orderCheckbox.disabled = true;
                        }
                    }
                }
            }
            
            // Cập nhật lại trạng thái nút sau khi thay đổi checkbox
            setTimeout(() => {
                try {
                    updateSelectedOrders();
                } catch (e) {
                    console.error('Lỗi khi gọi updateSelectedOrders:', e);
                }
            }, 500);
            
        } catch (error) {
            console.error('Lỗi khi cập nhật UI:', error);
        }
    }
    
    // Hàm tạo HTML cho badge trạng thái
    function getStatusBadgeHTML(status) {
        let badgeClass = '';
        let icon = '';
        
        switch (status) {
            case 'Chưa Xác Nhận':
                badgeClass = 'bg-warning';
                icon = '<i class="far fa-clock"></i>';
                break;
            case 'Đã Xác Nhận':
                badgeClass = 'bg-info';
                icon = '<i class="fas fa-check-circle"></i>';
                break;
            case 'Đang Chuẩn Bị Hàng':
                badgeClass = 'bg-primary';
                icon = '<i class="fas fa-box"></i>';
                break;
            case 'Đang Giao':
                badgeClass = 'bg-indigo';
                icon = '<i class="fas fa-truck"></i>';
                break;
            case 'Đã Giao':
                badgeClass = 'bg-success';
                icon = '<i class="fas fa-check-double"></i>';
                break;
            case 'Đã Nhận':
                badgeClass = 'bg-success';
                icon = '<i class="fas fa-handshake"></i>';
                break;
            case 'Hoàn Hàng':
                badgeClass = 'bg-danger';
                icon = '<i class="fas fa-undo"></i>';
                break;
            case 'Hủy Đơn':
                badgeClass = 'bg-danger';
                icon = '<i class="fas fa-ban"></i>';
                break;
            default:
                badgeClass = 'bg-secondary';
                icon = '<i class="fas fa-question-circle"></i>';
        }
        
        return `<span class="badge ${badgeClass} py-2 px-3" role="status">${icon} ${status}</span>`;
    }
</script>
@endsection
