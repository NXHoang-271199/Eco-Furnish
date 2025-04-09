@extends('layouts.admin')

@section('title')
    Quản lý phương thức thanh toán
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Danh sách phương thức thanh toán</h5>
                @if (Auth::user()->hasPermission('create-payment-methods'))
                    <a href="{{ route('payment-methods.create') }}" class="btn btn-primary">Thêm phương thức</a>
                @endif
            </div>
            <div class="card-body">
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
                                <td><img src="{{ Storage::url($method->image) }}" class="img-thumbnail" alt="ảnh phương thức"
                                        width="100px"></td>
                                <td>{{ $method->name }}</td>
                                <td>
                                    @if ($method->is_connected)
                                        <span class="badge bg-success">Đã kết nối</span>
                                    @else
                                        <span class="badge bg-danger">Chưa kết nối</span>
                                    @endif
                                </td>
                                <td>
                                    @if (Auth::user()->hasPermission('delete-payment-methods'))
                                        <form action="{{ route('payment-methods.destroy', $method->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash-alt"></i> Xóa
                                            </button>
                                        </form>
                                    @endif

                                    @if ($method->name !== 'Tiền mặt' && $method->name !== 'Ví')
                                        @if (!$method->is_connected)
                                            <!-- Nút Kết nối - Màu xanh lá -->
                                            <button class="btn btn-success btn-sm"
                                                onclick="openConnectModal('{{ route('payment-methods.connect', $method->id) }}')">
                                                <i class="fas fa-plug"></i> Kết nối
                                            </button>
                                        @endif

                                        <div class="btn-group">
                                            <!-- Nút bánh răng -->
                                            <button class="btn btn-primary btn-sm dropdown-toggle" type="button"
                                                id="settingsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fas fa-cogs"></i> Cấu hình
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="settingsDropdown">
                                                @if ($method->is_connected)
                                                    <li>
                                                        <a class="dropdown-item" href="#"
                                                            onclick="openEditConnectionModal('{{ route('payment-methods.edit_connection.form', $method->id) }}')">
                                                            <i class="fas fa-wrench"></i> Sửa cấu hình
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <!-- Form Hủy kết nối -->
                                                        <form
                                                            action="{{ route('payment-methods.disconnect', $method->id) }}"
                                                            method="POST" class="d-inline"
                                                            onsubmit="return confirm('Bạn có chắc chắn muốn hủy kết nối?');">
                                                            @csrf
                                                            @method('POST')
                                                            <button type="submit" class="dropdown-item">
                                                                <i class="fas fa-unlink"></i> Hủy kết nối
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @if (Auth::user()->hasPermission('update-payment-methods'))
                                                    <li>
                                                        <a class="dropdown-item"
                                                            href="{{ route('payment-methods.edit', $method->id) }}">
                                                            <i class="fas fa-edit"></i> Sửa
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
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
    <!-- Modal sửa kết nối -->
    <div class="modal fade" id="editConnectionModal" tabindex="-1" aria-labelledby="editConnectionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editConnectionModalLabel">Sửa thông tin kết nối</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="editConnectionModalBody">
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

        function openEditConnectionModal(url) {
            fetch(url)
                .then(response => response.json()) // Đảm bảo rằng bạn đang nhận lại JSON
                .then(data => {
                    if (data.html) {
                        document.getElementById('editConnectionModalBody').innerHTML = data.html;
                        var editConnectionModal = new bootstrap.Modal(document.getElementById('editConnectionModal'));
                        editConnectionModal.show();
                    } else {
                        alert('Lỗi khi tải thông tin kết nối');
                    }
                })
                .catch(error => {
                    console.error('Có lỗi xảy ra:', error);
                });
        }
    </script>
@endsection
