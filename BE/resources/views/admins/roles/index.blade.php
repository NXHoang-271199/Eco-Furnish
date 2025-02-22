@extends('layouts.admin')

@section('title')
    Quản lý vai trò
@endsection

@section('CSS')
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Danh sách vai trò</h4>
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
        <!-- Roles View - Card Grid -->
        <div class="card">

            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Danh sách vai trò</h5>
                <form action="" method="POST">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                        <i class="ri-add-line me-1"></i> Thêm Vai Trò
                    </button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Tên vai trò</th>
                                <th>Số người dùng</th>
                                <th>Ngày tạo</th>
                                <th class="text-end">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($listRole as $role)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0"><a href="{{ route('roles.show', $role->id) }}" class="text-reset">{{ $role->name }}</a></h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $role->users()->count() }}</td>
                                    <td>{{ $role->created_at }}</td>
                                    <td class="text-end">
                                        <button class="btn btn-soft-info btn-sm"><i class="ri-eye-line"></i></button>
                                        <button class="btn btn-soft-warning btn-sm"><i class="ri-edit-line"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
