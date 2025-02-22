@extends('layouts.admin')

@section('title', 'Danh sách biến thể')

@section('CSS')
  
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
        
            <div class="card">
                <div class="card-header border-0">
                    <div class="row g-4 align-items-center">
                        <div class="col">
                            <div class="d-flex">
                                <a href="{{ route('variants.create') }}" class="btn btn-success">
                                    <i class="ri-add-line align-bottom me-1"></i> Thêm biến thể
                                </a>
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="/admin/trash/trash-variants" class="btn btn-soft-danger btn-icon btn-sm fs-16" 
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

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Tên biến thể</th>
                                    <th>Giá trị</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($variants as $variant)
                                <tr>
                                    <td>{{ ($variants->currentPage() - 1) * $variants->perPage() + $loop->iteration }}</td>
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

