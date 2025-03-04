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

                    <form action="{{ route('admin.permissions.update-role-permissions', $role) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <h5 class="mb-3">Quyền của vai trò</h5>

                        <div class="row">
                            @foreach($allPermissions->groupBy(function($permission) {
                                return $permission->model ?? 'General';
                            }) as $group => $permissions)
                                <div class="col-md-6 mb-4">
                                    <div class="card">
                                        <div class="card-header">
                                            <h6 class="mb-0">{{ \App\Helpers\ModelHelper::getFriendlyModelName($group) }}</h6>
                                        </div>
                                        <div class="card-body">
                                            @foreach($permissions as $permission)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                        name="permissions[]" 
                                                        value="{{ $permission->id }}" 
                                                        id="permission-{{ $permission->id }}"
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