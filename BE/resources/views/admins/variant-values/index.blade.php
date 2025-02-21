@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách giá trị biến thể: {{ $variant->name }}</h3>
                    <div class="card-tools">
                        <div class="d-flex gap-1">
                            <a href="{{ route('trash.variant-values') }}" class="btn btn-warning">
                                <i class="ri-delete-bin-line align-bottom me-1"></i> Thùng rác
                            </a>
                            <a href="{{ route('variants.values.create', $variant->id) }}" class="btn btn-success">
                                <i class="ri-add-line align-bottom me-1"></i> Thêm giá trị
                            </a>
                        </div>
                        <a href="{{ route('variants.index') }}" class="btn btn-default">
                            <i class="fas fa-arrow-left"></i> Quay lại
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