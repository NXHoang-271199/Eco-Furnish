@extends('layouts.admin')

@section('title')
    Quản lý người dùng
@endsection
@section('CSS')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        table.dataTable>thead .sorting:after,
        table.dataTable>thead .sorting_asc:after,
        table.dataTable>thead .sorting_asc_disabled:after,
        table.dataTable>thead .sorting_desc:after,
        table.dataTable>thead .sorting_desc_disabled:after,
        table.dataTable>thead .sorting:before,
        table.dataTable>thead .sorting_asc:before,
        table.dataTable>thead .sorting_asc_disabled:before,
        table.dataTable>thead .sorting_desc:before,
        table.dataTable>thead .sorting_desc_disabled:before {
            display: none !important
        }
    </style>
@endsection
@section('JS')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Danh sách người dùng</h4>
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
        <!-- end page title -->

        <div class="row">
            <div class="col">
                @if (session('status') && session('message'))
                    <script>
                        Swal.fire({
                            icon: '{{ session('status') }}',
                            title: '{{ session('message') }}',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    </script>
                @endif

                <div class="h-100">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">Danh sách người dùng</h4>
                            <a href="{{ route('users.create') }}" class="btn btn-soft-success material-shadow-none">
                                <i class="ri-add-circle-line align-middle me-1"></i>
                                Thêm người dùng
                            </a>
                        </div><!-- end card header -->

                        <div class="card-body">
                            <div class="live-preview">
                                <div class="table-responsive">
                                    <div class="card-body">
                                        <div id="example_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                                            <div class="row">
                                                <div class="col-sm-10 col-md-10">
                                                    <form action="" method="GET">
                                                        <div class="row">
                                                            <!-- Mã sản phẩm -->
                                                            <div class="col-md-4 mb-3">
                                                                <label for="name" class="form-label">Tên người
                                                                    dùng</label>
                                                                <input type="text" class="form-control" id="name"
                                                                    name="name" placeholder="Nhập tên người dùng...."
                                                                    value="">
                                                            </div>
                                                            <!-- Tên sản phẩm -->
                                                            <div class="col-md-4 mb-3">
                                                                <label for="email" class="form-label">Email</label>
                                                                <input type="text" class="form-control" id="email"
                                                                    name="email" placeholder="Nhập email...."
                                                                    value="">
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <button type="submit" class="btn btn-success mt-4">Tìm
                                                                    kiếm</button>
                                                            </div>
                                                        </div>
                                                        <!-- Nút Tìm kiếm -->
                                                        {{-- <div class="d-flex justify-content-start">
                                                           
                                                        </div> --}}
                                                    </form>
                                                </div>
                                                {{-- <div class="col-sm-2 col-md-2">
                                                    <div class="dataTables_length d-flex flex-row-reverse"
                                                        id="example_length"><label>Show <select name="example_length"
                                                                aria-controls="example" class="form-select form-select-sm">
                                                                <option value="10">10</option>
                                                                <option value="25">25</option>
                                                                <option value="50">50</option>
                                                                <option value="100">100</option>
                                                            </select> entries</label></div>
                                                </div> --}}
                                            </div>
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <table id="example"
                                                        class="table table-bordered dt-responsive nowrap table-striped align-middle dataTable no-footer dtr-inline"
                                                        style="width: 100%;" aria-describedby="example_info">
                                                        <thead>
                                                            <tr>
                                                                <th data-ordering="false" class="sorting text-center"
                                                                    tabindex="0" aria-controls="example" rowspan="1"
                                                                    colspan="1" style="width: 8.2889px;"
                                                                    aria-label="SR No.: activate to sort column ascending">
                                                                    STT</th>

                                                                <th data-ordering="false" class="sorting" tabindex="0"
                                                                    aria-controls="example" rowspan="1" colspan="1"
                                                                    style="width: 150px;"
                                                                    aria-label="ID: activate to sort column ascending">
                                                                    Avatar
                                                                </th>
                                                                <th data-ordering="false" class="sorting" tabindex="0"
                                                                    aria-controls="example" rowspan="1" colspan="1"
                                                                    style="width: 230px;"
                                                                    aria-label="ID: activate to sort column ascending">
                                                                    Người dùng
                                                                </th>
                                                                <th data-ordering="false" class="sorting" tabindex="0"
                                                                    aria-controls="example" rowspan="1" colspan="1"
                                                                    style="width: 117.289px;"
                                                                    aria-label="Purchase ID: activate to sort column ascending">
                                                                    Email</th>
                                                                <th data-ordering="false" class="sorting" tabindex="0"
                                                                    aria-controls="example" rowspan="1" colspan="1"
                                                                    style="width: 50.289px;"
                                                                    aria-label="Title: activate to sort column ascending">
                                                                    Tuổi</th>

                                                                <th class="sorting" tabindex="0"
                                                                    aria-controls="example" rowspan="1" colspan="1"
                                                                    style="width: 74.2889px;"
                                                                    aria-label="Status: activate to sort column ascending">
                                                                    Quyền</th>
                                                                <th class="sorting" tabindex="0"
                                                                    aria-controls="example" rowspan="1" colspan="1"
                                                                    style="width: 74.2889px;"
                                                                    aria-label="Status: activate to sort column ascending">
                                                                    Trạng thái</th>
                                                                <th class="sorting" tabindex="0"
                                                                    aria-controls="example" rowspan="1" colspan="1"
                                                                    style="width: 90.2889px;"
                                                                    aria-label="Action: activate to sort column ascending">
                                                                    Hành động</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($listUsers as $key => $user)
                                                                <tr class="odd">
                                                                    <td class="text-center">
                                                                        {{ $key + 1 + ($listUsers->currentPage() - 1) * $listUsers->perPage() }}
                                                                    </td>
                                                                    <td class="text-center"><img
                                                                            src="{{ Storage::url($user->avatar) }}"
                                                                            alt="ảnh {{ $user->name }}" srcset=""
                                                                            width="75" height="75"
                                                                            class="object-fit-cover"></td>
                                                                    <td>{{ $user->name }}</td>
                                                                    <td>{{ $user->email }}</td>
                                                                    <td class="text-center">{{ $user->age }}</td>
                                                                    <td class="text-center">
                                                                        <span
                                                                            class="badge bg-primary-subtle text-primary badge-border">{{ $user->role->name }}</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        @if ($user->is_active == 1)
                                                                            <span class="badge bg-success">Kích hoạt</span>
                                                                        @else
                                                                            <span class="badge bg-danger">Hủy kích
                                                                                hoạt</span>
                                                                        @endif
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <div class="dropdown d-inline-block">
                                                                            <button
                                                                                class="btn btn-soft-secondary btn-sm dropdown"
                                                                                type="button" data-bs-toggle="dropdown"
                                                                                aria-expanded="false">
                                                                                <i class="ri-more-fill align-middle"></i>
                                                                            </button>
                                                                            <ul class="dropdown-menu dropdown-menu-end"
                                                                                style="">
                                                                                <li>
                                                                                    <a href="{{ route('users.show', $user->id) }}"
                                                                                        class="dropdown-item"><i
                                                                                            class="ri-eye-fill align-bottom me-2 text-muted"></i>
                                                                                        View</a>
                                                                                </li>
                                                                                <li><a class="dropdown-item edit-item-btn"
                                                                                        href="{{ route('users.edit', $user->id) }}"><i
                                                                                            class="ri-pencil-fill align-bottom me-2 text-muted"></i>
                                                                                        Edit</a></li>
                                                                                <li>
                                                                                    <form
                                                                                        action="{{ route('users.show', $user->id) }}"
                                                                                        method="POST" class="d-inline">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button
                                                                                            class="dropdown-item edit-item-btn cursor-pointer delete-btn"><i
                                                                                                class="ri-delete-bin-fill align-bottom me-2 text-muted"></i>
                                                                                            Xóa</button>
                                                                                    </form>
                                                                                </li>
                                                                            </ul>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                    {{ $listUsers->links('pagination::bootstrap-5') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- end card-body -->
                    </div><!-- end card -->

                </div> <!-- end .h-100-->

            </div> <!-- end col -->
        </div>
    </div>

    <x-alert />
@endsection
