@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header border-0">
                    <div class="row g-4 align-items-center">
                        <div class="col">
                            <div class="d-flex">
                                <a href="{{ route('variants.values.create', $variant) }}" class="btn btn-success">
                                    <i class="ri-add-line align-bottom me-1"></i> Thêm giá trị biến thể
                                </a>
                            </div>
                        </div>
                        <div class="col-auto">
                            <a href="/admin/trash/trash-variant-values" class="btn btn-soft-danger btn-icon btn-sm fs-16" 
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

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 10px">#</th>
                                <th>Giá trị</th>
                                <th style="width: 150px">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($values as $value)
                                <tr>
                                    <td>{{ ($values->currentPage() - 1) * $values->perPage() + $loop->iteration }}</td>
                                    <td>{{ $value->value }}</td>
                                    <td>
                                        <a href="{{ route('variants.values.edit', [$variant, $value]) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm delete-item" data-id="{{ $value->id }}">
                                            <i class="fas fa-trash"></i> Xóa
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-3">
                        {{ $values->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('CSS')

@endsection

@section('JS')
    @include('partials.variant-values.index_js')
@endsection 