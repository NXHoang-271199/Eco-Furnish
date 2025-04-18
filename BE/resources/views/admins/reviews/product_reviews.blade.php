@extends('layouts.admin')

@section('title', 'Đánh giá sản phẩm: ' . $product->name)

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin sản phẩm</h3>
                        <div class="card-tools">
                            <a href="{{ route('reviews.index') }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-arrow-left"></i> Quay lại danh sách
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                @if ($product->image_thumnail)
                                    <img src="{{ asset('storage/' . $product->image_thumnail) }}" alt="{{ $product->name }}"
                                        class="img-fluid">
                                @else
                                    <div class="text-center p-4 bg-light">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                        <p class="mt-2">Không có ảnh</p>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-10">
                                <h4>{{ $product->name }}</h4>
                                <p><strong>ID:</strong> {{ $product->id }}</p>
                                <p><strong>Giá:</strong> {{ number_format($product->price) }} VNĐ</p>
                                <p><strong>Danh mục:</strong> {{ $product->category->name ?? 'Không có' }}</p>
                                <p><strong>Tổng số đánh giá:</strong> <span
                                        class="badge bg-primary">{{ $reviews->total() }}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách đánh giá</h3>
                        <div class="card-tools">
                            <form action="{{ route('reviews.product', $product->id) }}" method="GET"
                                class="input-group input-group-sm" style="width: 250px;">
                                <input type="text" name="search" class="form-control float-right"
                                    placeholder="Tìm kiếm đánh giá" value="{{ request('search') }}">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-default">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px">STT</th>
                                    <th style="width: 150px">Người dùng</th>
                                    <th style="width: 100px">Số sao</th>
                                    <th>Nội dung</th>
                                    <th style="width: 100px">Trạng thái</th>
                                    <th>Ghi chú</th>
                                    <th style="width: 150px">Ngày tạo</th>
                                    <th style="width: 200px">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reviews as $key => $review)
                                    <tr>
                                        <td>{{ $reviews->firstItem() + $key }}</td>
                                        <td>
                                            <a href="{{ route('reviews.user-info', $review->user_id) }}"
                                                class="user-info-link" data-toggle="tooltip"
                                                title="Xem thông tin người dùng">
                                                {{ $review->user->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning">
                                                {{ $review->rating }} ★
                                            </span>
                                        </td>
                                        <td>{{ $review->review_text }}</td>
                                        <td>
                                            <span class="badge {{ $review->is_hidden ? 'bg-danger' : 'bg-success' }}">
                                                {{ $review->is_hidden ? 'Ẩn' : 'Hiển thị' }}
                                            </span>
                                        </td> <!-- Hiển thị trạng thái ẩn/hiển thị -->
                                        <td>{{ $review->note }}</td>
                                        <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('reviews.show', $review->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> Xem chi tiết
                                            </a>
                                            <button type="button"
                                                class="btn btn-sm {{ $review->is_hidden ? 'btn-success' : 'btn-warning' }} toggle-review-btn"
                                                data-review-id="{{ $review->id }}"
                                                data-is-hidden="{{ $review->is_hidden ? '1' : '0' }}"
                                                {{ !$review->is_hidden ? 'onclick="openHideReviewModal(' . $review->id . ')"' : '' }}>
                                                <i class="fas {{ $review->is_hidden ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                                {{ $review->is_hidden ? 'Hiển thị' : 'Ẩn' }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Không có đánh giá nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer clearfix">
                        <div class="float-right">
                            {{ $reviews->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                </div>
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
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
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
