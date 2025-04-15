@extends('layouts.admin')

@section('title', 'Chi tiết đánh giá')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Chi tiết đánh giá #{{ $review->id }}</h3>
                        <div class="card-tools">
                            <a href="{{ route('reviews.product', $review->product_id) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <!-- Cột 1: Thông tin đánh giá -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>ID:</label>
                                    <p>{{ $review->id }}</p>
                                </div>
                                <div class="form-group">
                                    <label>Người dùng:</label>
                                    <a href="{{ route('reviews.user-info', $review->user_id) }}" class="user-info-link"
                                        data-toggle="tooltip" title="Xem thông tin người dùng">
                                        <p>{{ $review->user->name }}</p>
                                    </a>
                                </div>
                                @if (!empty($review->productVariant))
                                    <div class="form-group">
                                        <label>Phân loại:</label>
                                        <p class="variant-info mb-2">
                                            <span class="badge bg-light text-dark fs-6">
                                                {{ implode(' - ', $review->variant_info) }}
                                            </span>
                                        </p>
                                    </div>
                                @else
                                    <div class="form-group">
                                        <label>Phân loại:</label>
                                        <p class="variant-info mb-2">Không có phân loại</p>
                                    </div>
                                @endif

                                <div class="form-group">
                                    <label>Sản phẩm:</label>
                                    <p>{{ $review->product->name }}</p>
                                </div>
                                <div class="form-group">
                                    <label>Số sao:</label>
                                    <p>
                                        <span class="badge bg-warning">
                                            {{ $review->rating }} ★
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <!-- Cột 2: Thông tin trạng thái -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Trạng thái:</label>
                                    <p>
                                        <span class="badge {{ $review->is_hidden ? 'bg-danger' : 'bg-success' }}">
                                            {{ $review->is_hidden ? 'Ẩn' : 'Hiển thị' }}
                                        </span>
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label>Ghi chú:</label>
                                    <p>
                                        {{ $review->note }}
                                    </p>
                                </div>
                                <div class="form-group">
                                    <label>Ngày tạo:</label>
                                    <p>{{ $review->created_at ? $review->created_at->format('d/m/Y H:i:s') : '' }}</p>
                                </div>
                                <div class="form-group">
                                    <label>Ngày cập nhật:</label>
                                    <p>{{ $review->updated_at ? $review->updated_at->format('d/m/Y H:i:s') : '' }}</p>
                                </div>
                                <div class="form-group">
                                    <label>Mã đơn hàng:</label>
                                    <p>
                                        @if ($review->order)
                                            <a href="{{ route('orders.show', $review->order->id) }}" class="text-primary">
                                                {{ $review->order->order_code }}
                                            </a>
                                        @else
                                            <span class="text-muted">Không có thông tin đơn hàng</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Hình ảnh đánh giá -->
                        @if ($review->images && count($review->images) > 0)
                            <div class="row">
                                <div class="col-12">
                                    <label>Hình ảnh đính kèm:</label>
                                    <div class="row g-1"> {{-- Thêm g-1 để giảm khoảng cách --}}
                                        @foreach ($review->images as $image)
                                            <div class="col-auto"> {{-- Sử dụng col-auto để ảnh không bị dàn trải quá rộng --}}
                                                <img src="{{ Storage::url($image) }}" alt="Ảnh đánh giá"
                                                    class="img-fluid rounded shadow-sm" style="max-width: 100px;">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Nội dung đánh giá -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label>Nội dung đánh giá:</label>
                                    <div class="p-3 bg-light rounded">
                                        {{ $review->review_text }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- /.card-body -->
                    <div class="card-footer">
                        <div class="btn-group">
                            <button type="button"
                                class="btn {{ $review->is_hidden ? 'btn-success' : 'btn-warning' }} toggle-review-btn"
                                data-review-id="{{ $review->id }}" data-is-hidden="{{ $review->is_hidden ? '1' : '0' }}"
                                {{ !$review->is_hidden ? 'onclick="openHideReviewModal(' . $review->id . ')"' : '' }}>
                                <i class="fas {{ $review->is_hidden ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                {{ $review->is_hidden ? 'Hiển thị đánh giá' : 'Ẩn đánh giá' }}
                            </button>
                        </div>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>
    </div>

    {{-- Model điền lí do ẩn --}}
    <div class="modal fade" id="hideReviewModal" tabindex="-1" role="dialog" aria-labelledby="hideReviewModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="hideReviewModalLabel">Lý do ẩn đánh giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="hideReviewForm" onsubmit="return false;">
                    <input type="hidden" id="reviewId" name="reviewId">
                    <div class="mb-3">
                        <label for="note" class="form-label">Vui lòng nhập lý do ẩn đánh giá:</label>
                        <input type="text" class="form-control" id="note" name="note" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="submitHideReview">Xác nhận</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('JS')
    <script>
        // Template URL cho route
        const routeTemplate = "{{ route('reviews.toggle', ['reviewId' => 'REVIEW_ID']) }}";

        // Hàm mở modal
        function openHideReviewModal(reviewId) {
            const reviewIdInput = document.getElementById('reviewId');
            const noteInput = document.getElementById('note');

            if (!reviewIdInput || !noteInput) {
                console.error('Không tìm thấy #reviewId hoặc #note trong modal!');
                return;
            }

            reviewIdInput.value = reviewId;
            noteInput.value = ''; // Reset input

            let modal = new bootstrap.Modal(document.getElementById('hideReviewModal'), {
                backdrop: 'static',
                keyboard: false
            });
            modal.show();
        }

        // Hàm hiển thị thông báo thành công
        function showSuccess(message) {
            Swal.fire({
                title: 'Thành công!',
                text: message,
                icon: 'success',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                customClass: {
                    popup: 'animate__animated animate__fadeInDown'
                }
            }).then(() => {
                location.reload(); // Load lại trang sau khi thông báo thành công
            });
        }

        // Hàm hiển thị thông báo lỗi
        function showError(message) {
            Swal.fire({
                title: 'Lỗi!',
                text: message || 'Đã có lỗi xảy ra!',
                icon: 'error',
                confirmButtonText: 'Đóng',
                confirmButtonColor: '#dc3545'
            });
        }

        // Hàm gửi request
        function sendRequest(url, token, data) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .catch(error => {
                console.error('Lỗi:', error);
                throw new Error('Không thể kết nối đến máy chủ!');
            });
        }

        // Xử lý sự kiện click nút ẩn/hiển thị
        document.querySelectorAll('.toggle-review-btn').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const reviewId = this.getAttribute('data-review-id');
                const isHidden = this.getAttribute('data-is-hidden') === '1';

                if (isHidden) {
                    // Hiển thị lại đánh giá
                    const url = routeTemplate.replace('REVIEW_ID', reviewId);
                    const token = document.querySelector('input[name=_token]').value;

                    sendRequest(url, token, {})
                        .then(data => {
                            if (data.success) {
                                showSuccess(data.message);
                            } else {
                                showError(data.message);
                            }
                        })
                        .catch(error => {
                            showError(error.message);
                        });
                } else {
                    // Mở modal để nhập lý do ẩn
                    openHideReviewModal(reviewId);
                }
            });
        });

        // Xử lý submit trong modal
        const submitHideReviewBtn = document.getElementById('submitHideReview');
        if (submitHideReviewBtn) {
            submitHideReviewBtn.addEventListener('click', function(e) {
                e.preventDefault(); // Ngăn hành vi mặc định của form
                console.log('Nút Xác nhận được nhấn'); // Debug

                const reviewId = document.getElementById('reviewId').value;
                console.log('reviewId:', reviewId); // Debug

                const note = document.getElementById('note').value.trim();

                if (!reviewId) {
                    showError('Không tìm thấy ID đánh giá!');
                    return;
                }

                if (!note) {
                    Swal.fire({
                        title: 'Cảnh báo!',
                        text: 'Vui lòng nhập lý do ẩn đánh giá!',
                        icon: 'warning',
                        confirmButtonText: 'Đóng',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                const url = routeTemplate.replace('REVIEW_ID', reviewId);
                const token = document.querySelector('input[name=_token]').value;

                sendRequest(url, token, { note })
                    .then(data => {
                        if (data.success) {
                            bootstrap.Modal.getInstance(document.getElementById('hideReviewModal')).hide();
                            showSuccess(data.message);
                        } else {
                            showError(data.message);
                        }
                    })
                    .catch(error => {
                        showError(error.message);
                    });
            });
        } else {
            console.error('Không tìm thấy nút #submitHideReview!');
        }

        // Reset modal khi đóng
        const hideReviewModal = document.getElementById('hideReviewModal');
        if (hideReviewModal) {
            hideReviewModal.addEventListener('hidden.bs.modal', function() {
                document.getElementById('note').value = '';
            });
        } else {
            console.error('Không tìm thấy modal #hideReviewModal!');
        }
    </script>
@endsection
