@extends('layouts.admin')

@section('title')
    Chi tiết bài viết
@endsection
@section('CSS')
    <style>
        .singer-post-content img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xxl-10">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mb-4">
                            <p class="text-success text-uppercase mb-2">{{ $singerPost->categoryPost->title }}</p>
                            <h4 class="mb-2">{{ $singerPost->title }}</h4>
                        </div>
                        <div class="row mt-4">
                            <div class="col-lg-2">
                                <h6 class="pb-1">Tạo bỏi:</h6>
                                <div class="d-flex gap-2 mb-3">
                                    <div class="flex-shrink-0">
                                        <img src="{{ $singerPost->user->avatar }}" alt="" class="avatar-sm rounded">
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1"><a
                                                href="{{ route('users.show', $singerPost->user->id) }}">{{ $singerPost->user->name }}</a>
                                        </h5>
                                        <p class="mb-2">{{ $singerPost->user->role->name }}</p>
                                        <p class="text-muted mb-0">{{ $singerPost->created_at->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            </div><!--end col-->
                            <div class="col-lg-10 singer-post-content">
                                <p>{!! $singerPost->content !!}</p>
                            </div><!--end col-->
                        </div><!--end row-->
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
