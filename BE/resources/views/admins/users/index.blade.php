@extends('layouts.admin')

@section('title')
    Quản lý người dùng
@endsection
@section('JS')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Admin</a></li>
                            <li class="breadcrumb-item active">Người dùng</li>
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

                        @if (session('success'))
                            <div class="alert alert-secondary alert-border-left alert-dismissible fade show material-shadow"
                                role="alert">
                                <i class="ri-check-double-line me-3 align-middle"></i> <strong>{{ session('success') }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-warning alert-border-left alert-dismissible fade show material-shadow" role="alert">
                                <i class="ri-alert-line me-3 align-middle"></i> <strong>{{ session('error') }}</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

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
                                                                <th scope="col" style="width: 17.1778px;"
                                                                    class="sorting sorting_asc " tabindex="0"
                                                                    aria-controls="example" rowspan="1" colspan="1"
                                                                    aria-sort="ascending"
                                                                    aria-label=": activate to sort column descending">
                                                                    <div class="form-check text-center">
                                                                        <input class="form-check-input fs-15"
                                                                            type="checkbox" id="checkAll" value="option">
                                                                    </div>
                                                                </th>
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
                                                                    style="width: 250px;"
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
                                                                    Status</th>
                                                                <th class="sorting" tabindex="0"
                                                                    aria-controls="example" rowspan="1" colspan="1"
                                                                    style="width: 70.2889px;"
                                                                    aria-label="Action: activate to sort column ascending">
                                                                    Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach ($listUsers as $key => $user)
                                                                <tr class="odd">
                                                                    <th scope="row" class="dtr-control sorting_1"
                                                                        tabindex="0">
                                                                        <div class="form-check text-center">
                                                                            <input class="form-check-input fs-15"
                                                                                type="checkbox" name="checkAll"
                                                                                value="option1">
                                                                        </div>
                                                                    </th>
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
                                                                            class="badge bg-danger">{{ $user->role->name }}</span>
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
                                                                                        method="POST" class="d-inline"
                                                                                        onclick="return confirm('Muốn xóa không ?')">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button
                                                                                            class="dropdown-item edit-item-btn cursor-pointer"><i
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
@endsection
