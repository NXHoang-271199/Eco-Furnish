@extends('layouts.admin')

@section('title')
    Quản lý đơn hàng
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Quản lý đơn hàng</h4>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <div class="card mb-3">
            <form action="{{ route('orders.index') }}" method="GET" class="m-3">
                <div class="row">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control"
                            placeholder="Tìm kiếm theo mã đơn hàng hoặc tên người nhận"
                            value="{{ request()->input('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card d-flex ">
            <ul class="nav nav-pills m-3 d-flex flex-wrap justify-content-start gap-2" id="pills-tab" role="tablist">
                @foreach ($groupedOrders as $status => $statusOrders)
                    @php $slug = Str::slug($status) @endphp
                    <li class="nav-item" role="presentation">
                        <button style="background:#9df99d; padding: 10px 15px; border-radius: 8px; min-width: 150px;"
                            class="btn fs-6 fw-bold text-dark {{ $loop->first ? 'active' : '' }} btn fs-6 fw-bold text-dark"
                            id="pills-{{ Str::slug($slug) }}-tab" data-bs-toggle="pill"
                            data-bs-target="#pills-{{ Str::slug($slug) }}" type="button" role="tab"
                            aria-controls="pills-{{ Str::slug($slug) }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                            {{ $status }}
                            <span class="text-danger fw-normal">({{ $statusOrders->total() }})</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Tabs Content -->
        <div class="tab-content" id="pills-tabContent">
            @foreach ($groupedOrders as $status => $orders)
                @php $slug = Str::slug($status) @endphp
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pills-{{ $slug }}"
                    role="tabpanel" aria-labelledby="pills-{{ $slug }}-tab">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn hàng</th>
                                            <th>Người nhận</th>
                                            <th>Tổng tiền</th>
                                            <th>Ngày đặt</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($orders as $order)
                                            <tr>
                                                <td>{{ $order->id }}</td>
                                                <td>{{ $order->receiver_name }}</td>
                                                <td>{{ number_format($order->total_amount) }} đ</td>
                                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <span class="badge 
                                                        @if($order->status == 'Đang xử lý') bg-warning
                                                        @elseif($order->status == 'Đã xác nhận') bg-info
                                                        @elseif($order->status == 'Đang giao hàng') bg-primary
                                                        @elseif($order->status == 'Đã giao hàng') bg-success
                                                        @elseif($order->status == 'Đã hủy') bg-danger
                                                        @endif">
                                                        {{ $order->status }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="hstack gap-3 fs-15">
                                                        <a href="{{ route('orders.detail', $order->id) }}" class="link-primary">
                                                            <i class="ri-eye-line"></i>
                                                        </a>
                                                        
                                                        @can('update-orders')
                                                        @if($order->status != 'Đã giao hàng' && $order->status != 'Đã hủy')
                                                        <div class="dropdown">
                                                            <a class="link-warning dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="ri-edit-line"></i>
                                                            </a>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="status" value="Đã xác nhận">
                                                                        <button type="submit" class="dropdown-item">Xác nhận</button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="status" value="Đang giao hàng">
                                                                        <button type="submit" class="dropdown-item">Đang giao hàng</button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="status" value="Đã giao hàng">
                                                                        <button type="submit" class="dropdown-item">Đã giao hàng</button>
                                                                    </form>
                                                                </li>
                                                                <li>
                                                                    <form action="{{ route('orders.update-status', $order->id) }}" method="POST">
                                                                        @csrf
                                                                        @method('PUT')
                                                                        <input type="hidden" name="status" value="Đã hủy">
                                                                        <button type="submit" class="dropdown-item">Hủy đơn</button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                        @endif
                                                        @endcan
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Không có đơn hàng nào</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                {{ $orders->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
