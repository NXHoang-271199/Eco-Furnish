@extends('layouts.admin')
@section('title', 'Thùng rác sản phẩm')

@section('CSS')

@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Danh sách sản phẩm đã xóa</h3>
                        <a href="{{ route('products.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>STT</th>
                                    <th>Mã sản phẩm</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Ngày xóa</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                <tr>
                                    <td>{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                                    <td>{{ $item->product_code }}</td>
                                    <td>{{ $item->name }}</td>
                                    <td>{{ number_format($item->price) }} VNĐ</td>
                                    <td>{{ Carbon\Carbon::parse($item->deleted_at)->setTimezone('Asia/Ho_Chi_Minh')->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-success btn-sm" onclick="confirmRestore({{ $item->id }})">
                                                <i class="fas fa-trash-restore"></i> Khôi phục
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmForceDelete({{ $item->id }})">
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
                        {{ $items->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('JS')
    @include('partials.trash.TrashProduct_js')
@endsection 