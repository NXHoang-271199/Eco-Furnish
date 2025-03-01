@extends('layouts.admin')

@section('title', 'Danh sách danh mục')

@section('CSS')

@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">CHUYÊN MỤC</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Chuyên Mục Bài Viết</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Thêm danh mục mới</h4>
                </div>
                <div class="card-body">
                    <form id="categoryForm" action="{{ route('categories.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="name">Tên danh mục</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name') }}" 
                                placeholder="Nhập tên danh mục">
                            @error('name')
                                <div class="invalid-feedback">
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

        <div class="col-lg-8">
            <div class="card" id="category-list">
                <div class="card-header d-flex align-items-center">
                    <h4 class="card-title mb-0 flex-grow-1">Danh sách chuyên mục</h4>
                    <div class="flex-shrink-0">
                        <a href="/admin/trash/trash-categories" class="btn btn-soft-danger btn-icon btn-sm fs-16" 
                           data-bs-toggle="tooltip" data-bs-placement="top" title="Thùng rác">
                            <i class="ri-delete-bin-line"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover table-nowrap align-middle mb-0">
                            <thead>
                                <tr class="text-muted text-uppercase">
                                    <th scope="col">STT</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                <tr>
                                    <td>{{ ($categories->currentPage() - 1) * $categories->perPage() + $loop->iteration }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>
                                        <div class="hstack gap-3 fs-15">
                                            <a href="{{ route('categories.edit', $category->id) }}" class="link-primary">
                                                <i class="ri-pencil-fill align-bottom me-2"></i>
                                            </a>
                                            <a href="javascript:void(0);" class="link-danger delete-item" data-id="{{ $category->id }}">
                                                <i class="ri-delete-bin-fill align-bottom"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('JS')
    @include('partials.category.index_js')
@endsection 