@extends('layouts.admin')

@section('title', 'Quản lý Banner')

@section('CSS')
    @include('partials.banner.index_css')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 text-primary">Quản lý Banner</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-primary">Dashboard</a></li>
                        <li class="breadcrumb-item active">Quản lý Banner</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title">Danh sách Banner</h5>
                        <a href="{{ route('banners.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus-circle me-1"></i> Thêm Banner
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">STT</th>
                                    <th width="20%">Ảnh</th>
                                    <th width="20%">Tiêu đề</th>
                                    <th width="20%">Đường dẫn</th>
                                    <th width="10%">Trạng thái</th>
                                    <th width="20%">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody id="sortable-banners">
                                @forelse($banners as $key => $banner)
                                <tr data-id="{{ $banner->id }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-grip-vertical handle me-2"></i>
                                            {{ $key + 1 }}
                                        </div>
                                    </td>
                                    <td>
                                        <img src="{{ asset($banner->image) }}" alt="{{ $banner->title }}" class="banner-image">
                                    </td>
                                    <td>{{ $banner->title ?? 'Không có tiêu đề' }}</td>
                                    <td>
                                        <a href="{{ $banner->link }}" target="_blank" class="text-primary">
                                            {{ $banner->link ?? 'Không có đường dẫn' }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="banner-status {{ $banner->status ? 'status-active' : 'status-inactive' }}"></span>
                                        {{ $banner->status ? 'Hiển thị' : 'Ẩn' }}
                                    </td>
                                    <td>
                                        <a href="{{ route('banners.edit', $banner->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <form action="{{ route('banners.destroy', $banner->id) }}" method="POST" class="d-inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm delete-btn" data-name="{{ $banner->title ?? 'Banner này' }}">
                                                <i class="fas fa-trash-alt"></i> Xóa
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Chưa có banner nào</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('JS')
    @include('partials.banner.index_js')
@endsection 