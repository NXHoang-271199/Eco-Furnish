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

        /* Đảm bảo các phần tử trong trang không che khuất dropdown thông báo */
        .card, .container-fluid, .tab-content, .tab-pane {
            z-index: 1;
            position: relative;
        }
        
        /* Đảm bảo dropdown thông báo từ header luôn hiển thị trên cùng */
        .dropdown-menu.show {
            z-index: 9999 !important;
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

        /* Style cho popup kết quả */
        #resultModal .modal-dialog {
            max-width: 650px;
            /* Tăng chiều rộng modal */
        }

        #resultModal .list-group-item {
            border: none;
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #f1f1f1;
        }

        #resultModal .list-group-item:last-child {
            border-bottom: none;
        }

        #resultModal .list-group-item span:first-child {
            font-weight: 500;
            margin-right: 10px;
            white-space: nowrap;
        }

        #resultModal .list-group-item span.badge {
            font-size: 0.8em;
            padding: 0.3em 0.6em;
        }

        #resultModal .list-group-item .message {
            font-size: 0.85rem;
            color: #6c757d;
            text-align: right;
            flex-grow: 1;
            margin-left: 10px;
            white-space: normal;
        }

        #resultModal .list-group-item .message.text-danger {
            color: #dc3545;
        }

        #resultModal .nav-tabs .nav-link {
            border-radius: 0;
            padding: 0.75rem 1rem;
            border: none;
            border-bottom: 2px solid transparent;
            color: #495057;
        }

        #resultModal .nav-tabs .nav-link.active {
            font-weight: 600;
            background-color: transparent;
            border-bottom: 2px solid #0d6efd;
            color: #0d6efd;
        }

        #resultModal .card {
            border-radius: 6px;
            overflow: hidden;
        }

        #resultModal .card-header-tabs {
            margin: 0;
        }

        #resultModal .modal-footer {
            padding: 1rem;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        /* Animation cho icon thành công */
        @keyframes success-icon-animation {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }

            40% {
                transform: scale(1.2);
                opacity: 1;
            }

            60% {
                transform: scale(0.9);
                opacity: 1;
            }

            80% {
                transform: scale(1.1);
                opacity: 1;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-icon-animated .avatar-title {
            animation: success-icon-animation 0.7s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        .success-icon-animated .avatar-title i {
            color: #0ab39c;
            font-size: 2.5rem;
            text-shadow: 0 0 10px rgba(10, 179, 156, 0.3);
            transition: all 0.3s ease;
        }

        .success-icon-animated .avatar-title {
            background: rgba(10, 179, 156, 0.1) !important;
            border: 2px solid rgba(10, 179, 156, 0.2);
            box-shadow: 0 0 15px rgba(10, 179, 156, 0.2);
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
                        Tổng đơn: {{ $groupedOrders['Tất cả']->total() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Search Form with Modern Design -->
        <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <form action="{{ route('orders.index') }}" method="GET" id="searchForm">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" name="search" id="searchInput"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Tìm kiếm theo mã đơn, tên người nhận hoặc tên sản phẩm"
                                    value="{{ request()->input('search') }}" autocomplete="off">
                                <span class="input-group-text bg-white border-start-0 d-none" id="searchSpinner">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="visually-hidden">Đang tìm kiếm...</span>
                                    </div>
                                </span>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4 text-end">
                        <button id="btnBulkUpdateStatus" class="btn btn-success fw-medium" data-bs-toggle="modal"
                            data-bs-target="#bulkUpdateModal" data-action="update-bulk-status" disabled>
                            <i class="fas fa-tasks me-1"></i> Cập nhật trạng thái hàng loạt <span id="selected-count-badge"
                                class="badge bg-light text-dark ms-1 d-none">0</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal cập nhật trạng thái hàng loạt -->
        <div class="modal fade" id="bulkUpdateModal" tabindex="-1" aria-labelledby="bulkUpdateModalLabel"
            aria-hidden="true">
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
                                <i class="fas fa-info-circle me-2"></i> Đã chọn <span id="selectedOrderCount">0</span> đơn
                                hàng
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
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title" id="resultModalLabel">Cập nhật nhanh</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Icon lớn -->
                        <div class="text-center mb-4" id="resultIconContainer">
                            <div class="avatar-lg mx-auto">
                                <div class="avatar-title bg-light text-success display-5 rounded-circle">
                                    <i class="ri-check-double-line"></i>
                                </div>
                            </div>
                            <p class="text-muted mt-2" id="resultIconText">Cập nhật thành công!</p>
                        </div>

                        <!-- Danh sách kết quả -->
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white p-0">
                                <ul class="nav nav-tabs nav-justified card-header-tabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="all-tab-result" data-bs-toggle="tab"
                                            data-bs-target="#all-content-result" type="button" role="tab"
                                            aria-controls="all-content-result" aria-selected="true">
                                            Tất cả <span class="badge bg-primary rounded-pill ms-1"
                                                id="all-count">0</span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="success-tab-result" data-bs-toggle="tab"
                                            data-bs-target="#success-content-result" type="button" role="tab"
                                            aria-controls="success-content-result" aria-selected="false">
                                            Thành công <span class="badge bg-success rounded-pill ms-1"
                                                id="success-count">0</span>
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="error-tab-result" data-bs-toggle="tab"
                                            data-bs-target="#error-content-result" type="button" role="tab"
                                            aria-controls="error-content-result" aria-selected="false">
                                            Lỗi <span class="badge bg-danger rounded-pill ms-1" id="error-count">0</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-2">
                                <div class="tab-content">
                                    <!-- Tab Tất cả -->
                                    <div class="tab-pane fade show active" id="all-content-result" role="tabpanel"
                                        aria-labelledby="all-tab-result">
                                        <ul class="list-group list-group-flush overflow-auto" style="max-height: 300px;"
                                            id="all-results">
                                            <!-- Dữ liệu sẽ được thêm bằng JavaScript -->
                                        </ul>
                                    </div>

                                    <!-- Tab Thành công -->
                                    <div class="tab-pane fade" id="success-content-result" role="tabpanel"
                                        aria-labelledby="success-tab-result">
                                        <ul class="list-group list-group-flush overflow-auto" style="max-height: 300px;"
                                            id="success-results">
                                            <!-- Dữ liệu sẽ được thêm bằng JavaScript -->
                                        </ul>
                                    </div>

                                    <!-- Tab Lỗi -->
                                    <div class="tab-pane fade" id="error-content-result" role="tabpanel"
                                        aria-labelledby="error-tab-result">
                                        <ul class="list-group list-group-flush overflow-auto" style="max-height: 300px;"
                                            id="error-results">
                                            <!-- Dữ liệu sẽ được thêm bằng JavaScript -->
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-between align-items-center">
                        <div class="hstack gap-2">
                            <button type="button" id="copySuccessButton" class="btn btn-soft-success btn-sm" disabled>
                                <i class="far fa-copy me-1"></i> Copy mã đơn thành công
                            </button>
                        </div>
                        <div class="hstack gap-2">
                            <button type="button" id="viewErrorButton" class="btn btn-danger btn-sm" disabled>Đơn
                                lỗi</button>
                            <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Quay lại</button>
                        </div>
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
                            <button
                                class="nav-link rounded-0 border-0 py-3 position-relative {{ $loop->first ? 'active' : '' }}"
                                id="pills-{{ Str::slug($slug) }}-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-{{ Str::slug($slug) }}" type="button" role="tab"
                                aria-controls="pills-{{ Str::slug($slug) }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="fs-6 fw-bold">{{ $status }}</span>
                                    <span
                                        class="badge rounded-pill bg-{{ getStatusBadgeColor($status) }} text-dark mt-1">{{ $statusOrders->total() }}</span>
                                </div>
                                @if ($loop->first)
                                    <span class="position-absolute bottom-0 start-0 end-0 height-3 bg-primary"
                                        style="height: 3px"></span>
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
                                            <input class="form-check-input select-all-checkbox cursor-pointer"
                                                type="checkbox" id="selectAll-{{ Str::slug($status) }}"
                                                data-tab="{{ Str::slug($status) }}">
                                            <label class="form-check-label cursor-pointer"
                                                for="selectAll-{{ Str::slug($status) }}">
                                                <span class="fw-medium">Chọn tất cả đơn hàng</span>
                                                <small class="text-muted">(trừ đơn Đã Nhận/ Hoàn Hàng/ Hủy Đơn)</small>
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
                                                <div
                                                    class="col-md-2 d-flex align-items-center justify-content-center bg-light p-3">
                                                    <div class="form-check me-2 cursor-pointer">
                                                        <label class="form-check-label cursor-pointer w-100">
                                                            <input class="form-check-input order-checkbox cursor-pointer"
                                                                type="checkbox" value="{{ $order->id }}"
                                                                data-order-code="{{ $order->order_code }}"
                                                                data-current-status="{{ $order->order_status }}"
                                                                {{ $order->order_status === 'Đã Nhận' || $order->order_status === 'Hủy Đơn' || $order->order_status === 'Hoàn Hàng' ? 'disabled' : '' }}>
                                                        </label>
                                                    </div>
                                                    @if ($order->orderItems->isNotEmpty())
                                                        <div class="position-relative">
                                                            <img src="{{ Storage::url($order->orderItems->first()->image_url) }}"
                                                                class="img-fluid rounded"
                                                                style="max-height: 120px; object-fit: contain;"
                                                                alt="Product">
                                                            @if ($order->orderItems->count() > 1)
                                                                <span
                                                                    class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-primary">
                                                                    +{{ $order->orderItems->count() - 1 }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="d-flex align-items-center justify-content-center bg-light rounded"
                                                            style="width: 120px; height: 120px;">
                                                            <i class="fas fa-shopping-bag fa-3x text-muted"></i>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Order Details Column -->
                                                <div class="col-md-8 p-4">
                                                    <div class="d-flex justify-content-between mb-3">
                                                        <div>
                                                            <h5 class="fw-bold mb-0">Đơn hàng #{{ $order->order_code }}
                                                            </h5>
                                                            @if ($order->orderItems->isNotEmpty())
                                                                <p class="text-muted mb-1">
                                                                    <i class="fas fa-box-open me-1"></i>
                                                                    {{ Str::limit($order->orderItems->first()->product_name, 50) }}
                                                                    @if ($order->orderItems->count() > 1)
                                                                        <small>(và {{ $order->orderItems->count() - 1 }}
                                                                            sản phẩm khác)</small>
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
                                                                <span class="text-muted"><i class="far fa-user me-1"></i>
                                                                    Người nhận:</span>
                                                                <span class="fw-medium">{{ $order->user_name }}</span>
                                                            </p>
                                                            <p class="mb-1">
                                                                <span class="text-muted"><i
                                                                        class="fas fa-money-bill-wave me-1"></i> Thanh
                                                                    toán:</span>
                                                                <span
                                                                    class="fw-medium badge {{ $order->payment_status == 1 ? 'bg-success' : ($order->payment_status == 2 ? 'bg-warning' : 'bg-danger') }}">
                                                                    {{ $order->payment_status == 1 ? 'Đã thanh toán' : ($order->payment_status == 2 ? 'Chờ thanh toán' : 'Chưa thanh toán') }}
                                                                </span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p class="mb-1">
                                                                <span class="text-muted"><i
                                                                        class="far fa-check-circle me-1"></i> Xác
                                                                    nhận:</span>
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
                                                                <span class="text-muted"><i class="fas fa-tags me-1"></i>
                                                                    Tổng tiền:</span>
                                                                <span
                                                                    class="fw-bold text-danger">{{ number_format($order->total_price, 0, ',', '.') }}
                                                                    đ</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @if ($order->order_status === 'Hủy Đơn' && $order->reason)
                                                        <div class="alert alert-warning p-2 mb-0">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                                <div>
                                                                    <p class="fw-bold mb-1">Lý do hủy đơn:</p>
                                                                    <p class="mb-1">
                                                                        {{ $order->reason ?? 'Chưa có lý do' }}</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif


                                                    @if ($order->refundRequest->isNotEmpty())
                                                        <div class="alert alert-warning p-2 mb-0">
                                                            <div class="d-flex align-items-center">
                                                                <i class="fas fa-exclamation-circle me-2"></i>
                                                                <div>
                                                                    <p class="fw-bold mb-1">Yêu cầu hoàn hàng:</p>
                                                                    <p class="mb-1">
                                                                        {{ $order->refundRequest->first()->reason ?? 'Chưa có lý do' }}
                                                                    </p>
                                                                </div>

                                                                @if ($order->refundRequest->first()->status === 'Chờ Duyệt')
                                                                    <div class="ms-auto">
                                                                        <form
                                                                            action="{{ route('order.refund.approve', ['orderId' => $order->id, 'refundRequestId' => $order->refundRequest->first()->id]) }}"
                                                                            method="POST" style="display:inline-block;">
                                                                            @csrf
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-success">
                                                                                <i class="fas fa-check me-1"></i> Duyệt
                                                                            </button>
                                                                        </form>
                                                                        <form
                                                                            action="{{ route('order.refund.reject', ['orderId' => $order->id, 'refundRequestId' => $order->refundRequest->first()->id]) }}"
                                                                            method="POST" style="display:inline-block;">
                                                                            @csrf
                                                                            <button type="submit"
                                                                                class="btn btn-sm btn-danger">
                                                                                <i class="fas fa-times me-1"></i> Từ chối
                                                                            </button>
                                                                        </form>
                                                                    </div>
                                                                @else
                                                                    <span
                                                                        class="ms-auto badge {{ $order->refundRequest->first()->status == 'Đã Duyệt' ? 'bg-success' : 'bg-danger' }}">
                                                                        {{ $order->refundRequest->first()->status }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- Action Column -->
                                                <div
                                                    class="col-md-2 bg-light p-4 d-flex flex-column justify-content-center">
                                                    <div class="mb-3">
                                                        <form action="{{ route('order.updateStatus', $order->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            <input type="hidden" name="current_status"
                                                                value="{{ $order->order_status }}">
                                                            <select name="order_status"
                                                                class="form-select mb-2 status-select border-0 shadow-sm fw-medium"
                                                                style="background-color: #f8f9fa;"
                                                                onchange="this.form.submit()"
                                                                {{ $order->order_status === 'Đã Nhận' || $order->order_status === 'Hủy Đơn' || $order->order_status === 'Hoàn Hàng' ? 'disabled' : '' }}>
                                                                <option value="Chưa Xác Nhận"
                                                                    {{ $order->order_status === 'Chưa Xác Nhận' ? 'selected' : '' }}>
                                                                    Chưa Xác Nhận</option>
                                                                <option value="Đã Xác Nhận"
                                                                    {{ $order->order_status === 'Đã Xác Nhận' ? 'selected' : '' }}>
                                                                    Đã Xác Nhận</option>
                                                                <option value="Đang Chuẩn Bị Hàng"
                                                                    {{ $order->order_status === 'Đang Chuẩn Bị Hàng' ? 'selected' : '' }}>
                                                                    Đang Chuẩn Bị Hàng</option>
                                                                <option value="Đang Giao"
                                                                    {{ $order->order_status === 'Đang Giao' ? 'selected' : '' }}>
                                                                    Đang Giao</option>
                                                                <option value="Đã Giao"
                                                                    {{ $order->order_status === 'Đã Giao' ? 'selected' : '' }}>
                                                                    Đã Giao</option>
                                                                <option value="Đã Nhận"
                                                                    {{ $order->order_status === 'Đã Nhận' ? 'selected' : '' }}>
                                                                    Đã Nhận</option>
                                                                <option value="Hoàn Hàng"
                                                                    {{ $order->order_status === 'Hoàn Hàng' ? 'selected' : '' }}>
                                                                    Hoàn Hàng</option>
                                                                <option value="Hủy Đơn"
                                                                    {{ $order->order_status === 'Hủy Đơn' ? 'selected' : '' }}>
                                                                    Hủy Đơn</option>
                                                            </select>
                                                        </form>
                                                    </div>
                                                    <a href="{{ route('orders.show', $order->id) }}"
                                                        class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
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
                                Hiển thị {{ $orders->firstItem() ?? 0 }} - {{ $orders->lastItem() ?? 0 }} trên tổng số
                                {{ $orders->total() }} đơn hàng
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
                button.addEventListener('shown.bs.tab', function(event) {
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
                tabButton.addEventListener('shown.bs.tab', function() {
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
                                    if (typeof bootstrap !== 'undefined' && typeof bootstrap.Modal !==
                                        'undefined') {
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

                        // Lắng nghe sự kiện khi modal kết quả đóng
                        const resultModalElement = document.getElementById('resultModal');
                        if (resultModalElement) {
                            resultModalElement.addEventListener('hidden.bs.modal', function handler() {
                                console.log('Modal kết quả đã đóng, tải lại trang.');
                                window.location.reload();
                                // Gỡ bỏ listener sau khi chạy để tránh reload nhiều lần
                                resultModalElement.removeEventListener('hidden.bs.modal', handler);
                            }, {
                                once: true
                            }); // { once: true } đảm bảo listener chỉ chạy một lần
                        }
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

                // Cập nhật số lượng trên các tab
                document.getElementById('all-count').textContent = data.totalCount;
                document.getElementById('success-count').textContent = data.successCount;
                document.getElementById('error-count').textContent = data.errorCount;

                // Cập nhật icon và text chính
                const resultIconContainer = document.getElementById('resultIconContainer');
                const resultIconText = document.getElementById('resultIconText');
                const iconDiv = resultIconContainer.querySelector('.avatar-title');

                // Xóa class animation cũ
                resultIconContainer.classList.remove('success-icon-animated');

                if (data.errorCount > 0 && data.successCount === 0) {
                    // Chỉ lỗi
                    iconDiv.className = 'avatar-title bg-light text-danger display-5 rounded-circle';
                    iconDiv.innerHTML = '<i class="ri-close-line"></i>';
                    resultIconText.textContent = 'Cập nhật thất bại!';
                } else if (data.errorCount > 0 && data.successCount > 0) {
                    // Có cả thành công và lỗi
                    iconDiv.className = 'avatar-title bg-light text-warning display-5 rounded-circle';
                    iconDiv.innerHTML = '<i class="ri-error-warning-line"></i>';
                    resultIconText.textContent = 'Cập nhật có lỗi!';
                } else {
                    // Chỉ thành công
                    iconDiv.className = 'avatar-title bg-light text-success display-5 rounded-circle';
                    iconDiv.innerHTML = '<i class="ri-check-line"></i>';
                    resultIconText.textContent = 'Cập nhật thành công!';
                    // Thêm class để kích hoạt animation
                    resultIconContainer.classList.add('success-icon-animated');
                }

                // Hiển thị dữ liệu kết quả
                const allResults = document.getElementById('all-results');
                const successResults = document.getElementById('success-results');
                const errorResults = document.getElementById('error-results');

                // Xóa dữ liệu cũ
                allResults.innerHTML = '';
                successResults.innerHTML = '';
                errorResults.innerHTML = '';

                // Mảng lưu mã đơn thành công
                const successOrderCodes = [];

                // Thêm dữ liệu mới
                for (const [orderId, result] of Object.entries(data.results)) {
                    const order = result.order || {};
                    const orderCode = order.order_code || 'N/A';
                    const message = result.message;

                    // Tạo HTML cho item
                    let listItemHTML = '';
                    if (result.success) {
                        listItemHTML = `
                        <li class="list-group-item list-group-item-success">
                            <span class="fw-bold">#${orderCode}</span>
                            <span class="message">${message}</span>
                        </li>
                    `;
                        successResults.insertAdjacentHTML('beforeend', listItemHTML);
                        successOrderCodes.push(orderCode);
                    } else {
                        listItemHTML = `
                        <li class="list-group-item list-group-item-danger">
                            <span class="fw-bold">#${orderCode}</span>
                            <span class="message text-danger">${message}</span>
                        </li>
                    `;
                        errorResults.insertAdjacentHTML('beforeend', listItemHTML);
                    }

                    // Thêm vào tab "Tất cả"
                    allResults.insertAdjacentHTML('beforeend', listItemHTML);
                }

                // Kích hoạt/vô hiệu hóa nút footer
                const copySuccessButton = document.getElementById('copySuccessButton');
                const viewErrorButton = document.getElementById('viewErrorButton');

                copySuccessButton.disabled = data.successCount === 0;
                viewErrorButton.disabled = data.errorCount === 0;

                // Gắn sự kiện copy
                copySuccessButton.onclick = () => {
                    navigator.clipboard.writeText(successOrderCodes.join('\n'))
                        .then(() => {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Đã sao chép mã đơn hàng thành công',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        })
                        .catch(err => {
                            console.error('Lỗi khi sao chép:', err);
                            Swal.fire('Lỗi', 'Không thể sao chép mã đơn hàng', 'error');
                        });
                };

                // Gắn sự kiện xem đơn lỗi (hiển thị tab lỗi)
                viewErrorButton.onclick = () => {
                    const errorTabButton = document.getElementById('error-tab-result');
                    if (errorTabButton) {
                        const tab = new bootstrap.Tab(errorTabButton);
                        tab.show();
                    }
                };

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

        // Thêm hàm tạo các particle hiệu ứng
        function createSuccessParticles(parentElement) {
            const colors = ['#0ab39c', '#25c9af', '#7ddece'];
            const particleCount = 8;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                const size = Math.random() * 8 + 4;

                particle.style.position = 'absolute';
                particle.style.width = size + 'px';
                particle.style.height = size + 'px';
                particle.style.background = colors[Math.floor(Math.random() * colors.length)];
                particle.style.borderRadius = '50%';
                particle.style.pointerEvents = 'none';
                particle.style.zIndex = '100';

                // Vị trí ban đầu ở trung tâm
                particle.style.left = '50%';
                particle.style.top = '50%';

                // Thêm particle vào phần tử cha
                parentElement.appendChild(particle);

                // Tạo hiệu ứng animation
                const angle = Math.random() * Math.PI * 2;
                const distance = 30 + Math.random() * 20;
                const x = Math.cos(angle) * distance;
                const y = Math.sin(angle) * distance;

                // Áp dụng animation với GSAP nếu có sẵn, nếu không thì dùng CSS
                if (typeof gsap !== 'undefined') {
                    gsap.to(particle, {
                        duration: 0.6 + Math.random() * 0.4,
                        x: x,
                        y: y,
                        opacity: 0,
                        scale: 0,
                        ease: 'power2.out',
                        onComplete: () => {
                            if (particle.parentNode) {
                                particle.parentNode.removeChild(particle);
                            }
                        }
                    });
                } else {
                    // Fallback to CSS animation
                    particle.style.transition = 'all ' + (0.6 + Math.random() * 0.4) + 's ease-out';
                    setTimeout(() => {
                        particle.style.transform = `translate(${x}px, ${y}px) scale(0)`;
                        particle.style.opacity = '0';

                        // Xóa particle sau khi animation kết thúc
                        setTimeout(() => {
                            if (particle.parentNode) {
                                particle.parentNode.removeChild(particle);
                            }
                        }, 1000);
                    }, 10);
                }
            }
        }

        // Hàm debounce để giới hạn số request tìm kiếm
        function debounce(func, timeout = 500) {
            let timer;
            return (...args) => {
                clearTimeout(timer);
                timer = setTimeout(() => {
                    func.apply(this, args);
                }, timeout);
            };
        }

        // Hàm xử lý tìm kiếm realtime
        function processSearch() {
            const searchForm = document.getElementById('searchForm');
            const searchSpinner = document.getElementById('searchSpinner');

            if (searchSpinner) {
                searchSpinner.classList.remove('d-none');
            }

            searchForm.submit();
        }

        // Khởi tạo tìm kiếm realtime
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');

            if (searchInput) {
                // Tạo hàm tìm kiếm với debounce
                const debouncedSearch = debounce(processSearch);

                // Thêm event listener cho input tìm kiếm
                searchInput.addEventListener('input', debouncedSearch);

                // Focus vào ô tìm kiếm nếu có giá trị
                if (searchInput.value.trim()) {
                    searchInput.focus();
                }
            }
        });
    </script>
@endsection
