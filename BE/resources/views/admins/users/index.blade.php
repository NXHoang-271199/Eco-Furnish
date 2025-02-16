@extends('layouts.admin')

@section('title')
    Quản lý người dùng
@endsection

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Quản lý người dùng</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Admin</a></li>
                            <li class="breadcrumb-item active">Danh sách người dùng</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col">

                <div class="h-100">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Danh sách người dùng</h4>
                            <a href="" class="btn btn-soft-success material-shadow-none">
                                <i class="ri-add-circle-line align-middle me-1"></i>
                                Thêm người dùng
                            </a>
                        </div><!-- end card header -->

                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <strong>{{ session('success') }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong> {{ session('error') }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="card-body">
                            <div class="live-preview">
                                <div class="table-responsive">

                                    <form action="" method="GET">
                                        <div class="row">
                                            <!-- Mã sản phẩm -->
                                            <div class="col-md-4 mb-3">
                                                <label for="ten_nguoi_dung" class="form-label">Tên người dùng</label>
                                                <input type="text" class="form-control" id="ten_nguoi_dung"
                                                    name="ten_nguoi_dung" placeholder="Nhập tên người dùng...." value="">
                                            </div>
                                            <!-- Tên sản phẩm -->
                                            <div class="col-md-4 mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="text" class="form-control" id="email"
                                                    name="email" placeholder="Nhập email...." value="">
                                            </div>
                                        </div>
                                        <!-- Nút Tìm kiếm -->
                                        <div class="d-flex justify-content-start">
                                            <button type="submit" class="btn btn-success">Tìm kiếm</button>
                                        </div>
                                    </form>

                                    <table class="table table-striped table-nowrap align-middle mb-3 text-center">
                                        <thead>
                                            <tr>
                                                <th scope="col">STT</th>
                                                <th scope="col">Tên người dùng</th>
                                                <th scope="col">Ảnh đại diện</th>
                                                <th scope="col">Tuổi</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Địa chỉ</th>
                                                <th scope="col">Quyền</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td scope="col">1</td>
                                                <td scope="col">ma_sinh_vien</td>
                                                <td scope="col">ten_sinh_vien</td>
                                                <td scope="col">
                                                    <img src="" alt="" class="img-thumbnail" width="100px">
                                                </td>
                                                <td scope="col">ngay_sinh</td>
                                                <td scope="col">so_dien_thoai</td>

                                                <td scope="col">
                                                    <span class="badge bg-success-subtle text-success text-uppercase">Active</span>

                                                    <span class="badge bg-success-subtle text-success text-uppercase">Unactive</span>
                                                </td>
                                                <td scope="col">
                                                    <a href=""
                                                        class="btn btn-sm btn-primary"><i class="ri-slideshow-line"></i></a>
                                                    <a href=""
                                                        class="btn btn-sm btn-success"><i class="ri-edit-line"></i></a>
                                                    <form action=""
                                                        method="POST" class="d-inline"
                                                        onclick="return confirm('Muốn xóa không ?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger"><i class="bx bxs-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    {{-- {{ $listSinhVien->links('pagination::bootstrap-5') }} --}}
                                </div>
                            </div>

                        </div><!-- end card-body -->
                    </div><!-- end card -->

                </div> <!-- end .h-100-->

            </div> <!-- end col -->
        </div>
    </div>
@endsection
