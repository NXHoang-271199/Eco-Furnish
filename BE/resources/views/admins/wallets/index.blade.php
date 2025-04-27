@extends('layouts.admin')

@section('title', 'Quản lý ví tiền')

@section('CSS')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
@endsection

@section('content')
    <div class="container-fluid">
        <x-alert />
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Danh sách ví người dùng</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <form method="GET" action="{{ route('wallets.index') }}" class="mb-3">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Tên khách hàng / Email / Số điện thoại" value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3">
                                <select name="withdraw_filter" class="form-select">
                                    <option value="">Tất cả</option>
                                    <option value="1" {{ request('withdraw_filter') == '1' ? 'selected' : '' }}>Có yêu cầu rút tiền</option>
                                    <option value="2" {{ request('withdraw_filter') == '2' ? 'selected' : '' }}>Không có yêu cầu rút tiền</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="bx bx-search"></i>
                                    </button>
                                    <a href="{{ route('wallets.index') }}" class="btn btn-danger">
                                        <i class="bx bx-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>


                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th scope="col">STT</th>
                                <th scope="col">Tên khách hàng</th>
                                <th scope="col">Email</th>
                                <th scope="col">Số điện thoại</th>
                                <th scope="col">Số dư</th>
                                <th scope="col">Yêu cầu rút tiền (Chờ xử lý)</th>
                                <th scope="col">Ngày tạo ví</th>
                                <th scope="col">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($wallets as $key => $wallet)
                                <tr>
                                    <td class="text-center">{{ $key + 1 }}</td>
                                    <td>
                                        <a href="{{ route('users.show', $wallet->user->id) }}"
                                            class="fw-semibold text-dark text-decoration-none">
                                            {{ $wallet->user->name }}
                                        </a>
                                    </td>
                                    <td>{{ $wallet->user->email }}</td>
                                    <td>{{ $wallet->user->phone ?? 'Chưa có' }}</td>
                                    <td>{{ number_format($wallet->balance, 0, ',', '.') }} đ</td>
                                    <td class="text-center">
                                        @php $count = $withdrawRequests[$wallet->user->id] ?? 0; @endphp
                                        <span
                                            class="d-inline-block px-2 py-1 rounded fw-semibold
                                            {{ $count > 0 ? 'bg-danger text-white' : 'bg-success-subtle text-success' }}">
                                            {{ $count }}
                                        </span>
                                    </td>

                                    <td>{{ $wallet->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('wallets.show', $wallet->id) }}" class="btn btn-info btn-sm">
                                            <i class="ri-eye-line align-middle"></i> Chi tiết
                                        </a>
                                        @if (Auth::user()->hasPermission('edit-wallets'))
                                            <a href="javascript:void(0);" class="btn btn-primary btn-sm"
                                                onclick="openBalanceModal({{ $wallet->id }}, '{{ $wallet->user->name }}')">
                                                <i class="ri-wallet-3-line align-middle"></i> Cộng số dư
                                            </a>
                                        @endif
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
                        Swal.fire({
                            title: 'Thành công!',
                            text: `Đã cộng ${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount)} vào ví thành công!`,
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 2000,
                            timerProgressBar: true,
                            customClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            }
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Lỗi!',
                            text: data.message || 'Đã có lỗi xảy ra!',
                            icon: 'error',
                            confirmButtonText: 'Đóng',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    console.error('Lỗi:', error);
                    Swal.fire({
                        title: 'Lỗi kết nối!',
                        text: 'Không thể kết nối đến máy chủ!',
                        icon: 'error',
                        confirmButtonText: 'Đóng',
                        confirmButtonColor: '#dc3545'
                    });
                });
        });
    </script>
@endsection
