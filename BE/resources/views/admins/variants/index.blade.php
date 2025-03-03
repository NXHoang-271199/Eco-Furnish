@extends('layouts.admin')

@section('title', 'Quản lý biến thể')

@section('CSS')
  
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">QUẢN LÝ BIẾN THỂ</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Biến thể</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Thêm biến thể mới</h4>
                </div>
                <div class="card-body">
                    <form id="variantForm" action="{{ route('variants.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="name">Tên biến thể</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name') }}" 
                                placeholder="VD: Màu sắc, Kích thước">
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="text-start">
                            <button type="submit" class="btn btn-success w-sm">
                                <i class="ri-add-line align-bottom me-1"></i> Thêm biến thể
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card" id="variant-list">
                <div class="card-header d-flex align-items-center">
                    <h4 class="card-title mb-0 flex-grow-1">Danh sách biến thể</h4>
                    <div class="flex-shrink-0">
                        <a href="/admin/trash/trash-variants" class="btn btn-soft-danger btn-icon btn-sm fs-16" 
                           data-bs-toggle="tooltip" data-bs-placement="top" title="Thùng rác">
                            <i class="ri-delete-bin-line"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
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
                                    <th scope="col">Tên biến thể</th>
                                    <th scope="col">Giá trị</th>
                                    <th scope="col">Action</th>
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
                                        <div class="hstack gap-3 fs-15">
                                            <a href="{{ route('variants.values.index', $variant) }}" class="btn btn-soft-info btn-sm" title="Giá trị">
                                                <i class="ri-list-check-line align-bottom"></i> Giá trị
                                            </a>
                                            <a href="{{ route('variants.edit', $variant) }}" class="btn btn-soft-warning btn-sm" title="Chỉnh sửa">
                                                <i class="ri-pencil-fill align-bottom"></i> Sửa
                                            </a>
                                            <a href="javascript:void(0);" class="btn btn-soft-danger btn-sm delete-item" data-id="{{ $variant->id }}" title="Xóa">
                                                <i class="ri-delete-bin-fill align-bottom"></i> Xóa
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($variants->hasPages())
                        <div class="d-flex justify-content-end mt-3">
                            {{ $variants->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('JS')
    @include('partials.variant.index_js')
@endsection

