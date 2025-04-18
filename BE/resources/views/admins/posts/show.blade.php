@extends('layouts.admin')

@section('title')
    Chi tiết bài viết
@endsection
@section('CSS')
    <style>
        .post-detail-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }

        .post-thumbnail {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .singer-post-content {
            font-size: 16px;
            line-height: 1.8;
            color: #333;
        }

        .singer-post-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 1.5rem auto;
            border-radius: 8px;
        }

        .post-title {
            font-size: 2rem;
            font-weight: 600;
            color: #2c3345;
            margin-bottom: 1rem;
        }

        .post-category {
            font-size: 0.9rem;
            font-weight: 500;
            letter-spacing: 1px;
        }

        .post-meta {
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .post-meta .avatar-sm {
            width: 45px;
            height: 45px;
            object-fit: cover;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="post-detail-wrapper">
                            <!-- Ảnh thumbnail -->
                            <div class="text-center">
                                @if($singerPost->image_thumbnail)
                                    <img src="{{ Storage::url($singerPost->image_thumbnail) }}" 
                                        alt="Ảnh bìa {{ $singerPost->title }}"
                                        class="post-thumbnail">
                                @else
                                    <img src="{{ asset('assets/admins/images/blog/overview.jpg') }}" 
                                        alt="Ảnh bìa mặc định"
                                        class="post-thumbnail">
                                @endif
                            </div>

                            <!-- Tiêu đề và chuyên mục -->
                            <div class="text-center mb-4">
                                <p class="text-success text-uppercase post-category mb-2">
                                    {{ $singerPost->categoryPost->title }}
                                </p>
                                <h1 class="post-title">{{ $singerPost->title }}</h1>
                            </div>

                            <!-- Thông tin tác giả -->
                            <div class="post-meta">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        @if($singerPost->user->avatar)
                                            <img src="{{ Storage::url($singerPost->user->avatar) }}" 
                                                alt="" class="rounded-circle avatar-sm">
                                        @else
                                            <img src="{{ asset('assets/admins/images/users/avatarUser.png') }}" 
                                                alt="" class="rounded-circle avatar-sm">
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-1">
                                            <a href="{{ route('users.show', $singerPost->user->id) }}">
                                                {{ $singerPost->user->name }}
                                            </a>
                                        </h5>
                                        <p class="text-muted mb-0">
                                            {{ $singerPost->user->role->name }} • 
                                            {{ $singerPost->created_at->format('d/m/Y') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Nội dung bài viết -->
                            <div class="singer-post-content">
                                {!! $singerPost->content !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
