<div class="container">
    <div class="card shadow-lg border-0 m-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-3">
            <span>Thông tin rút tiền</span>
            <span class="badge bg-{{ getWithdrawStatusColor($withdraw->status) }} fs-6">
                {{ getWithdrawStatusLabel($withdraw->status) }}
            </span>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-12 text-center">
                    <img src="{{ $withdraw->bankAccount->bank_logo_url }}" alt="Bank Logo" class="img-fluid rounded"
                        style="max-width: 100px;">
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <strong>Tên ngân hàng:</strong>
                        <span>{{ $withdraw->bankAccount->bank_name }}</span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <strong>Mã ngân hàng:</strong>
                        <span>{{ $withdraw->bankAccount->bank_code }}</span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <strong>Số tài khoản:</strong>
                        <span>{{ $withdraw->bankAccount->bank_account_number }}</span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <strong>Tên tài khoản:</strong>
                        <span>{{ $withdraw->bankAccount->account_holder_name }}</span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between border-bottom pb-2">
                        <strong>Số tiền:</strong>
                        <span class="text-success fw-bold">{{ number_format($withdraw->amount, 0, ',', '.') }} đ</span>
                    </div>
                </div>
                <div class="col-12 text-center">
                    <div class="border-bottom pb-2">
                        <strong>QR Code:</strong>
                        <div class="mt-2">
                            <img src="{{ $withdraw->qr_code }}" alt="QR Code" class="img-fluid rounded"
                                style="max-width: 150px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-3 d-flex justify-content-center gap-2">
                @if ($withdraw->status === 'dang_xu_ly')
                    <form action="{{ route('wallets.withdraws.approve', $withdraw->id) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm">Duyệt</button>
                    </form>
                    <button type="button" class="btn btn-danger btn-sm"
                        onclick="openRejectModal({{ $withdraw->id }})">Từ chối</button>
                @endif
            </div>
        </div>
    </div>
</div>
