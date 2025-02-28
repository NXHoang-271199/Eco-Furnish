@extends('layouts.admin')

@section('title', 'Thêm mới vai trò')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Thêm mới vai trò</h5>
                    </div>
                    <div class="card-body">
                        <form id="role-form" action="{{ route('roles.store') }}" method="POST" class="needs-validation" novalidate>
                            @csrf
                            <div class="form-group">
                                <label for="name">Tên vai trò</label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Nhập tên vai trò..." required>
                                <div class="invalid-feedback">
                                    @error('name')
                                        {{ $message }}
                                    @else
                                        Vui lòng nhập tên vai trò.
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" id="submit-btn" class="btn btn-success mt-3">
                                <i class="ri-add-fill me-1 align-bottom"></i> Thêm mới vai trò
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Cột chứa danh sách vai trò/người dùng -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Danh sách vai trò</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">Tên vai trò</th>
                                    <th scope="col" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($listRoles as $role)
                                    <tr>
                                        <td>{{ $role->name }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-primary btn-sm">Sửa</a>
                                            <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display:inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa vai trò này?');">Xóa</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $listRoles->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('JS')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("role-form");

            form.addEventListener("submit", function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add("was-validated");
            });
        });
    </script>
@endsection
