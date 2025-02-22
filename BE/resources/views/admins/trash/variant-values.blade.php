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
                                    <th>STT</th>
                                    <th>Tên giá trị</th>
                                    <th>Biến thể</th>
                                    <th>Ngày xóa</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                <tr>
                                    <td>{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                                    <td>{{ $item->value }}</td>
                                    <td>{{ $item->variant->name }}</td>
                                    <td>{{ Carbon\Carbon::parse($item->deleted_at)->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <button type="button" class="btn btn-success btn-sm" onclick="confirmRestore({{ $item->id }})">
                                            <i class="fas fa-trash-restore"></i> Khôi phục
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmForceDelete({{ $item->id }})">
                                            <i class="fas fa-trash"></i> Xóa vĩnh viễn
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $items->links() }}
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