@extends('layouts.admin')

@section('title')
    Chỉnh sửa phương thức thanh toán
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h5>Form chỉnh sửa phương thức thanh toán</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('payment-methods.update', $method->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="" class="form-label">Ảnh</label>
                        <input type="file" class="form-control @error('image') is-invalid @enderror" name="image">
                        @error('image')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                        @if ($method->image)
                            <img src="{{ Storage::url($method->image) }}" class="img-thumbnail mt-1"
                                alt="Ảnh phương thức thanh toán" width="100px">
                        @endif
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Tên phương thức</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                            value="{{ $method->name }}" name="name">
                        @error('name')
                            <p class="text-danger">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                    <a href="{{ route('payment-methods.index') }}" class="btn btn-secondary">Hủy</a>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('JS')
@endsection
