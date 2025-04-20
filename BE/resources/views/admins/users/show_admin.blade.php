@extends('layouts.admin')

@section('title')
    Danh sách tài khoản quản trị
@endsection

@section('CSS')
    <link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/admins/libs/gridjs/theme/mermaid.min.css') }}" rel="stylesheet">
    <style>
        .search-box {
            width: 100%;
            margin: 0;
        }
        
        .search-box .form-control {
            padding: 0.4rem 0.8rem;
            font-size: 13px;
            height: 35px;
            border-radius: 4px;
            border: 1px solid #e2e5e8;
            width: 200px;
        }
        
        .search-box .btn-search {
            padding: 0.4rem 1rem;
            background-color: #405189;
            border-color: #405189;
            color: #fff;
            font-size: 13px;
            height: 35px;
        }

        .search-box .btn-search:hover {
            background-color: #364574;
            border-color: #364574;
        }
        
        .search-form-wrapper {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        @media (max-width: 576px) {
            .search-box .form-control {
                width: 100%;
            }
            .search-form-wrapper {
                flex-direction: column;
            }
        }
        
        .table thead tr {
            background-color: #f3f6f9;
        }
        
        .table thead th {
            font-weight: 500;
            border: 0;
            color: #878a99;
            font-size: 13px;
        }
        
        .table tbody td {
            vertical-align: middle;
            font-size: 13px;
            color: #212529;
            border-top: 1px solid #e9ebec;
            padding: 1rem 0.6rem;
        }
        
        .avatar-xs {
            width: 32px;
            height: 32px;
        }
        
        .badge {
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 500;
            border-radius: 3px;
        }
        
        .badge.bg-success-subtle {
            background-color: #daf4e8 !important;
            color: #0ab39c !important;
        }
        
        .badge.bg-danger-subtle {
            background-color: #fbdbde !important;
            color: #f06548 !important;
        }

        .badge.bg-primary-subtle {
            background-color: #e0e4f9 !important;
            color: #405189 !important;
        }

        .btn-soft-primary {
            background-color: rgba(64,81,137,.1);
            color: #405189;
            border: 1px solid transparent;
        }

        .btn-soft-danger {
            background-color: rgba(240,101,72,.1);
            color: #f06548;
            border: 1px solid transparent;
        }

        .btn-soft-success {
            background-color: rgba(10,179,156,.1);
            color: #0ab39c;
            border: 1px solid transparent;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: .875rem;
        }

        .pagination {
            margin-bottom: 0;
        }

        .page-link {
            padding: 0.5rem 0.75rem;
            color: #405189;
            background-color: #fff;
            border: 1px solid #e9ebec;
        }

        .page-item.active .page-link {
            background-color: #405189;
            border-color: #405189;
        }

        .fs-14 {
            font-size: 14px !important;
        }

        .text-muted {
            color: #878a99 !important;
        }

        .card {
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 2px rgba(56,65,74,.15);
        }

        .card-header {
            border-bottom: 1px solid #e9ebec;
            background-color: #fff;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card table-card gridjs-border-none">
                    <div class="card-header">
                        <div class="row g-2 align-items-center">
                            <div class="col-md-6">
                                <h4 class="card-title mb-0">Danh sách quản trị</h4>
                            </div>
                            <div class="col-md-6">
                                <div class="search-box">
                                    <form id="searchForm" class="search-form-wrapper">
                                        <input type="search" class="form-control" placeholder="Tìm theo tên"
                                            name="name" value="{{ request('name') }}" id="searchName">
                                        <input type="search" class="form-control" placeholder="Tìm theo email"
                                            name="email" value="{{ request('email') }}" id="searchEmail">
                                        <button type="submit" class="btn btn-search">
                                            <i class="ri-search-line"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive table-card" id="adminTableContainer">
                            @include('partials.users.admin_table')
                        </div>
                        <div class="d-flex justify-content-end mt-3" id="paginationContainer">
                            {{ $listUsers->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('JS')
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        $(document).ready(function() {
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
                loadAdmins(url);
            });
        });

        function performSearch() {
            let url = '{{ route("users.admins") }}?' + $('#searchForm').serialize();
            loadAdmins(url);
            // Cập nhật URL trình duyệt
            window.history.pushState({}, '', url);
        }

        function loadAdmins(url) {
            $.ajax({
                url: url,
                type: 'GET',
                beforeSend: function() {
                    // Hiển thị loading nếu cần
                    $('#adminTableContainer').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Đang tải...</div>');
                },
                success: function(response) {
                    if (response.success) {
                        $('#adminTableContainer').html(response.html);
                        $('#paginationContainer').html(response.pagination);
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

        function deleteUser(userId) {
            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: "Bạn sẽ không thể khôi phục lại dữ liệu này!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/admin/users/${userId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'Thành công!',
                                text: 'Tài khoản đã được xóa thành công.',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let message = 'Có lỗi xảy ra khi xóa tài khoản.';
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