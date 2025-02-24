@extends('layouts.admin')

@section('title')
    Thêm mới vai trò
@endsection

@section('CSS')
    <style>
        #team-member-list.grid-view .member-item {
            display: inline-block;
            width: 30%;
        }

        #team-member-list.list-view .member-item {
            display: block;
            width: 100%;
        }
    </style>
@endsection

@section('JS')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("category-form");

            form.addEventListener("submit", function(event) {
                event.preventDefault(); // Ngăn chặn reload trang mặc định

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';

                fetch(this.action, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Hiển thị thông báo thành công
                            Swal.fire({
                                title: 'Thành công!',
                                text: 'Thêm vai trò thành công',
                                icon: 'success',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                // Chuyển hướng sau khi hiển thị thông báo
                                window.location.href = '/admin/roles';
                            });
                        } else {
                            // Xóa các lỗi cũ trước khi hiển thị lỗi mới
                            document.querySelectorAll('.text-danger').forEach(el => el.remove());

                            // Hiển thị lỗi nếu có
                            Object.keys(data.errors || {}).forEach(key => {
                                const input = document.querySelector(`[name="${key}"]`);
                                if (input) {
                                    const errorDiv = document.createElement('div');
                                    errorDiv.className = 'text-danger small';
                                    errorDiv.textContent = data.errors[key][0];
                                    input.parentNode.appendChild(errorDiv);
                                }
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'alert alert-danger mt-3';
                        errorMessage.textContent = 'Có lỗi xảy ra khi thêm vai trò. Vui lòng thử lại.';
                        form.insertBefore(errorMessage, form.firstChild);
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });
        });
    </script>
@endsection


@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Thêm mới vai trò</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            @foreach ($breadcrumbs as $breadcrumb)
                                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}">
                                    @if ($breadcrumb['url'])
                                        <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['name'] }}</a>
                                    @else
                                        {{ $breadcrumb['name'] }}
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-secondary alert-border-left alert-dismissible fade show material-shadow" role="alert">
                <i class="ri-check-double-line me-3 align-middle"></i>
                <strong>{{ session('success') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-warning alert-border-left alert-dismissible fade show material-shadow" role="alert">
                <i class="ri-alert-line me-3 align-middle"></i> <strong>{{ session('error') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <!-- end page title -->

        <div class="row">
            <!-- Column for the form -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Thêm mới vai trò</h5>
                    </div>
                    <div class="card-body">
                        <form id="category-form" action="{{ route('roles.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="title">Tên vai trò</label>
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Nhập tên vai trò..." value="">
                                @error('name')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" id="submit-btn" class="btn btn-success mt-3">
                                <i class="ri-add-fill me-1 align-bottom"></i> Thêm mới vai trò
                            </button>

                            <button type="button" id="cancel-btn" class="btn btn-secondary mt-3 d-none">Hủy</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Column for the categories list -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Danh sách</h5>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-xxl-5 row-cols-lg-3 row-cols-md-2 row-cols-1">
                            <!-- Tables Without Borders -->
                            <table class="table table-borderless table-nowrap">
                                <thead>
                                    <tr>
                                        <th scope="col">Name</th>
                                        <th scope="col" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td scope="row">
                                                <h5>{{ $user->name }}</h5>
                                            </td>
                                            <td>
                                                <div class="hstack gap-3 fs-15 justify-content-center">
                                                    {{-- <form action=""
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-danger mx-2 border-0" title="Xóa"
                                                            onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                                                            <i class="ri-delete-bin-5-line"></i>

                                                        </button>
                                                    </form> --}}
                                                    <a href="{{ route('users.show', $user->id) }}"
                                                        class="text-primary mx-2 border-0" title="Chỉnh sửa"><i
                                                            class="ri-eye-line"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{ $users->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
