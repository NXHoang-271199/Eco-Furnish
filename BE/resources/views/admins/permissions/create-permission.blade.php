@extends('layouts.admin')

@section('title', 'Thêm quyền mới')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Thêm quyền mới</h5>
                    <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('admin.permissions.store-permission') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Tên quyền <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">Slug <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" value="{{ old('slug') }}" required>
                            <small class="form-text text-muted">Slug chỉ nên chứa chữ thường, số và dấu gạch ngang (ví dụ: view-posts, create-users).</small>
                            @error('slug')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="model" class="form-label">Model liên quan</label>
                            <input type="text" class="form-control @error('model') is-invalid @enderror" id="model" name="model" value="{{ old('model') }}">
                            <small class="form-text text-muted">Nhập tên đầy đủ của model (ví dụ: App\Models\Post). Để trống nếu không liên quan đến model cụ thể.</small>
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Lưu quyền
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tự động tạo slug từ tên
    document.getElementById('name').addEventListener('input', function() {
        const name = this.value;
        const slug = name.toLowerCase()
            .replace(/đ/g, 'd')
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
        document.getElementById('slug').value = slug;
    });
</script>
@endpush 