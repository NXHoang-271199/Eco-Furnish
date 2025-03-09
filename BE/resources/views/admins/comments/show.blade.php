@extends('layouts.admin')

@section('title', 'Chi tiết bình luận')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Chi tiết bình luận #{{ $comment->id }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('comments.index') }}" class="btn btn-sm btn-default">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ID:</label>
                                <p>{{ $comment->id }}</p>
                            </div>
                            <div class="form-group">
                                <label>Người dùng:</label>
                                <p>{{ $comment->user->name }} (ID: {{ $comment->user_id }})</p>
                            </div>
                            <div class="form-group">
                                <label>Email:</label>
                                <p>{{ $comment->user->email }}</p>
                            </div>
                            <div class="form-group">
                                <label>Sản phẩm:</label>
                                <p>{{ $comment->product->name }} (ID: {{ $comment->product_id }})</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Trạng thái:</label>
                                <p>
                                    <span class="badge {{ $comment->status === 'Hiển thị' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $comment->status }}
                                    </span>
                                </p>
                            </div>
                            <div class="form-group">
                                <label>Ngày tạo:</label>
                                <p>{{ $comment->created_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                            <div class="form-group">
                                <label>Ngày cập nhật:</label>
                                <p>{{ $comment->updated_at->format('d/m/Y H:i:s') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Nội dung bình luận:</label>
                                <div class="p-3 bg-light rounded">
                                    {{ $comment->content }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <div class="btn-group">
                        <form action="{{ route('comments.toggle-status', $comment->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn {{ $comment->status === 'Hiển thị' ? 'btn-warning' : 'btn-success' }}">
                                <i class="fas {{ $comment->status === 'Hiển thị' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                {{ $comment->status === 'Hiển thị' ? 'Ẩn bình luận' : 'Hiển thị bình luận' }}
                            </button>
                        </form>
                        <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bình luận này?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash"></i> Xóa bình luận
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection