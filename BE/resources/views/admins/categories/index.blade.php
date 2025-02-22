@extends('layouts.admin')

@section('title', 'Danh sách danh mục')

@section('CSS')

@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Danh mục</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Danh mục</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row g-4 align-items-center">
                        <div class="col">
                            <div class="d-flex">
                                <a href="{{ route('categories.create') }}" class="btn btn-success">
                                    <i class="ri-add-line align-bottom me-1"></i> Thêm danh mục
                                </a>
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="/admin/trash/trash-categories" class="btn btn-soft-danger btn-icon btn-sm fs-16"
                               data-bs-toggle="tooltip" data-bs-placement="top" title="Thùng rác">
                                <i class="ri-delete-bin-line"></i>
                            </a>
                        </div>
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
                                    <th scope="col">Tên danh mục</th>
                                    <th scope="col">Slug</th>
                                    <th scope="col" style="width: 150px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                <tr>
                                    <td>{{ ($categories->currentPage() - 1) * $categories->perPage() + $loop->iteration }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->slug }}</td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ri-more-fill align-middle"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <a href="{{ route('categories.edit', $category->id) }}" class="dropdown-item">
                                                        <i class="ri-pencil-fill align-bottom me-2 text-muted"></i> Sửa
                                                    </a>
                                                </li>
                                                <li class="dropdown-divider"></li>
                                                <li>
                                                    <a href="javascript:void(0);" class="dropdown-item text-danger delete-item" data-id="{{ $category->id }}">
                                                        <i class="ri-delete-bin-fill align-bottom me-2 text-danger"></i> Xóa
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="row align-items-center mt-4 py-3 px-2">
                        <div class="col-md-5">
                            <div class="text-muted">
                                Showing
                                <span class="fw-semibold">{{ $categories->firstItem() }}</span>
                                to
                                <span class="fw-semibold">{{ $categories->lastItem() }}</span>
                                of
                                <span class="fw-semibold">{{ $categories->total() }}</span>
                                Results
                            </div>
                        </div>
                        <div class="col-sm-auto ms-auto">
                            <nav class="pagination-outer">
                                <ul class="pagination">
                                    <li class="page-item {{ !$categories->onFirstPage() ? '' : 'disabled' }}">
                                        <a class="page-link" href="{{ $categories->previousPageUrl() }}" aria-label="Previous">
                                            <i class="ri-arrow-left-s-line"></i>
                                        </a>
                                    </li>
                                    @foreach ($categories->getUrlRange(1, $categories->lastPage()) as $page => $url)
                                        <li class="page-item {{ $categories->currentPage() == $page ? 'active' : '' }}">
                                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                        </li>
                                    @endforeach
                                    <li class="page-item {{ $categories->hasMorePages() ? '' : 'disabled' }}">
                                        <a class="page-link" href="{{ $categories->nextPageUrl() }}" aria-label="Next">
                                            <i class="ri-arrow-right-s-line"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('JS')
   @include('partials.category.index_js')
@endsection
