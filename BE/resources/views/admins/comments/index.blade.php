@extends('layouts.admin')

@section('title', 'Quản lý bình luận')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Danh sách bình luận</h3>
                    <div class="card-tools">
                        <form action="{{ route('comments.index') }}" method="GET" class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" class="form-control float-right" placeholder="Tìm kiếm" value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Người dùng</th>
                                <th>Sản phẩm</th>
                                <th>Nội dung</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($comments as $comment)
                            <tr>
                                <td>{{ $comment->id }}</td>
                                <td>{{ $comment->user->name }}</td>
                                <td>{{ $comment->product->name }}</td>
                                <td>{{ Str::limit($comment->content, 50) }}</td>
                                <td>
                                    <span class="badge {{ $comment->status === 'Hiển thị' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $comment->status }}
                                    </span>
                                </td>
                                <td>{{ $comment->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('comments.show', $comment->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Chi tiết
                                        </a>
                                        <form action="{{ route('comments.toggle-status', $comment->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $comment->status === 'Hiển thị' ? 'btn-warning' : 'btn-success' }}">
                                                <i class="fas {{ $comment->status === 'Hiển thị' ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                                {{ $comment->status === 'Hiển thị' ? 'Ẩn' : 'Hiển thị' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bình luận này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i> Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Không có bình luận nào</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
                <div class="card-footer clearfix">
                    {{ $comments->links() }}
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</div>
@endsection 