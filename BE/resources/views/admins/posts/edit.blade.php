@extends('layouts.admin')

@section('title')
    Cập nhật danh mục bài viết: {{ $singerPost->title }}
@endsection
@section('JS')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor-container', {
            theme: 'snow'
        });

        quill.root.innerHTML = @json($singerPost->content);

        var form = document.querySelector('form');

        form.addEventListener('submit', function() {
            var content = quill.root.innerHTML;
            document.getElementById('content').value = content;
        });
    </script>
@endsection
@section('CSS')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .quill-editor {
            height: 450px;
            background: #fff;
        }

        .file-upload-wrapper {
            position: relative;
        }

        .file-upload-wrapper input[type="file"] {
            display: none;
        }

        .custom-file-upload {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background-color: #f8f9fa;
            border: 1px solid #dce0e3;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .custom-file-upload:hover {
            background-color: #e9ecef;
            border-color: #0d6efd;
        }

        .custom-file-upload i {
            font-size: 1.2rem;
            color: #0d6efd;
        }

        .custom-file-upload span {
            font-size: 0.9rem;
            color: #6c757d;
        }

        .file-upload-wrapper {
            position: relative;
        }

        .image-container {
            position: relative;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 10px;
        }

        .image-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .custom-file-upload-small {
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #f8f9fa;
            border: 1px solid #dce0e3;
            border-radius: 50%;
            padding: 8px;
            font-size: 1.2rem;
            color: #0d6efd;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .custom-file-upload-small:hover {
            background-color: #e9ecef;
        }

        .custom-file-upload-small i {
            font-size: 1.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Chỉnh sửa bài viết</h4>
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
        <div class="row">
            <form action="{{ route('posts.update', $singerPost->id) }}" method="POST" enctype="multipart/form-data"
                class="d-flex">
                @csrf
                @method('PUT')
                <div class="col-lg-8 mx-1">
                    <div class="card">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="mb-3">
                                <h1><input type="text" class="form-control" id="project-title-input"
                                        placeholder="Nhập tiêu đề...." name="title" value="{{ $singerPost->title }}"
                                        style="font-size: 23px;"></h1>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="content">Nội dung</label>
                                <div id="editor-container" class="quill-editor"></div>
                                <input type="hidden" name="content" id="content" value="{{ $singerPost->content }}">
                            </div>
                            <div class="text-end mb-4">
                                <button type="submit" class="btn btn-success w-sm">Cập nhật</button>
                                <a href="{{ route('posts.index') }}" class="btn btn-secondary w-sm">Hủy bỏ</a>
                            </div>

                        </div>

                    </div>
                    <!-- end card -->

                </div>
                <!-- end col -->
                <div class="col-lg-3 mx-1">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Mở rộng</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="choices-categories-input" class="form-label">Chuyên mục</label>
                                <div class="choices">
                                    <select class="form-select" id="choices-categories-input" name="category_id">
                                        <option value="" selected></option>
                                        @foreach ($listCategoryPost as $category)
                                            <option value="{{ $category->id }}"
                                                {{ $category->id == $singerPost->category_post_id ? 'selected' : '' }}>
                                                {{ $category->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="user_id" class="form-label">Chọn người dùng</label>
                                <select class="form-select" name="user_id" id="user_id" required>
                                    <option value="">Chọn người dùng</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}"
                                            @if ($user->id == $singerPost->user_id) selected @endif>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Ảnh bìa</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="file-upload-wrapper">
                                    <div class="col-lg-12 mb-3">
                                        <!-- Phần hiển thị ảnh -->
                                        <div class="image-container position-relative">
                                            <img src="{{ Storage::url($singerPost->image_thumbnail) }}" alt="Ảnh bìa"
                                                class="img-fluid rounded">

                                            <!-- Nút chọn ảnh nhỏ ở dưới -->
                                            <label for="project-thumbnail-img" class="custom-file-upload-small">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </label>
                                        </div>

                                        <input class="form-control" id="project-thumbnail-img" type="file"
                                            accept="image/png, image/gif, image/jpeg" name="image_thumbnail">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

    </div>
@endsection
