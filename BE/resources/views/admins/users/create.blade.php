@extends('layouts.admin')

@section('title')
    Tạo mới người dùng
@endsection

@section('CSS')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/pnotify/5.2.0/PNotifyBrightTheme.min.css" rel="stylesheet">
@endsection

@section('JS')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pnotify/5.2.0/PNotify.min.js"></script>
    <script src="{{ asset('assets/admins/js/pages/form-validation.init.js') }}"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector(".needs-validation");
            const imageInput = document.getElementById('profile-img-file-input');
            const previewImage = document.getElementById('preview-image');

            // Validate form khi submit
            form.addEventListener("submit", function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add("was-validated");
            });

            // Validate và preview ảnh
            imageInput.addEventListener('change', function() {
                const file = this.files[0];

                if (!file) return;

                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    this.value = '';
                    PNotify.error({
                        title: 'Lỗi',
                        text: 'Vui lòng chọn file ảnh có định dạng: JPG, JPEG, PNG hoặc GIF'
                    });
                    return;
                }

                // Validate file size (10MB)
                if (file.size > 10 * 1024 * 1024) {
                    this.value = '';
                    PNotify.error({
                        title: 'Lỗi',
                        text: 'Kích thước ảnh không được vượt quá 10MB'
                    });
                    return;
                }

                // Preview image
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                }
                reader.readAsDataURL(file);
            });
        });
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Tạo mới người dùng</h4>
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

        <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation"
            novalidate autocomplete="off">
            @csrf
            <div class="row">
                <div class="col-xxl-3 mt-5">
                    <div class="card mt-n5">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                    <img id="preview-image"
                                        src="{{ asset('assets/admins/images/users/user-dummy-img.jpg') }}"
                                        class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                        alt="user-profile-image">


                                    <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                        <input id="profile-img-file-input" type="file" name="avatar"
                                            class="profile-img-file-input @error('avatar') is-invalid @enderror">
                                        <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                            <span class="avatar-title rounded-circle bg-light text-body">
                                                <i class="ri-camera-fill"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <h4 class="text-center mb-2">Avatar</h4>
                                    @error('avatar')
                                        <div class="invalid-feedback d-block text-danger">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    <small class="text-muted d-block mt-2">Cho phép JPG, JPEG, PNG hoặc GIF. Tối đa
                                        10MB</small>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-9">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Thêm mới người dùng</h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="nameInput" class="form-label">Tên người dùng</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="nameInput" name="name" placeholder="Nhập tên người dùng"
                                            value="{{ old('name') }}" required autocomplete="off">
                                        <div class="invalid-feedback">
                                            @error('name')
                                                {{ $message }}
                                            @else
                                                Vui lòng nhập tên người dùng.
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="ageInput" class="form-label">Tuổi</label>
                                        <input type="number" class="form-control @error('age') is-invalid @enderror"
                                            id="ageInput" name="age" placeholder="Nhập tuổi"
                                            value="{{ old('age') }}" required>
                                        <div class="invalid-feedback">
                                            @error('age')
                                                {{ $message }}
                                            @else
                                                Vui lòng nhập tuổi hợp lệ.
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="emailInput" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="emailInput" name="email" placeholder="Nhập email"
                                            value="{{ old('email') }}" required autocomplete="off">
                                        <div class="invalid-feedback">
                                            @error('email')
                                                {{ $message }}
                                            @else
                                                Vui lòng nhập email hợp lệ.
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="passwordInput" class="form-label">Mật khẩu</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="passwordInput" name="password" placeholder="Nhập mật khẩu" required
                                            autocomplete="new-password">
                                        <div class="invalid-feedback">
                                            @error('password')
                                                {{ $message }}
                                            @else
                                                Vui lòng nhập mật khẩu.
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="roleInput" class="form-label">Phân quyền</label>
                                        <select name="role_id" class="form-select @error('role_id') is-invalid @enderror"
                                            id="roleInput" required>
                                            <option value="">Chọn phân quyền</option>
                                            @foreach ($listRoles as $role)
                                                <option value="{{ $role->id }}"
                                                    {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                    {{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('role_id')
                                                {{ $message }}
                                            @else
                                                Vui lòng chọn phân quyền.
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="addressInput" class="form-label">Địa chỉ</label>
                                        <input type="text" class="form-control @error('address') is-invalid @enderror"
                                            id="addressInput" name="address" placeholder="Nhập địa chỉ"
                                            value="{{ old('address') }}" required>
                                        <div class="invalid-feedback">
                                            @error('address')
                                                {{ $message }}
                                            @else
                                                Vui lòng nhập địa chỉ.
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-12 text-end">
                                    <button type="submit" class="btn btn-primary">Thêm mới</button>
                                    <a href="{{ route('users.index') }}" class="btn btn-soft-secondary">Hủy bỏ</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
@endsection
