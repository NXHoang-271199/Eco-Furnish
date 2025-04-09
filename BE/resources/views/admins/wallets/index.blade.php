@extends('layouts.admin')

@section('title', 'Quản lý ví tiền')

@section('content')
    <div class="container-fluid">
        <x-alert />
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Danh sách ví người dùng</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th scope="col">STT</th>
                                <th scope="col">Tên khách hàng</th>
                                <th scope="col">Email</th>
                                <th scope="col">Số điện thoại</th>
                                <th scope="col">Số dư</th>
                                <th scope="col">Ngày tạo ví</th>
                                <th scope="col">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($wallets as $key => $wallet)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td>
                                        <a href="{{ route('users.show', $wallet->user->id) }}" class="fw-semibold">
                                            {{ $wallet->user->name }}
                                        </a>
                                    </td>
                                    <td>{{ $wallet->user->email }}</td>
                                    <td>{{ $wallet->user->phone ?? 'Chưa có' }}</td>
                                    <td>{{ number_format($wallet->balance, 0, ',', '.') }} đ</td>
                                    <td>{{ $wallet->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('wallets.show', $wallet->id) }}" class="btn btn-info btn-sm">
                                            <i class="ri-eye-line align-middle"></i> Chi tiết
                                        </a>
                                        <a href="javascript:void(0);" class="btn btn-primary btn-sm"
                                            onclick="openBalanceModal({{ $wallet->id }}, '{{ $wallet->user->name }}')">
                                            <i class="ri-wallet-3-line align-middle"></i> Cộng số dư
                                        </a>

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <div class="mt-3">
                            {{ $wallets->links('pagination::bootstrap-5') }}
                        </div>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal cộng số dư -->
    <div class="modal fade" id="balanceModal" tabindex="-1" aria-labelledby="balanceModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="balanceForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="balanceModalLabel">Cộng số dư cho <span id="modalUserName"></span></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="wallet_id" id="walletId">
                        <div class="mb-3">
                            <label for="amount" class="form-label">Số tiền cần cộng</label>
                            <input type="number" class="form-control" name="amount" id="amount" required
                                min="1">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Lý do nạp tiền (tuỳ chọn)</label>
                            <input type="text" class="form-control" name="description" id="description"
                                placeholder="VD: Nạp tiền theo yêu cầu khách hàng...">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Xác nhận</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Huỷ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('JS')
    <script>
        function openBalanceModal(walletId, userName) {
            document.getElementById('walletId').value = walletId;
            document.getElementById('amount').value = '';
            document.getElementById('modalUserName').textContent = userName;

            let modal = new bootstrap.Modal(document.getElementById('balanceModal'));
            modal.show();
        }
        const routeTemplate = "{{ route('wallets.update_balance', ['id' => 'WALLET_ID']) }}";

        document.getElementById('balanceForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const walletId = document.getElementById('walletId').value;
            const amount = document.getElementById('amount').value;
            const description = document.getElementById('description').value;
            const token = document.querySelector('input[name=_token]').value;
            const url = routeTemplate.replace('WALLET_ID', walletId);

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: amount,
                        description: description
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('balanceModal')).hide();
                        alert('Cộng tiền thành công!');
                        location.reload();
                    } else {
                        alert(data.message || 'Đã có lỗi xảy ra!');
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    alert('Không thể kết nối đến máy chủ!');
                });
        });
    </script>
@endsection
