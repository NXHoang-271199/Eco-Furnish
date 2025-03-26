@extends('layouts.admin')

@section('title', 'Quản lý phân quyền')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Quản lý phân quyền</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card ribbon-box border shadow-none">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Danh sách vai trò</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th scope="col">Vai trò</th>
                                    <th scope="col" class="text-center">Quyền</th>
                                    <th scope="col" class="text-end">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($roles as $role)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <h6 class="mb-0">{{ $role->name }}</h6>
                                                <small class="text-muted">{{ $role->slug }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success rounded-pill">{{ $role->permissions_count }}</span>
                                    </td>
                                    <td>
                                        <div class="hstack gap-2 justify-content-end">
                                            <a href="{{ route('admin.permissions.role', $role) }}" 
                                               class="btn btn-soft-info btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               data-bs-placement="top" 
                                               title="Chi tiết">
                                                <i class="ri-settings-4-line align-bottom"></i>
                                            </a>
                                            @if($role->slug !== 'admin')
                                            <button type="button" 
                                                    class="btn btn-soft-danger btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteRole{{ $role->id }}"
                                                    title="Xóa">
                                                <i class="ri-delete-bin-line align-bottom"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Không có vai trò nào.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card ribbon-box border shadow-none">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-0">Danh sách quyền</h5>
                        </div>
                        <div class="flex-shrink-0">
                            <a href="{{ route('admin.permissions.create-permission') }}" class="btn btn-soft-success btn-sm">
                                <i class="ri-add-circle-line me-1 align-bottom"></i> Thêm quyền
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        $groupedPermissions = $permissions->groupBy('model');
                    @endphp

                    <div class="accordion custom-accordionwithicon" id="permissionsAccordion">
                        @foreach ($groupedPermissions as $model => $modelPermissions)
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading{{ Str::slug($model) }}">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse{{ Str::slug($model) }}">
                                    <i class="ri-shield-keyhole-line me-2"></i>
                                    {{ $model ? \App\Helpers\ModelHelper::getFriendlyModelName($model) : 'Quyền chung' }}
                                </button>
                            </h2>
                            <div id="collapse{{ Str::slug($model) }}" class="accordion-collapse collapse show"
                                 data-bs-parent="#permissionsAccordion">
                                <div class="accordion-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-borderless align-middle mb-0">
                                            <tbody>
                                            @foreach ($modelPermissions as $permission)
                                                <tr>
                                                    <td style="width: 50%;">
                                                        <h6 class="mb-0">{{ $permission->name }}</h6>
                                                        <small class="text-muted">{{ $permission->slug }}</small>
                                                    </td>
                                                    <td class="text-center" style="width: 20%;">
                                                        <span class="badge bg-soft-primary rounded-pill">
                                                            {{ $permission->roles_count }} vai trò
                                                        </span>
                                                    </td>
                                                    <td class="text-end" style="width: 30%;">
                                                        <button type="button" 
                                                                class="btn btn-soft-danger btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#deletePermission{{ $permission->id }}">
                                                            <i class="ri-delete-bin-line align-bottom"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        {{ $permissions->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Delete Role --}}
@foreach ($roles as $role)
@if($role->slug !== 'admin')
<div class="modal fade" id="deleteRole{{ $role->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-0">Bạn có chắc chắn muốn xóa vai trò <strong>{{ $role->name }}</strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <form action="{{ route('admin.permissions.destroy-role', $role) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach

{{-- Modal Delete Permission --}}
@foreach ($permissions as $permission)
<div class="modal fade" id="deletePermission{{ $permission->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-0">Bạn có chắc chắn muốn xóa quyền <strong>{{ $permission->name }}</strong>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy</button>
                <form action="{{ route('admin.permissions.destroy-permission', $permission) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection