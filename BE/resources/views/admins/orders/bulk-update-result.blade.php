@extends('layouts.admin')

@section('title')
    Kết quả cập nhật trạng thái đơn hàng
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="card shadow-sm mb-4 border-0 rounded-lg overflow-hidden">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-primary mb-0">Cập nhật thành công!</h4>
                <p class="text-muted mb-0">Kết quả cập nhật trạng thái hàng loạt</p>
            </div>
            <div>
                <a href="{{ route('orders.index') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách đơn hàng
                </a>
            </div>
        </div>
    </div>

    <!-- Thống kê kết quả -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="avatar-sm mx-auto mb-3 rounded-circle bg-soft-success">
                        <i class="fas fa-check-circle fa-2x text-success mt-2"></i>
                    </div>
                    <h5 class="fw-bold text-success mb-1">{{ $successCount }}</h5>
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
                    <h5 class="fw-bold text-danger mb-1">{{ $errorCount }}</h5>
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
                    <h5 class="fw-bold text-primary mb-1">{{ $totalCount }}</h5>
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
                        <i class="fas fa-list me-1"></i> Tất cả <span class="badge bg-primary rounded-pill ms-1">{{ $totalCount }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="success-tab" data-bs-toggle="tab" data-bs-target="#success-content" type="button" role="tab" aria-controls="success-content" aria-selected="false">
                        <i class="fas fa-check-circle me-1"></i> Thành công <span class="badge bg-success rounded-pill ms-1">{{ $successCount }}</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="error-tab" data-bs-toggle="tab" data-bs-target="#error-content" type="button" role="tab" aria-controls="error-content" aria-selected="false">
                        <i class="fas fa-times-circle me-1"></i> Lỗi <span class="badge bg-danger rounded-pill ms-1">{{ $errorCount }}</span>
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
                            <tbody>
                                @foreach($results as $orderId => $result)
                                    <tr>
                                        <th scope="row">{{ $loop->iteration }}</th>
                                        <td>
                                            @if($result['order'])
                                                <a href="{{ route('orders.show', $result['order']->id) }}" class="fw-medium">
                                                    {{ $result['order']->order_code }}
                                                </a>
                                            @else
                                                <span class="text-muted">Không tìm thấy</span>
                                            @endif
                                        </td>
                                        <td>{{ $result['order']->user_name ?? 'N/A' }}</td>
                                        <td>
                                            @if(isset($result['order']))
                                                @if(strpos($result['message'], "Đã chuyển từ") === 0)
                                                    {!! getOrderStatusBadge(explode("'", $result['message'])[1]) !!}
                                                @else
                                                    {!! getOrderStatusBadge($result['order']->order_status) !!}
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($result['order']))
                                                @if(strpos($result['message'], "Đã chuyển từ") === 0)
                                                    {!! getOrderStatusBadge(explode("'", $result['message'])[3]) !!}
                                                @else
                                                    <span class="badge bg-secondary">Không đổi</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($result['success'])
                                                <span class="badge bg-success rounded-pill">Thành công</span>
                                            @else
                                                <span class="badge bg-danger rounded-pill">Lỗi</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="{{ $result['success'] ? 'text-success' : 'text-danger' }}">
                                                {{ $result['message'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
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
                            <tbody>
                                @php $count = 1; @endphp
                                @foreach($results as $orderId => $result)
                                    @if($result['success'])
                                        <tr>
                                            <th scope="row">{{ $count++ }}</th>
                                            <td>
                                                <a href="{{ route('orders.show', $result['order']->id) }}" class="fw-medium">
                                                    {{ $result['order']->order_code }}
                                                </a>
                                            </td>
                                            <td>{{ $result['order']->user_name }}</td>
                                            <td>{!! getOrderStatusBadge(explode("'", $result['message'])[1]) !!}</td>
                                            <td>{!! getOrderStatusBadge(explode("'", $result['message'])[3]) !!}</td>
                                            <td>
                                                <span class="text-success">{{ $result['message'] }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
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
                            <tbody>
                                @php $count = 1; @endphp
                                @foreach($results as $orderId => $result)
                                    @if(!$result['success'])
                                        <tr>
                                            <th scope="row">{{ $count++ }}</th>
                                            <td>
                                                @if($result['order'])
                                                    <a href="{{ route('orders.show', $result['order']->id) }}" class="fw-medium">
                                                        {{ $result['order']->order_code }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Không tìm thấy</span>
                                                @endif
                                            </td>
                                            <td>{{ $result['order']->user_name ?? 'N/A' }}</td>
                                            <td>
                                                @if(isset($result['order']))
                                                    {!! getOrderStatusBadge($result['order']->order_status) !!}
                                                @else
                                                    <span class="badge bg-secondary">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-danger">{{ $result['message'] }}</span>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hiệu ứng khi chuyển tab kết quả
        const tabLinks = document.querySelectorAll('.nav-link');
        
        tabLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Remove active class from all tabs
                tabLinks.forEach(tab => {
                    tab.classList.remove('active');
                });
                
                // Add active class to the clicked tab
                this.classList.add('active');
            });
        });
    });
</script>
@endpush 