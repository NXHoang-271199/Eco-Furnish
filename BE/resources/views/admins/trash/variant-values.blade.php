@extends('layouts.admin')
@section('title', 'Thùng rác giá trị biến thể')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách giá trị biến thể đã xóa</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên giá trị</th>
                                    <th>Biến thể</th>
                                    <th>Ngày xóa</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($variantValues as $value)
                                <tr>
                                    <td>{{ $value->id }}</td>
                                    <td>{{ $value->value }}</td>
                                    <td>{{ $value->variant->name }}</td>
                                    <td>{{ $value->deleted_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" onclick="confirmRestore({{ $value->id }})">
                                            <i class="fas fa-trash-restore"></i> Khôi phục
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmForceDelete({{ $value->id }})">
                                            <i class="fas fa-trash"></i> Xóa vĩnh viễn
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $variantValues->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('JS')
    @include('partials.trash.TrashVariantValue_js')
@endsection 