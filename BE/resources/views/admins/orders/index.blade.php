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
                        <button type="submit" form="bulkUpdateForm" class="btn btn-primary">Cập nhật</button>
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
                                                            {!! getOrderStatusBadge($order->order_status) !!}
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
    // Đảm bảo DOM đã sẵn sàng
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM đã sẵn sàng, bắt đầu khởi tạo JavaScript');
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
</script>
@endsection
