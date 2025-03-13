@extends('layouts.admin')

@section('title', 'Chi tiết vai trò: ' . $role->name)

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

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Chi tiết vai trò: {{ $role->name }}</h5>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>

                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> {{ $role->id }}</p>
                            <p><strong>Tên:</strong> {{ $role->name }}</p>
                            <p><strong>Slug:</strong> {{ $role->slug }}</p>
                        </div>
                    </div>

                    <form id="permissions-form" action="{{ route('admin.permissions.update-role-permissions', $role) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3">Quyền của vai trò</h5>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i> Lưu ý: Khi chọn quyền thêm, sửa, xóa thì quyền xem tương ứng sẽ được tự động chọn và không thể bỏ chọn.
                        </div>

                        <div class="row">
                            @foreach($allPermissions->groupBy(function($permission) {
                                return $permission->model ?? 'General';
                            }) as $group => $permissions)
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">{{ \App\Helpers\ModelHelper::getFriendlyModelName($group) }}</h6>
                                        </div>
                                        <div class="card-body permission-group" data-group="{{ $group }}">
                                            @foreach($permissions as $permission)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input permission-checkbox" type="checkbox" 
                                                        name="permissions[]" 
                                                        value="{{ $permission->id }}" 
                                                        id="permission-{{ $permission->id }}"
                                                        data-slug="{{ $permission->slug }}"
                                                        {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                        {{ $permission->name }} <small class="text-muted">({{ $permission->slug }})</small>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('JS')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lấy tất cả các nhóm quyền
        const permissionGroups = document.querySelectorAll('.permission-group');
        const form = document.getElementById('permissions-form');
        
        // Hàm kiểm tra và cập nhật quyền xem
        function updateViewPermission(group) {
            const checkboxes = group.querySelectorAll('.permission-checkbox');
            const resourceTypes = new Map(); // Sử dụng Map để lưu trữ thông tin về mỗi loại tài nguyên
            
            // Duyệt qua tất cả các checkbox để thu thập thông tin
            checkboxes.forEach(checkbox => {
                const slug = checkbox.getAttribute('data-slug');
                let type = '';
                let action = '';
                
                // Xác định loại tài nguyên và hành động
                if (slug.startsWith('view-')) {
                    type = slug.split('-')[1];
                    action = 'view';
                } else if (slug.startsWith('create-')) {
                    type = slug.split('-')[1];
                    action = 'create';
                } else if (slug.startsWith('update-')) {
                    type = slug.split('-')[1];
                    action = 'update';
                } else if (slug.startsWith('delete-')) {
                    type = slug.split('-')[1];
                    action = 'delete';
                } else if (slug.startsWith('restore-')) {
                    type = slug.split('-')[1];
                    action = 'restore';
                }
                
                // Nếu là loại tài nguyên hợp lệ
                if (type) {
                    // Khởi tạo đối tượng cho loại tài nguyên nếu chưa có
                    if (!resourceTypes.has(type)) {
                        resourceTypes.set(type, {
                            view: null,
                            create: null,
                            update: null,
                            delete: null,
                            restore: null
                        });
                    }
                    
                    // Lưu trữ checkbox
                    resourceTypes.get(type)[action] = checkbox;
                }
            });
            
            // Kiểm tra và cập nhật quyền xem cho mỗi loại tài nguyên
            resourceTypes.forEach((actions, type) => {
                const viewCheckbox = actions.view;
                const hasOtherPermissions = actions.create?.checked || 
                                           actions.update?.checked || 
                                           actions.delete?.checked || 
                                           actions.restore?.checked;
                
                // Nếu có quyền khác và có checkbox xem
                if (hasOtherPermissions && viewCheckbox) {
                    // Đánh dấu checkbox xem và vô hiệu hóa nó
                    viewCheckbox.checked = true;
                    
                    // Thêm sự kiện để ngăn người dùng bỏ chọn
                    viewCheckbox.addEventListener('click', function(e) {
                        const stillHasOtherPermissions = actions.create?.checked || 
                                                        actions.update?.checked || 
                                                        actions.delete?.checked || 
                                                        actions.restore?.checked;
                        
                        if (stillHasOtherPermissions) {
                            e.preventDefault();
                            alert(`Không thể bỏ chọn quyền xem khi đã chọn quyền thêm, sửa hoặc xóa cho ${type}.`);
                            return false;
                        }
                    });
                }
            });
        }
        
        // Xử lý cho từng nhóm quyền
        permissionGroups.forEach(group => {
            const checkboxes = group.querySelectorAll('.permission-checkbox');
            
            // Cập nhật ban đầu
            updateViewPermission(group);
            
            // Thêm sự kiện change cho mỗi checkbox
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Cập nhật lại quyền xem sau mỗi thay đổi
                    updateViewPermission(group);
                });
            });
        });
        
        // Kiểm tra trước khi submit form
        form.addEventListener('submit', function(e) {
            let hasError = false;
            
            // Kiểm tra tất cả các nhóm quyền
            permissionGroups.forEach(group => {
                const checkboxes = group.querySelectorAll('.permission-checkbox');
                const resourceTypes = new Map();
                
                // Thu thập thông tin
                checkboxes.forEach(checkbox => {
                    const slug = checkbox.getAttribute('data-slug');
                    let type = '';
                    let action = '';
                    
                    if (slug.startsWith('view-')) {
                        type = slug.split('-')[1];
                        action = 'view';
                    } else if (slug.startsWith('create-')) {
                        type = slug.split('-')[1];
                        action = 'create';
                    } else if (slug.startsWith('update-')) {
                        type = slug.split('-')[1];
                        action = 'update';
                    } else if (slug.startsWith('delete-')) {
                        type = slug.split('-')[1];
                        action = 'delete';
                    } else if (slug.startsWith('restore-')) {
                        type = slug.split('-')[1];
                        action = 'restore';
                    }
                    
                    if (type) {
                        if (!resourceTypes.has(type)) {
                            resourceTypes.set(type, {
                                view: null,
                                create: null,
                                update: null,
                                delete: null,
                                restore: null
                            });
                        }
                        
                        resourceTypes.get(type)[action] = checkbox;
                    }
                });
                
                // Kiểm tra mỗi loại tài nguyên
                resourceTypes.forEach((actions, type) => {
                    const hasView = actions.view?.checked;
                    const hasOtherPermissions = actions.create?.checked || 
                                               actions.update?.checked || 
                                               actions.delete?.checked || 
                                               actions.restore?.checked;
                    
                    // Nếu có quyền khác nhưng không có quyền xem
                    if (hasOtherPermissions && !hasView && actions.view) {
                        hasError = true;
                        actions.view.checked = true; // Tự động chọn quyền xem
                    }
                });
            });
            
            // Nếu có lỗi, hiển thị thông báo
            if (hasError) {
                alert('Một số quyền xem đã được tự động chọn vì bạn đã chọn quyền thêm, sửa hoặc xóa tương ứng.');
            }
        });
    });
</script>
@endsection 