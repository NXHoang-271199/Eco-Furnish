@extends('layouts.admin')

@section('title')
    Cập nhật thông tin người dùng: {{ $singerUser->name }}
@endsection

@section('CSS')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/pnotify/5.2.0/PNotifyBrightTheme.min.css" rel="stylesheet">
@endsection

@section('JS')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pnotify/5.2.0/PNotify.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector(".needs-validation");
            const input = document.getElementById("profile-img-file-input");
            const previewImage = document.getElementById("preview-image");

            form.addEventListener("submit", function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add("was-validated");
            });

            input.addEventListener("change", function(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        previewImage.src = e.target.result; // Cập nhật ảnh xem trước
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Chỉnh sửa người dùng</h4>
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

        <form action="{{ route('users.update', $singerUser->id) }}" method="POST" enctype="multipart/form-data"
            autocomplete="off" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            <div class="row">
                <!-- Avatar Section -->
                <div class="col-xxl-3 mt-5">
                    <div class="card mt-n5">
                        <div class="card-body p-4">
                            <div class="text-center">
                                <div class="profile-user position-relative d-inline-block mx-auto mb-4">
                                    @if ($singerUser->avatar)
                                        <img id="preview-image" src="{{ Storage::url($singerUser->avatar) }}"
                                            class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                            alt="user-profile-image">
                                    @else
                                        <img id="preview-image"
                                            src="{{ asset('assets/admins/images/users/user-dummy-img.jpg') }}"
                                            class="rounded-circle avatar-xl img-thumbnail user-profile-image"
                                            alt="user-profile-image">
                                    @endif

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
                                        <div class="invalid-feedback d-block text-danger">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted d-block mt-2">Cho phép JPG, JPEG, PNG hoặc GIF. Tối đa
                                        2MB</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Fields Section -->
                <div class="col-xxl-9">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Cập nhật người dùng</h4>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <!-- User Name -->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="nameInput" class="form-label">Tên người dùng</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="nameInput" name="name" placeholder="Nhập tên người dùng"
                                            value="{{ old('name', $singerUser->name) }}" required autocomplete="off">
                                        <div class="invalid-feedback">
                                            @error('name')
                                                {{ $message }}
                                            @else
                                                Vui lòng nhập tên người dùng.
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Age -->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="ageInput" class="form-label">Tuổi</label>
                                        <input type="number" class="form-control @error('age') is-invalid @enderror"
                                            id="ageInput" name="age" placeholder="Nhập tuổi"
                                            value="{{ old('age', $singerUser->age) }}" required>
                                        <div class="invalid-feedback">
                                            @error('age')
                                                {{ $message }}
                                            @else
                                                Vui lòng nhập tuổi hợp lệ.
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Email -->
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="emailInput" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="emailInput" name="email" placeholder="Nhập email"
                                            value="{{ old('email', $singerUser->email) }}" required autocomplete="off">
                                        <div class="invalid-feedback">
                                            @error('email')
                                                {{ $message }}
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="roleSelect" class="form-label">Vai trò</label>
                                        <select class="form-select @error('role_id') is-invalid @enderror" id="roleSelect"
                                            name="role_id" required autocomplete="off">
                                            <option value="">Chọn vai trò</option>
                                            @foreach ($listRoles as $role)
                                                <option value="{{ $role->id }}"
                                                    {{ old('role_id', $singerUser->role_id) == $role->id ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <div class="invalid-feedback">
                                            @error('role_id')
                                                {{ $message }}
                                            @else
                                                Vui lòng chọn vai trò.
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="mb-3">
                                        <label for="statusSelect" class="form-label">Trạng thái</label>
                                        <select name="is_active"
                                            class="form-select @error('is_active') is-invalid @enderror" id="statusSelect"
                                            required autocomplete="off">
                                            <option value="1"
                                                {{ old('is_active', $singerUser->is_active ?? 1) == 1 ? 'selected' : '' }}>
                                                Kích hoạt</option>
                                            <option value="0"
                                                {{ old('is_active', $singerUser->is_active ?? 1) == 0 ? 'selected' : '' }}>
                                                Hủy kích hoạt</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('is_active')
                                                {{ $message }}
                                            @else
                                                Vui lòng chọn trạng thái.
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Address -->
                                <div class="col-lg-12">
                                    <div class="mb-3">
                                        <label for="addressInput" class="form-label">Địa chỉ</label>
                                        <input type="text" class="form-control @error('address') is-invalid @enderror"
                                            id="addressInput" name="address" placeholder="Nhập địa chỉ"
                                            value="{{ old('address', $singerUser->address) }}" required
                                            autocomplete="off">
                                        <div class="invalid-feedback">
                                            @error('address')
                                                {{ $message }}
                                            @else
                                                Vui lòng nhập địa chỉ.
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <!-- Submit Button -->
                                <div class="col-lg-12 text-end">
                                    <button type="submit" class="btn btn-primary">Cập nhật</button>
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
