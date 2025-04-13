<table class="table table-nowrap mb-0">
    <thead>
        <tr class="text-muted">
            <th scope="col">#</th>
            <th scope="col">Ảnh</th>
            <th scope="col">Tên</th>
            <th scope="col">Email</th>
            <th scope="col">Địa chỉ</th>
            <th scope="col">Ngày tham gia</th>
            <th scope="col">Trạng thái</th>
            <th scope="col" class="text-end">Thao tác</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($listUsers as $user)
            @if($user->role->slug === 'client')
            <tr data-user-id="{{ $user->id }}">
                <td>{{ $loop->iteration }}</td>
                <td>
                    @if($user->avatar && Storage::exists($user->avatar))
                        <img src="{{ Storage::url($user->avatar) }}" 
                            alt="ảnh {{ $user->name }}"
                            class="rounded-circle avatar-md">
                    @else
                        <img src="{{ asset('assets/admins/images/users/avatarUser.png') }}"
                            alt="ảnh mặc định"
                            class="rounded-circle avatar-md">
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="fs-14 mb-0">{{ $user->name }}</h5>
                        </div>
                    </div>
                </td>
                <td>{{ $user->email }}</td>
                <td>{{ $user->address }}</td>
                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                <td>
                    @if($user->is_active)
                        <span class="badge bg-success-subtle">Hoạt động</span>
                    @else
                        <span class="badge bg-danger-subtle">Đã khóa</span>
                    @endif
                </td>
                <td class="text-end">
                    <div class="d-flex gap-2 justify-content-end">
                        @if($user->is_active)
                            <button class="btn btn-sm btn-soft-danger" onclick="toggleStatus({{ $user->id }})">
                                <i class="ri-lock-line"></i>
                            </button>
                        @else
                            <button class="btn btn-sm btn-soft-success" onclick="toggleStatus({{ $user->id }})">
                                <i class="ri-lock-unlock-line"></i>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @endif
        @endforeach
    </tbody>
</table>

@section('JS')
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Kiểm tra URL để active đúng tab
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            if (status === 'inactive') {
                $('.nav-tabs .nav-link[href="#inactive-users"]').tab('show');
                loadUsers(window.location.href);
            }

            // Xử lý form submit
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();
                performSearch();
            });

            // Xử lý khi người dùng nhập
            let searchTimeout;
            $('#searchName, #searchEmail').on('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(performSearch, 500);
            });

            // Xử lý phân trang
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                let url = $(this).attr('href');
                loadUsers(url);
                // Cập nhật URL trình duyệt nhưng giữ nguyên status
                const currentStatus = $('#statusFilter').val();
                const newUrl = updateUrlParameter(url, 'status', currentStatus);
                window.history.pushState({}, '', newUrl);
            });

            // Load inactive users when tab is clicked
            $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
                const status = $(e.target).attr('href') === '#inactive-users' ? 'inactive' : 'active';
                $('#statusFilter').val(status);
                performSearch();
            });
        });

        function changeTab(status) {
            $('#statusFilter').val(status);
            performSearch();
        }

        function performSearch() {
            let url = '{{ route("users.index") }}?' + $('#searchForm').serialize();
            loadUsers(url);
            window.history.pushState({}, '', url);
        }

        function loadUsers(url) {
            const status = $('#statusFilter').val();
            // Đảm bảo URL luôn có tham số status
            if (!url.includes('status=')) {
                url = updateUrlParameter(url, 'status', status);
            }

            $.ajax({
                url: url,
                type: 'GET',
                beforeSend: function() {
                    let container = status === 'inactive' ? '#inactiveUserTableContainer' : '#userTableContainer';
                    $(container).html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>');
                },
                success: function(response) {
                    if (response.success) {
                        let container = status === 'inactive' ? '#inactiveUserTableContainer' : '#userTableContainer';
                        let paginationContainer = status === 'inactive' ? '#inactivePaginationContainer' : '#paginationContainer';
                        
                        $(container).html(response.html);
                        $(paginationContainer).html(response.pagination);
                    } else {
                        Swal.fire({
                            title: 'Lỗi!',
                            text: 'Không thể tải dữ liệu',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr) {
                    let message = 'Không thể tải dữ liệu';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Lỗi!',
                        text: message,
                        icon: 'error'
                    });
                }
            });
        }

        // Hàm hỗ trợ cập nhật tham số trong URL
        function updateUrlParameter(url, key, value) {
            const urlObj = new URL(url, window.location.origin);
            urlObj.searchParams.set(key, value);
            return urlObj.toString();
        }

        function toggleStatus(userId) {
            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: "Bạn có muốn thay đổi trạng thái của người dùng này?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
            }).then((result) => {
                if (result.isConfirmed) {
                    // Hiển thị loading
                    Swal.fire({
                        title: 'Đang xử lý...',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/admin/users/${userId}/toggle-status`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Thành công!',
                                    text: response.message,
                                    icon: 'success',
                                    showConfirmButton: false,
                                    timer: 1500
                                });

                                // Thay vì reload trang, chúng ta sẽ cập nhật UI
                                if (response.status) {
                                    // Nếu tài khoản được kích hoạt, load lại tab active
                                    if ($('#statusFilter').val() === 'inactive') {
                                        // Xóa dòng khỏi bảng inactive
                                        $(`tr[data-user-id="${userId}"]`).fadeOut(300, function() {
                                            $(this).remove();
                                            // Nếu không còn dòng nào trong bảng
                                            if ($('#inactiveUserTableContainer table tbody tr').length === 0) {
                                                $('#inactiveUserTableContainer').html('<div class="text-center p-4">Không có tài khoản bị vô hiệu hóa</div>');
                                            }
                                        });
                                    }
                                    // Load lại tab active để hiển thị tài khoản mới được kích hoạt
                                    if ($('#statusFilter').val() === 'active') {
                                        loadUsers('{{ route("users.index") }}?status=active');
                                    }
                                } else {
                                    // Nếu tài khoản bị vô hiệu hóa
                                    if ($('#statusFilter').val() === 'active') {
                                        // Xóa dòng khỏi bảng active
                                        $(`tr[data-user-id="${userId}"]`).fadeOut(300, function() {
                                            $(this).remove();
                                            // Nếu không còn dòng nào trong bảng
                                            if ($('#userTableContainer table tbody tr').length === 0) {
                                                $('#userTableContainer').html('<div class="text-center p-4">Không có tài khoản hoạt động</div>');
                                            }
                                        });
                                    }
                                    // Load lại tab inactive để hiển thị tài khoản mới bị vô hiệu hóa
                                    if ($('#statusFilter').val() === 'inactive') {
                                        loadUsers('{{ route("users.index") }}?status=inactive');
                                    }
                                }
                            } else {
                                Swal.fire({
                                    title: 'Thông báo!',
                                    text: response.message,
                                    icon: 'info'
                                });
                            }
                        },
                        error: function(xhr) {
                            let message = 'Có lỗi xảy ra khi cập nhật trạng thái.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                message = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'Lỗi!',
                                text: message,
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection 