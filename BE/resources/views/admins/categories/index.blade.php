@extends('layouts.admin')

@section('title', 'Quản lý Danh mục Sản phẩm')

@section('CSS')
{{-- Thêm CSS cho select multiple nếu cần (ví dụ: thư viện choices.js) --}}
{{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" /> --}}
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">DANH MỤC SẢN PHẨM</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Danh mục sản phẩm</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4" id="addCategoryBlock">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Thêm danh mục mới</h4>
                </div>
                <div class="card-body">
                    <form id="categoryForm" action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="name">Tên danh mục</label>
                            <input type="text" class="form-control @error('name', 'store') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name') }}"
                                placeholder="Nhập tên danh mục">
                            @error('name', 'store')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="spaces" class="form-label">Không gian</label>
                            {{-- Sử dụng select multiple --}}
                            <select class="form-select @error('spaces', 'store') is-invalid @enderror" id="spaces" name="spaces[]" multiple data-choices data-choices-removeItem> {{-- Thêm name="spaces[]" và multiple --}}
                                {{-- <option value="">-- Chọn không gian --</option> --}} {{-- Bỏ option mặc định cho multiple --}}
                                @foreach($spaceTypes as $key => $value)
                                    {{-- Kiểm tra old('spaces') phải là mảng --}}
                                    <option value="{{ $key }}" {{ (is_array(old('spaces')) && in_array($key, old('spaces'))) ? 'selected' : '' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                            {{-- Hiển thị lỗi chung cho mảng và lỗi cho từng phần tử --}}
                             @error('spaces', 'store')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                             @error('spaces.*', 'store')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="text-start">
                            <button type="submit" class="btn btn-success w-sm">
                                <i class="ri-add-line align-bottom me-1"></i> Thêm danh mục
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4" id="editCategoryBlock" style="display: none;">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Chỉnh sửa danh mục</h4>
                </div>
                <div class="card-body">
                    <form id="editCategoryForm" action="" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" id="edit_category_id" name="edit_category_id">
                        <div class="mb-3">
                            <label class="form-label" for="edit_name">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name', 'update') is-invalid @enderror" id="edit_name" name="name" value="{{ old('name') }}" required>
                            @error('name', 'update')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="edit_spaces" class="form-label">Không gian</label>
                            <select class="form-select @error('spaces', 'update') is-invalid @enderror" id="edit_spaces" name="spaces[]" multiple data-choices data-choices-removeItem>
                                @foreach($spaceTypes as $key => $value)
                                    {{-- JS sẽ xử lý việc chọn các option này --}}
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                             @error('spaces', 'update')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                             @error('spaces.*', 'update')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="text-end mb-3">
                            <button type="submit" class="btn btn-primary w-sm">Cập nhật</button>
                            <button type="button" class="btn btn-secondary w-sm" id="cancelEditBtn">Hủy</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card" id="category-list">
                <div class="card-header d-flex align-items-center">
                    <h4 class="card-title mb-0 flex-grow-1">Danh sách danh mục</h4>
                    <div class="flex-shrink-0">
                        <a href="/admin/trash/trash-categories" class="btn btn-soft-danger btn-icon btn-sm fs-16"
                           data-bs-toggle="tooltip" data-bs-placement="top" title="Thùng rác">
                            <i class="ri-delete-bin-line"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->update->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->update->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($errors->store->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->store->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap align-middle mb-0">
                            <thead>
                                <tr class="text-muted text-uppercase">
                                    <th scope="col">STT</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Không gian</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                @php
                                    // Lấy danh sách tên không gian cho category này
                                    $categorySpaceNames = collect($category->spaceKeys)
                                        ->map(fn($key) => $spaceTypes[$key] ?? null)
                                        ->filter()
                                        ->implode(', '); // Nối bằng dấu phẩy
                                @endphp
                                <tr>
                                    <td>{{ ($categories->currentPage() - 1) * $categories->perPage() + $loop->iteration }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $categorySpaceNames ?: 'Chưa phân loại' }}</td>
                                    <td>
                                        <div class="hstack gap-3 fs-15">
                                            <a href="javascript:void(0);" class="link-primary edit-trigger" data-id="{{ $category->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Sửa">
                                                <i class="ri-pencil-fill align-bottom me-2"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="link-danger delete-item" data-id="{{ $category->id }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Xóa">
                                                <i class="ri-delete-bin-fill align-bottom"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                         {{ $categories->links('vendor.pagination.bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('JS')
{{-- Thêm JS cho select multiple nếu cần (ví dụ: thư viện choices.js) --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script> --}}
{{-- <script>
    document.addEventListener('DOMContentLoaded', function () {
      var genericExamples = document.querySelectorAll('[data-choices]');
      genericExamples.forEach(function (genericExample) {
        new Choices(genericExample, {
          removeItemButton: genericExample.hasAttribute('data-choices-removeItem'),
        });
      });
    });
</script> --}}
    @include('partials.category.index_js')
@endsection
