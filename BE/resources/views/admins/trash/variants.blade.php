@extends('layouts.admin')
@section('title', 'Thùng rác biến thể')

@section('CSS')

@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách biến thể đã xóa</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên biến thể</th>
                                    <th>Ngày xóa</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($variants as $variant)
                                <tr>
                                    <td>{{ $variant->id }}</td>
                                    <td>{{ $variant->name }}</td>
                                    <td>{{ $variant->deleted_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-success btn-sm" onclick="confirmRestore({{ $variant->id }})">
                                                <i class="fas fa-trash-restore"></i> Khôi phục
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmForceDelete({{ $variant->id }})">
                                                <i class="fas fa-trash"></i> Xóa vĩnh viễn
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
   @include('partials.trash.TrashVariant_js')
@endsection 