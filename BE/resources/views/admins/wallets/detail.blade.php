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
                <form id="filterForm" method="GET" action="{{ route('wallets.show', $wallet->id) }}">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search"
                                placeholder="Tên / Email / Số điện thoại" value="{{ request()->search }}">
                        </div>
                        <div class="col-md-4">
                            <select name="transaction_type" class="form-control">
                                <option value="">Chọn loại giao dịch</option>
                                <option value="nap_tien" {{ request()->transaction_type == 'nap_tien' ? 'selected' : '' }}>
                                    Nạp tiền</option>
                                <option value="hoan_tien"
                                    {{ request()->transaction_type == 'hoan_tien' ? 'selected' : '' }}>Hoàn tiền
                                </option>
                                <option value="thanh_toan_don_hang"
                                    {{ request()->transaction_type == 'thanh_toan_don_hang' ? 'selected' : '' }}>Thanh
                                    toán đơn hàng</option>
                                <option value="rut_tien" {{ request()->transaction_type == 'rut_tien' ? 'selected' : '' }}>
                                    Rút tiền</option>
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
                                <option value="da_huy" {{ request()->transaction_status == 'da_huy' ? 'selected' : '' }}>Đã
                                    hủy</option>
                            </select>
                        </div>

                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <input type="date" class="form-control" name="start_date"
                                value="{{ request()->start_date }}">
                        </div>
                        <div class="col-md-4">
                            <input type="date" class="form-control" name="end_date" value="{{ request()->end_date }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Tìm kiếm</button>
                        </div>
                        <div class="col-md-2">
                            <button type="reset" class="btn btn-danger w-100"
                                onclick="window.location='{{ route('wallets.transactions') }}'">Xóa tìm kiếm</button>
                        </div>
                    </div>
                </form>
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th></th>
                            <th>STT</th>
                            <th>Mã GD</th>
                            <th>Loại GD</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Thời gian</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $key => $tran)
                            <tr>
                                <td class="text-center">
                                    <button class="btn btn-sm btn-light toggle-detail"
                                        data-id="{{ $key }}">+</button>
                                </td>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>
                                    @if ($tran->type === 'nap_tien')
                                        {{ $tran->wallet_code ?? 'N/A' }}
                                    @elseif (in_array($tran->type, ['thanh_toan_don_hang', 'hoan_tien']) && $tran->order)
                                        <a href="{{ route('orders.detail', $tran->order_id) }}"
                                            class="text-dark text-decoration-none">
                                            {{ $tran->order->order_code }}
                                        </a>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ getTransactionTypeColor($tran->type) }}">
                                        {{ getTransactionTypeLabel($tran->type) }}
                                    </span>
                                </td>
                                <td>{{ number_format($tran->amount, 0, ',', '.') }}đ</td>
                                <td>
                                    <span class="badge bg-{{ getTransactionStatusColor($tran->status) }}">
                                        {{ getTransactionStatusLabel($tran->status) }}
                                    </span>
                                </td>
                                <td>{{ $tran->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @if ($tran->type === 'rut_tien' && $tran->withdrawRequest)
                                        <a class="btn btn-sm btn-outline-primary" href="#"
                                            onclick="openWithdrawModal('{{ route('wallets.withdraws.detail', $tran->withdrawRequest->id) }}')">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>

                            {{-- Chi tiết ẩn --}}
                            <tr class="transaction-detail-row d-none" id="detail-{{ $key }}">
                                <td colspan="8" class="bg-light">
                                    <strong>Số dư trước:</strong>
                                    {{ number_format($tran->balance_before ?? 0, 0, ',', '.') }}đ<br>
                                    <strong>Số dư cuối:</strong>
                                    {{ number_format($tran->balance_after ?? 0, 0, ',', '.') }}đ<br>
                                    <strong>Kênh:</strong> {{ $tran->paymentMethod->name ?? 'N/A' }}<br>
                                    <strong>Người thực hiện:</strong>
                                    {{ $tran->createdBy?->name ?? ($tran->updatedBy?->name ?? 'N/A') }}<br>
                                    <strong>Ghi chú:</strong> {{ $tran->description ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Không có giao dịch nào.</td>
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
    <!-- Modal yêu cầu rút -->
    <div class="modal fade" id="withdrawModal" tabindex="-1" aria-labelledby="withdrawModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"> <!-- Thêm modal-lg để tăng chiều rộng của modal -->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-1" id="withdrawModalBody">
                    <!-- Nội dung form sẽ được load bằng Ajax -->
                </div>
            </div>
        </div>
    </div>

    {{-- model từ chối --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="rejectForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectModalLabel">Từ chối yêu cầu rút tiền</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="withdraw_id" id="withdrawId">
                        <div class="mb-3">
                            <label for="description" class="form-label">Lý do từ chối</label>
                            <input type="text" class="form-control" name="description" id="description"
                                placeholder="Ví dụ: Tài khoản không hợp lệ" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xác nhận</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
@section('JS')
    <script>
        document.querySelectorAll('.toggle-detail').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const detailRow = document.getElementById('detail-' + id);
                detailRow.classList.toggle('d-none');
                this.textContent = this.textContent === '+' ? '-' : '+';
            });
        });
        // Mở modal withdraw_detail
        function openWithdrawModal(url) {
            console.log('Opening withdraw modal:', url); // Debug
            fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.html) {
                        document.getElementById('withdrawModalBody').innerHTML = data.html;
                        var withdrawModal = new bootstrap.Modal(document.getElementById('withdrawModal'));
                        withdrawModal.show();
                    } else {
                        Swal.fire({
                            title: 'Lỗi!',
                            text: 'Không thể tải thông tin rút tiền',
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
                        text: 'Không thể tải thông tin rút tiền: ' + error.message,
                        icon: 'error',
                        confirmButtonText: 'Đóng',
                        confirmButtonColor: '#dc3545'
                    });
                });
        }

        // Mở modal từ chối
        function openRejectModal(withdrawId) {
            console.log('Opening reject modal, ID:', withdrawId); // Debug

            // Đóng modal cha
            var withdrawModal = bootstrap.Modal.getInstance(document.getElementById('withdrawModal'));
            if (withdrawModal) {
                withdrawModal.hide();
                console.log('Closed withdrawModal');
            }

            // Cập nhật withdrawId vào form
            document.getElementById('withdrawId').value = withdrawId;
            document.getElementById('description').value = '';

            // Mở modal con
            let modal = new bootstrap.Modal(document.getElementById('rejectModal'));
            modal.show();
            console.log('Opened rejectModal');
        }

        // Tạo URL động cho reject
        const rejectRouteTemplate = "{{ route('wallets.withdraws.reject', ['id' => 'WITHDRAW_ID']) }}";

        // Xử lý submit form từ chối
        document.getElementById('rejectForm').addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Submitting reject form'); // Debug

            const withdrawId = document.getElementById('withdrawId').value;
            const description = document.getElementById('description').value;
            const token = document.querySelector('input[name=_token]').value;
            const url = rejectRouteTemplate.replace('WITHDRAW_ID', withdrawId);

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        description: description
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! Status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        bootstrap.Modal.getInstance(document.getElementById('rejectModal')).hide();
                        Swal.fire({
                            title: 'Thành công!',
                            text: data.success,
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
                            text: data.error || 'Đã có lỗi xảy ra!',
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
                        text: 'Không thể gửi yêu cầu: ' + error.message,
                        icon: 'error',
                        confirmButtonText: 'Đóng',
                        confirmButtonColor: '#dc3545'
                    });
                });
        });
    </script>
@endsection
