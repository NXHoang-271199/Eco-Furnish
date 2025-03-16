@extends('layouts.admin')

@section('title', 'Chi tiết vai trò: ' . $role->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Chi tiết vai trò: {{ $role->name }}</h5>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                <div class="card-body">
                    <form id="permissions-form" action="{{ route('admin.permissions.update-role-permissions', $role) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            @foreach($allPermissions->groupBy(function($permission) {
                                return $permission->model ?? 'General';
                            }) as $group => $permissions)
                                <div class="col-md-6 mb-4">
                                    <div class="card permission-section">
                                        <div class="card-header d-flex align-items-center">
                                            <div class="form-check mb-0">
                                                <input type="checkbox"
                                                       class="form-check-input parent-checkbox"
                                                       id="parent-{{ Str::slug($group) }}">
                                                <label class="form-check-label" for="parent-{{ Str::slug($group) }}">
                                                    <h6 class="mb-0">{{ \App\Helpers\ModelHelper::getFriendlyModelName($group) }}</h6>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="card-body permission-group" data-group="{{ $group }}">
                                            @foreach($permissions as $permission)
                                                @php
                                                    $showPermission = true;
                                                    // Chỉ hiển thị quyền khôi phục cho các model có soft delete
                                                    if (str_contains($permission->slug, 'restore-')) {
                                                        $modelName = str_replace('restore-', '', $permission->slug);
                                                        $showPermission = in_array($modelName, [
                                                            'posts',
                                                            'products'
                                                        ]);
                                                    }
                                                @endphp

                                                @if($showPermission)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input permission-checkbox"
                                                               type="checkbox"
                                                               name="permissions[]"
                                                               value="{{ $permission->id }}"
                                                               id="permission-{{ $permission->id }}"
                                                               data-slug="{{ $permission->slug }}"
                                                               {{ $role->permissions->contains($permission->id) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="permission-{{ $permission->id }}">
                                                            {{ $permission->name }}
                                                            <small class="text-muted">({{ $permission->slug }})</small>
                                                        </label>
                                                    </div>
                                                @endif
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
        const permissionGroups = document.querySelectorAll('.permission-group');
        const form = document.getElementById('permissions-form');

        // Thêm hàm xử lý parent checkbox
        function handleParentCheckbox(parentCheckbox, childrenContainer) {
            // Xử lý khi click vào parent checkbox
            parentCheckbox.addEventListener('change', function() {
                const childCheckboxes = childrenContainer.querySelectorAll('.permission-checkbox');
                childCheckboxes.forEach(checkbox => {
                    checkbox.checked = parentCheckbox.checked;
                });
            });

            // Cập nhật trạng thái parent checkbox dựa trên children
            function updateParentState() {
                const childCheckboxes = childrenContainer.querySelectorAll('.permission-checkbox');
                const checkedCount = childrenContainer.querySelectorAll('.permission-checkbox:checked').length;

                if (checkedCount === 0) {
                    parentCheckbox.checked = false;
                    parentCheckbox.indeterminate = false;
                } else if (checkedCount === childCheckboxes.length) {
                    parentCheckbox.checked = true;
                    parentCheckbox.indeterminate = false;
                } else {
                    parentCheckbox.checked = false;
                    parentCheckbox.indeterminate = true;
                }
            }

            // Thêm event listener cho các child checkboxes
            const childCheckboxes = childrenContainer.querySelectorAll('.permission-checkbox');
            childCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateParentState);
            });

            // Khởi tạo trạng thái ban đầu
            updateParentState();
        }

        // Áp dụng cho mỗi nhóm quyền
        document.querySelectorAll('.permission-section').forEach(section => {
            const parentCheckbox = section.querySelector('.parent-checkbox');
            const childrenContainer = section.querySelector('.permission-group');
            if (parentCheckbox && childrenContainer) {
                handleParentCheckbox(parentCheckbox, childrenContainer);
            }
        });

        // Hàm hiển thị thông báo
        function showAlert(message, type = 'warning') {
            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                className: `bg-${type}`,
                style: {
                    background: `var(--vz-${type})`,
                    color: "#fff",
                    boxShadow: `0 10px 20px -10px var(--vz-${type})`
                }
            }).showToast();
        }

        // Hàm kiểm tra và cập nhật quyền xem
        function updateViewPermission(group) {
            const checkboxes = group.querySelectorAll('.permission-checkbox');
            const resourceTypes = new Map();

            // Thu thập thông tin về checkboxes
            checkboxes.forEach(checkbox => {
                const slug = checkbox.getAttribute('data-slug');
                const [action, type] = slug.split('-');

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

            // Xử lý logic quyền
            resourceTypes.forEach((actions, type) => {
                const viewCheckbox = actions.view;
                const hasOtherPermissions = actions.create?.checked ||
                                          actions.update?.checked ||
                                          actions.delete?.checked ||
                                          actions.restore?.checked;

                // Chỉ tự động check quyền xem khi có quyền khác được chọn
                if (hasOtherPermissions && viewCheckbox && !viewCheckbox.checked) {
                    viewCheckbox.checked = true;
                    showAlert(`Quyền xem ${type} đã được tự động chọn`, 'info');
                }
            });
        }

        // Xử lý sự kiện thay đổi cho checkbox
        permissionGroups.forEach(group => {
            const checkboxes = group.querySelectorAll('.permission-checkbox');

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function(e) {
                    const slug = checkbox.getAttribute('data-slug');
                    const [action, type] = slug.split('-');

                    // Nếu đang thay đổi một quyền không phải quyền xem
                    if (action !== 'view' && checkbox.checked) {
                        // Tìm và check quyền xem tương ứng
                        const viewCheckbox = group.querySelector(`[data-slug="view-${type}"]`);
                        if (viewCheckbox && !viewCheckbox.checked) {
                            viewCheckbox.checked = true;
                            showAlert(`Quyền xem ${type} đã được tự động chọn`, 'info');
                        }
                    }
                });
            });
        });

        // Xử lý submit form
        form.addEventListener('submit', function(e) {
            let hasChanges = false;
            permissionGroups.forEach(group => {
                const checkboxes = group.querySelectorAll('.permission-checkbox');
                checkboxes.forEach(checkbox => {
                    if (checkbox.checked !== checkbox.defaultChecked) {
                        hasChanges = true;
                    }
                });
            });

            if (!hasChanges) {
                e.preventDefault();
                showAlert('Không có thay đổi nào để lưu', 'warning');
            }
        });
    });
</script>
@endsection

@section('CSS')
<style><style>
    .form-check-input:indeterminate {
        background-color: #0d6efd;
        border-color: #0d6efd;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10h8'/%3e%3c/svg%3e");
    }
</style>
@endsection

