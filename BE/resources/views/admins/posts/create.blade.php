@extends('layouts.admin')

@section('title')
    Thêm mới bài viết
@endsection

@section('JS')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor-container', {
            theme: 'snow'
        });
        var form = document.querySelector('form');
        form.addEventListener('submit', function() {
            var content = quill.root.innerHTML; // Lấy nội dung HTML từ Quill
            document.getElementById('content').value = content; // Cập nhật giá trị cho input ẩn
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
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            background-color: #f8f9fa;
            border: 1px solid #dce0e3;
            border-radius: 8px;
            padding: 20px;
        }

        .custom-file-upload {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 1rem;
            color: #0d6efd;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .custom-file-upload i {
            font-size: 1.5rem;
        }

        .custom-file-upload span {
            font-size: 1rem;
        }

        .file-upload-wrapper input[type="file"] {
            display: none;
            /* Ẩn input file */
        }

        .custom-file-upload:hover {
            background-color: #e9ecef;
            border-color: #0d6efd;
        }

        .custom-file-upload:active {
            background-color: #d1e7ff;
        }

        /* Điều chỉnh kích thước input cho giống với div */
        .file-upload-wrapper input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            /* Ẩn input để chỉ hiển thị button */
            cursor: pointer;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="d-flex">
                @csrf
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
                                <input type="text" class="form-control" id="project-title-input"
                                    placeholder="Nhập tiêu đề...." name="title" value="{{ old('title') }}"
                                    style="font-size: 23px;">
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="content">Nội dung</label>
                                <div id="editor-container" class="quill-editor"></div>
                                <input type="hidden" name="content" id="content" value="{{ old('content') }}">
                            </div>
                            <div class="text-end mb-4">
                                <button type="submit" class="btn btn-success w-sm">Tạo bài viết</button>
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
                                            <option value="{{ $category->id }}">{{ $category->title }}</option>
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
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label" for="project-thumbnail-img">Ảnh bìa</label>
                                <div class="file-upload-wrapper">
                                    <label for="project-thumbnail-img" class="custom-file-upload">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                    </label>
                                    <input class="form-control" id="project-thumbnail-img" type="file"
                                        accept="image/png, image/gif, image/jpeg" name="image_thumbnail">
                                </div>
                            </div>
                        </div>
                    </div>
            </form>
        </div>

    </div>
@endsection
