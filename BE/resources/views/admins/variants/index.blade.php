@extends('layouts.admin')

@section('title', 'Danh sách biến thể')

@section('CSS')

@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Biến thể</h4>
                <div class="page-title-right d-flex gap-2">
                    <div class="d-flex gap-1">
                        <a href="{{ route('trash.variants') }}" class="btn btn-warning">
                            <i class="ri-delete-bin-line align-bottom me-1"></i> Thùng rác
                        </a>
                        <a href="{{ route('variants.create') }}" class="btn btn-success">
                            <i class="ri-add-line align-bottom me-1"></i> Thêm biến thể
                        </a>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách biến thể</h3>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên biến thể</th>
                                    <th>Giá trị</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($variants as $variant)
                                <tr>
                                    <td>{{ $variant->id }}</td>
                                    <td>{{ $variant->name }}</td>
                                    <td>
                                        @foreach($variant->values as $value)
                                            <span class="badge bg-info me-1">{{ $value->value }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('variants.values.index', $variant) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-list"></i> Giá trị
                                            </a>
                                            <a href="{{ route('variants.edit', $variant) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Sửa
                                            </a>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete({{ $variant->id }})">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        {{ $variants->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('JS')
   @include('partials.variant.index_js')
@endsection

