@extends('layouts.admin')

@section('title')
    Quản lý phương thức thanh toán
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Danh sách phương thức thanh toán</h5>
                <a href="{{ route('payment-methods.create') }}" class="btn btn-primary">Thêm phương thức</a>
            </div>
            <div class="card-body">
                {{-- <x-alert /> --}}
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Hình ảnh</th>
                            <th>Tên</th>
                            <th>Trạng thái kết nối</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($methods as $key => $method)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td><img src="{{ Storage::url($method->image) }}" class="img-thumbnail"
                                        alt="ảnh phương thức" width="100px"></td>
                                <td>{{ $method->name }}</td>
                                <td>
                                    @if ($method->is_connected)
                                        <span class="badge bg-success">Đã kết nối</span>
                                    @else
                                        <span class="badge bg-danger">Chưa kết nối</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('payment-methods.edit', $method->id) }}"
                                        class="btn btn-warning btn-sm">Sửa</a>
                                    <form action="{{ route('payment-methods.destroy', $method->id) }}" method="POST"
                                        class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                                    </form>
                                    @if ($method->name !== 'Tiền mặt')
                                        @if (!$method->is_connected)
                                            <button class="btn btn-info btn-sm"
                                                onclick="openConnectModal('{{ route('payment-methods.connect', $method->id) }}')">Kết
                                                nối</button>
                                        @else
                                            <form action="{{ route('payment-methods.disconnect', $method->id) }}"
                                                method="POST" class="d-inline"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn hủy kết nối?');">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-secondary btn-sm">Hủy kết nối</button>
                                            </form>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal kết nối -->
    <div class="modal fade" id="connectModal" tabindex="-1" aria-labelledby="connectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="connectModalLabel">Kết nối phương thức thanh toán</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="connectModalBody">
                    <!-- Nội dung form sẽ được load bằng Ajax -->
                </div>
            </div>
        </div>
    </div>
@endsection
@section('JS')
    <script>
        function openConnectModal(url) {
            fetch(url)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('connectModalBody').innerHTML = html;
                    var connectModal = new bootstrap.Modal(document.getElementById('connectModal'));
                    connectModal.show();
                });
        }
    </script>
@endsection
