@extends('layouts.admin')

@section('title', 'Thêm Banner mới')

@section('CSS')
    @include('partials.banner.create_css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 text-primary">Thêm Banner mới</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('banners.index') }}" class="text-primary">Quản lý Banner</a></li>
                        <li class="breadcrumb-item active">Thêm Banner mới</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Thông tin Banner</h5>

                    <x-alert />

                    <form action="{{ route('banners.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Tiêu đề</label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="link" class="form-label">Đường dẫn</label>
                                    <input type="text" class="form-control @error('link') is-invalid @enderror" id="link" name="link" value="{{ old('link') }}" placeholder="https://...">
                                    @error('link')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô tả</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="toggle-switch-modern">
                                        <div class="toggle-label">Trạng thái:</div>
                                        <label class="switch">
                                            <input type="checkbox" name="status" value="1" checked>
                                            <span class="slider">
                                                <div class="icons">
                                                    <i class="fas fa-check icon-on"></i>
                                                    <i class="fas fa-times icon-off"></i>
                                                </div>
                                            </span>
                                        </label>
                                        <span class="status-text status-on">Đang hiển thị</span>
                                        <span class="status-text status-off">Đang ẩn</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Ảnh banner <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    <div class="preview-container" id="preview-container">
                                        <div class="placeholder">
                                            <i class="fas fa-image fa-3x mb-2"></i>
                                            <p>Ảnh xem trước</p>
                                        </div>
                                        <div class="remove-image" id="remove-image">
                                            <i class="fas fa-times"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <a href="{{ route('banners.index') }}" class="btn btn-secondary me-2">Hủy</a>
                            <button type="submit" class="btn btn-primary">Lưu Banner</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('JS')
    @include('partials.banner.create_js')
@endsection 