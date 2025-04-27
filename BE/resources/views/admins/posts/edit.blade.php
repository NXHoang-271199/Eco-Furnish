@extends('layouts.admin')

@section('title')
    Cập nhật danh mục bài viết: {{ $singerPost->title }}
@endsection
@section('JS')
    <script src="{{ asset('assets/admins/js/pages/form-validation.init.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            ClassicEditor
                .create(document.querySelector('#content'), {
                    simpleUpload: {
                        uploadUrl: '{{ route('upload.image') }}',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    }
                })
                .then(editor => {
                    window.editor = editor;
                })
                .catch(error => {
                    console.error(error);
                });
        });

        // Hàm preview ảnh thumbnail
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('thumbnail-preview');
            const previewWrapper = document.getElementById('thumbnail-preview-wrapper');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewWrapper.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }

        // Hàm xóa ảnh preview và reset về ảnh cũ
        function removePreview() {
            const input = document.getElementById('project-thumbnail-img');
            const preview = document.getElementById('thumbnail-preview');
            const originalImage = '{{ Storage::url($singerPost->image_thumbnail) }}';
            
            input.value = '';
            preview.src = originalImage;
        }
    </script>
@endsection
@section('CSS')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Style cho preview ảnh */
        #thumbnail-preview-wrapper {
            margin-top: 15px;
            position: relative;
        }

        #thumbnail-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .remove-preview {
            position: absolute;
            top: -10px;
            right: -10px;
            background: #fff;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }

        .remove-preview:hover {
            background: #dc3545;
            color: #fff;
        }

        .file-upload-wrapper {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
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

        .custom-file-upload:hover {
            background-color: #e9ecef;
            border-color: #0d6efd;
        }

        .file-upload-wrapper input[type="file"] {
            display: none;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <form action="{{ route('posts.update', $singerPost->id) }}" method="POST" enctype="multipart/form-data"
                class="d-flex needs-validation" novalidate id="postForm">
                @csrf
                @method('PUT')
                <meta name="csrf-token" content="{{ csrf_token() }}">
                <div class="col-lg-8 mx-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <h1><input type="text" class="form-control @error('title') is-invalid @enderror"
                                        id="project-title-input" placeholder="Nhập tiêu đề...." name="title"
                                        value="{{ $singerPost->title }}" style="font-size: 23px;"></h1>
                                <div class="invalid-feedback">
                                    @error('title')
                                        {{ $message }}
                                    @else
                                        Vui nhập tên bài viết.
                                    @enderror
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="content">Nội dung</label>
                                {{-- <div id="editor-container" class="quill-editor @error('content') is-invalid @enderror">
                                </div> --}}
                                <textarea class="form-control @error('content') is-invalid @enderror" type="" name="content" id="content">
                                    {{ $singerPost->content }}
                                </textarea>
                                <div class="invalid-feedback">
                                    @error('content')
                                        {{ $message }}
                                    @else
                                        Vui nhập nôi dung bài viết.
                                    @enderror
                                </div>
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
                                    <select class="form-select @error('category_id') is-invalid @enderror"
                                        id="choices-categories-input" name="category_id">
                                        <option value="" selected></option>
                                        @foreach ($listCategoryPost as $category)
                                            <option value="{{ $category->id }}"
                                                {{ $category->id == $singerPost->category_post_id ? 'selected' : '' }}>
                                                {{ $category->title }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        @error('category_id')
                                            {{ $message }}
                                        @else
                                            Chọn chuyên mục.
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Người đăng</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="user-select" class="form-label">Chọn người đăng bài</label>
                                <select class="form-select @error('user_id') is-invalid @enderror"
                                    id="user-select" name="user_id" required>
                                    <option value="">Chọn người đăng</option>
                                    @foreach ($listUsers as $user)
                                        <option value="{{ $user->id }}"
                                            {{ (old('user_id', $singerPost->user_id) == $user->id) ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    @error('user_id')
                                        {{ $message }}
                                    @else
                                        Vui lòng chọn người đăng.
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div> --}}

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Trạng thái</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="post-status" class="form-label">Trạng thái bài viết</label>
                                <select class="form-select @error('status') is-invalid @enderror" name="status"
                                    id="post-status" required>
                                    <option value="1" {{ $singerPost->status == '1' ? 'selected' : '' }}>Xuất bản
                                    </option>
                                    <option value="0" {{ $singerPost->status == '0' ? 'selected' : '' }}>Chưa xuất
                                        bản</option>
                                </select>
                                <div class="invalid-feedback">
                                    @error('status')
                                        {{ $message }}
                                    @else
                                        Vui lòng chọn trạng thái.
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Ảnh bìa</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="file-upload-wrapper">
                                    <label for="project-thumbnail-img" class="custom-file-upload">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Thay đổi ảnh bìa</span>
                                    </label>
                                    <input class="form-control @error('image_thumbnail') is-invalid @enderror"
                                        id="project-thumbnail-img" type="file" accept="image/png, image/gif, image/jpeg"
                                        name="image_thumbnail" onchange="previewImage(event)">
                                    <div class="invalid-feedback">
                                        @error('image_thumbnail')
                                            {{ $message }}
                                        @else
                                            Chọn ảnh bìa hợp lệ (JPEG, PNG, JPG, GIF, tối đa 2MB).
                                        @enderror
                                    </div>
                                </div>
                                <!-- Hiển thị ảnh bìa -->
                                <div id="thumbnail-preview-wrapper" style="display: block;">
                                    <img id="thumbnail-preview" src="{{ Storage::url($singerPost->image_thumbnail) }}" 
                                         alt="Ảnh bìa">
                                    <div class="remove-preview" onclick="removePreview()">
                                        <i class="ri-close-line"></i>
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
