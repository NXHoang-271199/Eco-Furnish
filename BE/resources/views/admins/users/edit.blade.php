@extends('layouts.admin')

@section('title')
    Cập nhật thông tin người dùng: {{ $singerUser->name }}
@endsection
@section('CSS')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/pnotify/5.2.0/PNotifyBrightTheme.min.css" rel="stylesheet">
@endsection
@section('JS')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pnotify/5.2.0/PNotify.min.js"></script>
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
        <div class="row mt-5">
            <div class="col-xxl-3">
                <div class="card mt-n5">
                    <div class="card-body p-4">
                        <form action="{{ route('users.update', $singerUser->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="text-center">
                                <div class="profile-user position-relative d-inline-block mx-auto  mb-4">
                                    <img src="{{ Storage::url($singerUser->avatar) }}"
                                        class="rounded-circle avatar-xl img-thumbnail user-profile-image material-shadow"
                                        alt="user-profile-image">
                                    <div class="avatar-xs p-0 rounded-circle profile-photo-edit">
                                        <input id="profile-img-file-input" type="file" class="profile-img-file-input"
                                            name="avatar">
                                        <label for="profile-img-file-input" class="profile-photo-edit avatar-xs">
                                            <span class="avatar-title rounded-circle bg-light text-body material-shadow">
                                                <i class="ri-camera-fill"></i>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                                <h5 class="fs-16 mb-1">{{ $singerUser->name }}</h5>
                                <p class="text-muted mb-0">{{ $singerUser->role->name }}</p>
                            </div>
                    </div>
                </div>
                <!--end card-->
            </div>
            <!--end col-->
            <div class="col-xxl-9">
                <div class="card mt-xxl-n5">
                    <div class="card-header">
                        <ul class="nav nav-tabs-custom rounded card-header-tabs border-bottom-0" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" data-bs-toggle="tab" href="#personalDetails" role="tab"
                                    aria-selected="true">
                                    <i class="fas fa-home"></i> Chi tiết cá nhân
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" data-bs-toggle="tab" href="#resetPassword" role="tab"
                                    aria-selected="false" tabindex="-1">
                                    <i class="far fa-user"></i> Reset mật khẩu
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content">
                            <div class="tab-pane active" id="personalDetails" role="tabpanel">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="firstnameInput" class="form-label">Tên người dùng</label>
                                            <input type="text" class="form-control" id="firstnameInput"
                                                placeholder="Enter your firstname" value="{{ $singerUser->name }}"
                                                name="name">
                                            @error('name')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="lastnameInput" class="form-label">Tuổi</label>
                                            <input type="text" class="form-control" id="lastnameInput"
                                                placeholder="Enter your lastname" value="{{ $singerUser->age }}"
                                                name="age">
                                            @error('age')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="emailInput" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="emailInput"
                                                placeholder="Enter your email" value="{{ $singerUser->email }}"
                                                name="email">
                                            @error('email')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="JoiningdatInput" class="form-label">Ngày tham gia</label>
                                            <input type="text" class="form-control flatpickr-input"
                                                placeholder="{{ $singerUser->created_at }}" readonly="readonly">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="countryInput" class="form-label">Phân quyền</label>
                                            <select name="role_id" class="form-select" id="">
                                                <option value="">Chưa phân quyền</option>
                                                @foreach ($listRoles as $role)
                                                    <option value="{{ $role->id }}"
                                                        {{ $role->id == $singerUser->role_id ? 'selected' : '' }}>
                                                        {{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('role_id')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="mb-3">
                                            <label for="isActiveSelect" class="form-label">Trạng thái</label>
                                            <select name="is_active" class="form-select" id="isActiveSelect">
                                                <option value="1"
                                                    {{ old('is_active', $singerUser->is_active ?? 1) == 1 ? 'selected' : '' }}>
                                                    Kích hoạt</option>
                                                <option value="0"
                                                    {{ old('is_active', $singerUser->is_active ?? 1) == 0 ? 'selected' : '' }}>
                                                    Hủy kích hoạt</option>
                                            </select>
                                            @error('is_active')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="mb-3">
                                            <label for="countryInput" class="form-label">Địa chỉ</label>
                                            <input type="text" class="form-control" id="countryInput"
                                                placeholder="Country" value="{{ $singerUser->address }}" name="address">
                                            @error('address')
                                                <div class="text-danger small">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-lg-12">
                                        <div class="hstack gap-2 justify-content-end">
                                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                                            <a href="{{ route('users.index') }}" type="button"
                                                class="btn btn-soft-success">Hủy bỏ</a>
                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>
                                <!--end row-->
                                </form>
                            </div>
                            <!--end tab-pane-->
                            <div class="tab-pane" id="resetPassword" role="tabpanel">
                                <form action="javascript:void(0);">
                                    <div class="row g-2">
                                        <div class="col-lg-4">
                                            <div>
                                                <label for="newpasswordInput" class="form-label">New Password*</label>
                                                <input type="password" class="form-control" id="newpasswordInput"
                                                    placeholder="Enter new password" value="123456">
                                            </div>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-success">Change Password</button>
                                            </div>
                                        </div>
                                        <!--end col-->
                                    </div>
                                    <!--end row-->
                                </form>
                            </div>
                            <!--end tab-pane-->
                        </div>
                    </div>
                </div>
            </div>
            <!--end col-->
        </div>
        <!--end row-->

    </div>
@endsection
