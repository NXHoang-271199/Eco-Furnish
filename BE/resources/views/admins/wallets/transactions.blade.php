@extends('layouts.admin')

@section('title', 'Lịch sử giao dịch toàn hệ thống')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lịch sử giao dịch web</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <form id="filterForm" method="GET" action="{{ route('wallets.transactions') }}">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <input type="text" class="form-control" name="search"
                                    placeholder="Tìm kiếm theo tên/email/số điện thoại" value="{{ request()->search }}">
                            </div>
                            <div class="col-md-4">
                                <select name="transaction_type" class="form-control">
                                    <option value="">Chọn loại giao dịch</option>
                                    <option value="nap_tien"
                                        {{ request()->transaction_type == 'nap_tien' ? 'selected' : '' }}>Nạp tiền</option>
                                    <option value="hoan_tien"
                                        {{ request()->transaction_type == 'hoan_tien' ? 'selected' : '' }}>Hoàn tiền
                                    </option>
                                    <option value="thanh_toan_don_hang"
                                        {{ request()->transaction_type == 'thanh_toan_don_hang' ? 'selected' : '' }}>Thanh
                                        toán đơn hàng</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <select name="transaction_status" class="form-control">
                                    <option value="">Chọn trạng thái</option>
                                    <option value="cho_thanh_toan"
                                        {{ request()->transaction_status == 'cho_thanh_toan' ? 'selected' : '' }}>Chờ thanh
                                        toán</option>
                                    <option value="thanh_cong"
                                        {{ request()->transaction_status == 'thanh_cong' ? 'selected' : '' }}>Thành công
                                    </option>
                                    <option value="that_bai"
                                        {{ request()->transaction_status == 'that_bai' ? 'selected' : '' }}>Thất bại
                                    </option>
                                    <option value="da_huy"
                                        {{ request()->transaction_status == 'da_huy' ? 'selected' : '' }}>Đã hủy</option>
                                </select>
                            </div>

                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <input type="date" class="form-control" name="start_date"
                                    value="{{ request()->start_date }}">
                            </div>
                            <div class="col-md-4">
                                <input type="date" class="form-control" name="end_date"
                                    value="{{ request()->end_date }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                            </div>
                            <div class="col-md-2">
                                <button type="reset" class="btn btn-danger w-100" onclick="window.location='{{ route('wallets.transactions') }}'">Xóa tìm kiếm</button>
                            </div>
                        </div>
                    </form>
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th>#</th>
                                <th>Khách hàng</th>
                                <th>Loại GD</th>
                                <th>Trạng thái</th>
                                <th>Mã đơn hàng</th>
                                <th>Số tiền</th>
                                <th>Kênh nạp</th>
                                <th>Người cộng tiền</th>
                                <th>Ghi chú</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $key => $transaction)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td>
                                        <a href="{{ route('wallets.show', $transaction->wallet_id) }}" class="fw-semibold">
                                            {{ $transaction->wallet->user->name ?? '[N/A]' }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ getTransactionTypeColor($transaction->type) }}">
                                            {{ getTransactionTypeLabel($transaction->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ getTransactionStatusColor($transaction->status) }}">
                                            {{ getTransactionStatusLabel($transaction->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $transaction->order->oder_code ?? 'N/A' }}</td>
                                    <td class="text-end">{{ number_format($transaction->amount, 0, ',', '.') }} đ</td>
                                    <td>{{ $transaction->paymentMethod->name ?? 'N/A' }}</td>
                                    <td>{{ $transaction->createdBy->name ?? 'N/A' }}</td>
                                    <td>{{ $transaction->description ?? '-' }}</td>
                                    <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $transactions->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
