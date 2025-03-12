@extends('layouts.admin')

@section('title', 'Quản lý giá trị biến thể')

@section('CSS')
    <link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">QUẢN LÝ GIÁ TRỊ BIẾN THỂ</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('variants.index') }}">Biến thể</a></li>
                        <li class="breadcrumb-item active">Giá trị biến thể</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Thêm giá trị biến thể mới</h4>
                </div>
                <div class="card-body">
                    <form id="createVariantValueForm" action="{{ route('variants.values.store', $variant) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="value">Giá trị</label>
                            <input type="text" class="form-control @error('value') is-invalid @enderror"
                                id="value" name="value" value="{{ old('value') }}"
                                placeholder="VD: Đỏ, XL, 512GB">
                            @error('value')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="text-start">
                            <button type="submit" class="btn btn-success w-sm">
                                <i class="ri-add-line align-bottom me-1"></i> Thêm giá trị
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card" id="variant-value-list">
                <div class="card-header d-flex align-items-center">
                    <h4 class="card-title mb-0 flex-grow-1">Danh sách giá trị của: {{ $variant->name }}</h4>
                    <div class="flex-shrink-0">
                        <a href="{{ route('variants.index') }}" class="btn btn-soft-primary btn-sm me-2">
                            <i class="ri-arrow-left-line align-bottom"></i> Quay lại
                        </a>
                        <a href="/admin/trash/trash-variant-values" class="btn btn-soft-danger btn-icon btn-sm fs-16"
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
                                    <th scope="col">Giá trị</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($values as $value)
                                <tr>
                                    <td>{{ ($values->currentPage() - 1) * $values->perPage() + $loop->iteration }}</td>
                                    <td class="edit-value" data-id="{{ $value->id }}">{{ $value->value }}</td>
                                    <td>
                                        <div class="hstack gap-3 fs-15">
                                            <a href="javascript:void(0);" class="btn btn-soft-warning btn-sm edit-trigger" data-id="{{ $value->id }}" title="Chỉnh sửa">
                                                <i class="ri-pencil-fill align-bottom"></i> Sửa
                                            </a>
                                            <a href="javascript:void(0);" class="btn btn-soft-danger btn-sm delete-item" data-id="{{ $value->id }}" title="Xóa">
                                                <i class="ri-delete-bin-fill align-bottom"></i> Xóa
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($values->hasPages())
                        <div class="d-flex justify-content-end mt-3">
                            {{ $values->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('JS')
    @include('partials.variant-values.index_js')
@endsection
