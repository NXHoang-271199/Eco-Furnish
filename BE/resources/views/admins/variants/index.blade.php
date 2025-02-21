@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách biến thể</h3>
                    <div class="card-tools">
                        <a href="{{ route('variants.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm biến thể
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 10px">#</th>
                                    <th>Tên biến thể</th>
                                    <th>Giá trị</th>
                                    <th style="width: 200px">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($variants as $variant)
                                    <tr>
                                        <td>{{ ($variants->currentPage() - 1) * $variants->perPage() + $loop->iteration }}</td>
                                        <td>{{ $variant->name }}</td>
                                        <td>
                                            @if($variant->values->count() > 0)
                                                @foreach($variant->values as $value)
                                                    <span class="badge bg-info me-1">{{ $value->value }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">Chưa có giá trị</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('variants.values.index', $variant) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-list"></i> Giá trị
                                            </a>
                                            <a href="{{ route('variants.edit', $variant) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-edit"></i> Sửa
                                            </a>
                                            <form action="{{ route('variants.destroy', $variant) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa?')">
                                                    <i class="fas fa-trash"></i> Xóa
                                                </button>
                                            </form>
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

@section('css')
<style>
.badge {
    padding: 5px 10px;
    font-size: 12px;
    border-radius: 4px;
}
.badge.bg-info {
    background-color: #17a2b8;
    color: white;
}
.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}
.me-1 {
    margin-right: 0.25rem;
}
</style>
@endsection 