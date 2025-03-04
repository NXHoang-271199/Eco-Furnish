@extends('layouts.admin')

@section('title')
    Chi tiết vai trò
@endsection

@section('CSS')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection


@section('JS')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $(document).on('click', '.delete-btn', function(e) {
                e.preventDefault();

                let form = $(this).closest("form");

                // Debug: Kiểm tra xem sự kiện có chạy không
                console.log("Nút xóa đã được nhấn!");

                Swal.fire({
                    title: "Bạn có chắc chắn muốn xóa?",
                    text: "Hành động này không thể hoàn tác!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#dc3545",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Có, xóa!",
                    cancelButtonText: "Hủy"
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log("Đã xác nhận xóa!"); // Debug: Kiểm tra xem có xác nhận không
                        form.submit(); // Gửi form sau khi xác nhận
                    } else {
                        console.log("Hủy xóa!"); // Debug: Kiểm tra nếu người dùng hủy
                    }
                });
            });
        });
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Cập nhật vai trò</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            @foreach ($breadcrumbs as $breadcrumb)
                                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                    @if ($breadcrumb['url'])
                                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                                    @else
                                        {{ $breadcrumb['name'] }}
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-secondary alert-border-left alert-dismissible fade show material-shadow" role="alert">
                <i class="ri-check-double-line me-3 align-middle"></i>
                <strong>{{ session('success') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-warning alert-border-left alert-dismissible fade show material-shadow" role="alert">
                <i class="ri-alert-line me-3 align-middle"></i> <strong>{{ session('error') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <!-- end page title -->

        <div class="row">
            <!-- Column for the form -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Cập nhật vai trò</h5>
                    </div>
                    <div class="card-body">
                        <form id="category-form" action="{{ route('roles.update', $singerRole->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group">
                                <label for="title">Tên vai trò</label>
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Nhập tên vai trò..." value="{{ $singerRole->name }}">
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" id="submit-btn" class="btn btn-success mt-3">
                                <i class="ri-add-fill me-1 align-bottom"></i> Cập nhật vai trò
                            </button>

                            <button type="button" id="cancel-btn" class="btn btn-secondary mt-3 d-none">Hủy</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Column for the categories list -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Danh sách</h5>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-xxl-5 row-cols-lg-3 row-cols-md-2 row-cols-1">
                            <!-- Tables Without Borders -->
                            <table class="table table-borderless table-nowrap">
                                <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td scope="row"><h5>{{ $user->name }}</h5></td>
                                            <td>
                                                <div class="hstack gap-3 fs-15 justify-content-center">
                                                    {{-- <form action="{{ route('users.destroy', $user->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-danger mx-2 border-0 delete-btn" title="Xóa">
                                                            <i class="ri-delete-bin-5-line"></i>

                                                        </button>
                                                    </form> --}}
                                                    <a href="{{ route('users.show', $user->id) }}" class="text-primary mx-2 border-0" title="Chỉnh sửa"><i class="ri-eye-line"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $users->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-alert />

<<<<<<< HEAD
=======
    <div class="row">
        <!-- Column for the form -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5>Cập nhật vai trò</h5>
                </div>
                <div class="card-body">
                    <form id="category-form" action="{{ route('roles.update', $singerRole->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="title">Tên vai trò</label>
                            <input type="text" id="name" name="name" class="form-control"
                                placeholder="Nhập tên vai trò..." value="{{ $singerRole->name }}">
                            @error('name')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" id="submit-btn" class="btn btn-success mt-3">
                            <i class="ri-add-fill me-1 align-bottom"></i> Cập nhật vai trò
                        </button>

                        <button type="button" id="cancel-btn" class="btn btn-secondary mt-3 d-none">Hủy</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Column for the categories list -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5>Danh sách</h5>
                </div>
                <div class="card-body">
                    <div class="row row-cols-xxl-5 row-cols-lg-3 row-cols-md-2 row-cols-1">
                        <!-- Tables Without Borders -->
                        <table class="table table-borderless table-nowrap">
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td scope="row"><h5>{{ $user->name }}</h5></td>
                                        <td>
                                            <div class="hstack gap-3 fs-15 justify-content-center">
                                                {{-- <form action="{{ route('users.destroy', $user->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-danger mx-2 border-0 delete-btn" title="Xóa">
                                                        <i class="ri-delete-bin-5-line"></i>

                                                    </button>
                                                </form> --}}
                                                <a href="{{ route('users.show', $user->id) }}" class="text-primary mx-2 border-0" title="Chỉnh sửa"><i class="ri-eye-line"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $users->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
>>>>>>> 10364d08a8bcd4f28ff782a9812e783852789c1f
@endsection
