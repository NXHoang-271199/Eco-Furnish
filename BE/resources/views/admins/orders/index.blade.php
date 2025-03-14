@extends('layouts.admin')

@section('title')
    Quản lý đơn hàng
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="m-3" style="margin-left: 20px">
                <a href="{{ route('orders.index') }}"><b class="fs-4 fw-bold">Danh Sách Đơn Hàng</b></a>
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
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pills-{{ Str::slug($status) }}"
                    role="tabpanel" aria-labelledby="pills-{{ Str::slug($status) }}-tab">
                    @if ($orders->count() > 0)
                        <div class="card-body">
                            <table class="table table-bordered table-hover">
                                @forelse ($orders as $order)
                                    <div class="card shadow-sm mb-4">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center border-bottom">
                                                @foreach ($order->orderItems as $item)
                                                    <div class="col-md">
                                                        <img src="{{ Storage::url($item->image_url) }}" width="40px"
                                                            height="40px" alt="Product" class="img-fluid rounded">
                                                    </div>
                                                @endforeach
                                                <div class="col-md-9">
                                                    <p class="mb-1 ms-3">
                                                        Ngày xác nhận:
                                                        <span class="text-primary fw-bold">
                                                            @if (
                                                                $order->order_status === 'Đã Xác Nhận' ||
                                                                    $order->order_status === 'Đang Chuẩn Bị Hàng' ||
                                                                    $order->order_status === 'Đang Giao' ||
                                                                    $order->order_status === 'Đã Giao' ||
                                                                    $order->order_status === 'Đã Nhận' ||
                                                                    $order->order_status === 'Thành Công' ||
                                                                    $order->order_status === 'Hoàn Hàng' ||
                                                                    $order->order_status === 'Hủy Đơn' ||
                                                                    $order->payment_status === '2')
                                                                {{ $order->updated_at ? $order->updated_at->format('d/m/Y H:i') : 'Chưa xác nhận' }}
                                                            @else
                                                                Chưa xác nhận
                                                            @endif
                                                        </span>
                                                    </p>
                                                    <p class="mb-1 ms-3">Mã đơn hàng: <span
                                                            class="fw-bold">{{ $order->order_code }}</span></p>
                                                    <p class="mb-1 ms-3 text-muted">Tên người nhận: {{ $order->user_name }}
                                                    </p>
                                                    <p class="mb-1 ms-3 text-danger fw-600"><strong>Tổng tiền:
                                                            {{ number_format($order->total_price, 0, ',', '.') }}
                                                            đ</strong></p>
                                                </div>
                                                <div class="col-md-2 text-end">
                                                    <form action="{{ route('order.updateStatus', $order->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <input type="hidden" name="current_status" value="{{ $order->order_status }}">
                                                        <select name="order_status" class="form-control fw-bold" onchange="this.form.submit()"
                                                                {{ $order->order_status === 'Hủy Đơn' || $order->order_status === 'Hoàn Hàng' ? 'disabled' : '' }}>
                                                            <option value="Chưa Xác Nhận" {{ $order->order_status === 'Chưa Xác Nhận' ? 'selected' : '' }}>Chưa Xác Nhận</option>
                                                            <option value="Đã Xác Nhận" {{ $order->order_status === 'Đã Xác Nhận' ? 'selected' : '' }}>Đã Xác Nhận</option>
                                                            <option value="Đang Chuẩn Bị Hàng" {{ $order->order_status === 'Đang Chuẩn Bị Hàng' ? 'selected' : '' }}>Đang Chuẩn Bị Hàng</option>
                                                            <option value="Đang Giao" {{ $order->order_status === 'Đang Giao' ? 'selected' : '' }}>Đang Giao</option>
                                                            <option value="Đã Giao" {{ $order->order_status === 'Đã Giao' ? 'selected' : '' }}>Đã Giao</option>
                                                            <option value="Đã Nhận" {{ $order->order_status === 'Đã Nhận' ? 'selected' : '' }}>Đã Nhận</option>
                                                            <option value="Thành Công" {{ $order->order_status === 'Thành Công' ? 'selected' : '' }}>Thành Công</option>
                                                            <option value="Hoàn Hàng" {{ $order->order_status === 'Hoàn Hàng' ? 'selected disabled' : '' }}>Hoàn Hàng</option>
                                                            <option value="Hủy Đơn" {{ $order->order_status === 'Hủy Đơn' ? 'selected disabled' : '' }}>Hủy Đơn</option>
                                                        </select>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="mt-3">

                                                <div class="text-end mt-3 d-flex justify-content-between gap-2">
                                                    <p style="font-size:13px;">Ngày đặt hàng:
                                                        {{ $order->created_at->format('d/m/Y H:i:s') }}</p>
                                                    <div class="d-flex gap-2">
                                                        <div><a href="{{ route('orders.show', $order->id) }}"
                                                                class="btn border border-danger btn-sm text-dark">Xem Chi
                                                                Tiết Đơn Hàng</a></div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-center">Không có đơn hàng nào.</p>
                                @endforelse
                            </table>
                        </div>
                        <!-- tab -->
                        <div class="d-flex justify-content-end me-3">
                            {{ $orders->links('pagination::bootstrap-5') }}
                        </div>
                    @else
                        <p class="text-center">Không có đơn hàng trong trạng thái này.</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endsection
