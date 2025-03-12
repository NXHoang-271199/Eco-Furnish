@extends('layouts.admin')

@section('title')
    Thêm mới bài viết
@endsection

@section('CSS')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .quill-editor {
            height: 500px;
            background: #fff;
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

        .custom-file-upload span {
            font-size: 1rem;
        }

        .file-upload-wrapper input[type="file"] {
            display: none;
        }

        .custom-file-upload:hover {
            background-color: #e9ecef;
            border-color: #0d6efd;
        }

        .ql-editor img {
            max-width: 100%;
            height: auto;
        }

        /* Loader cho quá trình upload ảnh */
        .image-uploading {
            display: inline-block;
            width: 100px;
            height: 100px;
            background-color: #f3f3f3;
            border-radius: 5px;
            position: relative;
        }

        .image-uploading:after {
            content: 'Đang tải...';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 12px;
            color: #888;
        }

        .img-fluid {
            border-radius: 7px;
        }

        /* Thêm CSS cho thông báo lỗi */
        .log_css {
            display: none;
            /* color: red; */
            font-size: 12px;
            margin-top: 5px;
            text-align: center;
            position: absolute;
            bottom: -21px;
            left: 22%;
            transform: translateX(-50%);
            width: 100%;
        }

        /* Hiển thị thông báo lỗi khi có lỗi */
        .is-invalid+.log_css {
            display: block;
        }
    </style>
@endsection

@section('JS')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script src="{{ asset('assets/admins/js/pages/form-validation.init.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Khởi tạo toolbar với nút upload ảnh
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ];
        
            // Khởi tạo Quill
            var quill = new Quill('#editor-container', {
                modules: { toolbar: toolbarOptions },
                theme: 'snow'
            });
        
            var oldContent = document.getElementById('content').value;
            if (oldContent) {
                quill.root.innerHTML = oldContent;
            }
        
            // Lấy toolbar từ Quill
            var toolbar = quill.getModule('toolbar');
            toolbar.addHandler('image', imageHandler);
        
            // Xử lý khi người dùng chọn tải ảnh lên
            function imageHandler() {
                var input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.click();
        
                input.onchange = function () {
                    var file = input.files[0];
                    if (file) {
                        uploadImage(file);
                    }
                };
            }
        
            // Hàm upload ảnh lên server
            function uploadImage(file) {
                var formData = new FormData();
                formData.append('image', file);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
                // Hiển thị placeholder cho ảnh đang upload
                var range = quill.getSelection();
                quill.insertEmbed(range.index, 'image', '/path/to/placeholder-image.jpg');
        
                // Gửi request lên server
                fetch('{{ route("upload.image") }}', {  // Đổi route thành route thực tế của bạn
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(result => {
                        quill.deleteText(range.index, 1); // Xóa placeholder
                        quill.insertEmbed(range.index, 'image', result.url);
                        quill.setSelection(range.index + 1);
                    })
                    .catch(error => {
                        console.error('Lỗi khi upload ảnh:', error);
                        alert('Không thể tải ảnh lên. Vui lòng thử lại.');
                        quill.deleteText(range.index, 1);
                    });
            }
        
            // Lấy form
            var form = document.getElementById("postForm");
        
            // Validate Quill Editor khi có thay đổi
            quill.on('text-change', function () {
                var textOnly = quill.getText().trim();
                var editorContainer = document.getElementById('editor-container');
        
                document.getElementById('content').value = quill.root.innerHTML; // Lưu nội dung vào input ẩn
        
                if (textOnly.length > 0) {
                    editorContainer.classList.remove('is-invalid');
                    editorContainer.classList.add('is-valid');
                } else {
                    editorContainer.classList.remove('is-valid');
                    editorContainer.classList.add('is-invalid');
                }
            });
        
            // Validate file ảnh bìa
            var thumbnailInput = document.getElementById('project-thumbnail-img');
            thumbnailInput.addEventListener('change', function (event) {
                previewImage(event);
        
                if (thumbnailInput.files && thumbnailInput.files.length > 0) {
                    thumbnailInput.classList.remove('is-invalid');
                    thumbnailInput.classList.add('is-valid');
                } else {
                    thumbnailInput.classList.remove('is-valid');
                    thumbnailInput.classList.add('is-invalid');
                }
            });
        
            // Validate các trường input, select, textarea thông thường
            form.querySelectorAll('input:not([type="file"]), select, textarea').forEach(function (input) {
                input.addEventListener('input', function () {
                    if (input.checkValidity()) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    } else {
                        input.classList.remove('is-valid');
                        input.classList.add('is-invalid');
                    }
                });
            });
        
            // Hàm hiển thị ảnh preview
            function previewImage(event) {
                var input = event.target;
                var preview = document.getElementById('thumbnail-preview');
        
                if (input.files && input.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }
        });
        </script>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Tạo bài viết mới</h4>
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
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="d-flex"
                class="needs-validation" novalidate id="postForm">
                @csrf
                <meta name="csrf-token" content="{{ csrf_token() }}">
                <div class="col-lg-8 mx-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <input type="text" class="form-control @error('title') is-invalid @enderror"
                                    id="project-title-input" placeholder="Nhập tiêu đề...." name="title"
                                    value="{{ old('title') }}" style="font-size: 23px;" required>
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
                                <div id="editor-container" class="quill-editor @error('content') is-invalid @enderror">
                                </div>
                                <input type="hidden" name="content" id="content" value="{{ old('content') }}" required>
                                <div class="invalid-feedback">
                                    @error('content')
                                        {{ $message }}
                                    @else
                                        Vui nhập nôi dung bài viết.
                                    @enderror
                                </div>
                            </div>
                            <div class="text-end mb-4">
                                <button type="submit" class="btn btn-success w-sm">Tạo bài viết</button>
                                <a href="{{ route('posts.index') }}" class="btn btn-secondary w-sm">Hủy bỏ</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 mx-1">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Mở rộng</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="choices-categories-input" class="form-label">Chuyên mục</label>
                                <select class="form-select @error('category_id') is-invalid @enderror"
                                    id="choices-categories-input" name="category_id" required>
                                    <option value="" selected></option>
                                    @foreach ($listCategoryPost as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Người đăng</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="user-select" class="form-label">Chọn người đăng bài</label>
                                <select class="form-select @error('user_id') is-invalid @enderror"
                                    id="user-select" name="user_id" required>
                                    <option value="" selected>Chọn người đăng</option>
                                    @foreach ($listUsers as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
            

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Trạng thái</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="post-status" class="form-label">Trạng thái bài viết</label>
                                <select class="form-select @error('status') is-invalid @enderror" name="status"
                                    id="post-status" required>
                                    <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Xuất bản</option>
                                    <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Chưa xuất bản
                                    </option>
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
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label" for="project-thumbnail-img">Ảnh bìa</label>
                                <div class="file-upload-wrapper">
                                    <label for="project-thumbnail-img" class="custom-file-upload">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Chọn ảnh bìa</span>
                                    </label>
                                    <input class="form-control @error('image_thumbnail') is-invalid @enderror"
                                        id="project-thumbnail-img" type="file" accept="image/png, image/gif, image/jpeg"
                                        name="image_thumbnail" onchange="previewImage(event)" required>
                                    <div class="invalid-feedback log_css">
                                        @error('image_thumbnail')
                                            {{ $message }}
                                        @else
                                            Chọn ảnh bìa hợp lệ (JPEG, PNG, JPG, GIF, tối đa 2MB).
                                        @enderror
                                    </div>
                                </div>
                                <!-- Hiển thị ảnh bìa đã chọn -->
                                <div class="mt-3">
                                    <img id="thumbnail-preview"
                                        src="{{ isset($post) ? asset($post->image_thumbnail) : '' }}" alt="Ảnh bìa"
                                        class="img-fluid" style="display: {{ isset($post) ? 'block' : 'none' }};">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
