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
                    <h3 class="card-title">Danh sách sản phẩm đã xóa</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Mã sản phẩm</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Ngày xóa</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                <tr>
                                    <td>{{ $product->id }}</td>
                                    <td>{{ $product->product_code }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ number_format($product->price) }} VNĐ</td>
                                    <td>{{ $product->deleted_at->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-success btn-sm" onclick="confirmRestore({{ $product->id }})">
                                                <i class="fas fa-trash-restore"></i> Khôi phục
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmForceDelete({{ $product->id }})">
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
                        {{ $products->links() }}
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