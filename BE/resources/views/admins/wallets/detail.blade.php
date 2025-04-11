@extends('layouts.admin')

@section('title', 'Chi tiết ví tiền')

@section('content')
    <div class="container-fluid">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Thông tin ví</h5>
                <a href="{{ route('wallets.index') }}" class="btn btn-secondary btn-sm">
                    <i class="ri-arrow-left-line"></i> Quay lại danh sách
                </a>
            </div>
            <div class="card-body">
                <p><strong>Chủ ví:</strong> {{ $wallet->user->name }}</p>
                <p><strong>Email:</strong> {{ $wallet->user->email }}</p>
                <p><strong>Số điện thoại:</strong> {{ $wallet->user->phone ?? 'Chưa có' }}</p>
                <p><strong>Số dư:</strong> {{ number_format($wallet->balance, 0, ',', '.') }} đ</p>
                <p><strong>Ngày tạo ví:</strong> {{ $wallet->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Lịch sử giao dịch</h5>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>STT</th>
                            <th>Số tiền</th>
                            <th>Loại giao dịch</th>
                            <th>Trạng thái</th>
                            <th>Mã đơn hàng</th>
                            <th>Kênh nạp</th>
                            <th>Người cộng tiền</th>
                            <th>Mô tả</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $key => $tran)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>{{ number_format($tran->amount, 0, ',', '.') }} đ</td>
                                <td>
                                    <span class="badge bg-{{ getTransactionTypeColor($tran->type) }}">
                                        {{ getTransactionTypeLabel($tran->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ getTransactionStatusColor($tran->status) }}">
                                        {{ getTransactionStatusLabel($tran->status) }}
                                    </span>
                                </td>
                                <td>{{ $tran->order->order_code ?? 'N/A' }}</td>
                                <td>{{ $tran->paymentMethod->name ?? 'N/A' }}</td>
                                <td>
                                    @if($tran->createdBy)
                                        {{ $tran->createdBy->name }}  <!-- Hiển thị tên người cộng tiền -->
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $tran->description }}</td>
                                <td>{{ $tran->created_at->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có giao dịch nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $transactions->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
