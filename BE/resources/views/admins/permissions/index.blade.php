@extends('layouts.admin')

@section('title', 'Quản lý phân quyền')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Quản lý vai trò</h5>
                    <a href="{{ route('admin.permissions.create-role') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm vai trò
                    </a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên</th>
                                    <th>Slug</th>
                                    <th>Số lượng quyền</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>{{ $role->slug }}</td>
                                    <td>{{ $role->permissions_count }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.permissions.role', $role) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Chi tiết
                                            </a>
                                            
                                            @if($role->slug !== 'admin')
                                            <form action="{{ route('admin.permissions.destroy-role', $role) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa vai trò này?')">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">Không có vai trò nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Quản lý quyền</h5>
                    <a href="{{ route('admin.permissions.create-permission') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm quyền
                    </a>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên</th>
                                    <th>Slug</th>
                                    <th>Model</th>
                                    <th>Số lượng vai trò</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->id }}</td>
                                    <td>{{ $permission->name }}</td>
                                    <td>{{ $permission->slug }}</td>
                                    <td>{{ $permission->model ? \App\Helpers\ModelHelper::getFriendlyModelName($permission->model) : 'N/A' }}</td>
                                    <td>{{ $permission->roles_count }}</td>
                                    <td>
                                        <form action="{{ route('admin.permissions.destroy-permission', $permission) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa quyền này?')">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Không có quyền nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 