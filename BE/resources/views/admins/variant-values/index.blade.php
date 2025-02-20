@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách giá trị biến thể: {{ $variant->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('variants.values.create', $variant) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm giá trị
                        </a>
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
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $value->value }}</td>
                                    <td>
                                        <a href="{{ route('variants.values.edit', [$variant, $value]) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <form action="{{ route('variants.values.destroy', [$variant, $value]) }}" method="POST" class="d-inline">
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
            </div>
        </div>
    </div>
</div>
@endsection 