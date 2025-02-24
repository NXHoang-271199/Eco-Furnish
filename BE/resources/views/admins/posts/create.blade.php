@extends('layouts.admin')

@section('title')
    Thêm mới bài viết
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
    </style>
@endsection

@section('JS')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Khởi tạo toolbar với nút upload ảnh
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{
                    'header': 1
                }, {
                    'header': 2
                }],
                [{
                    'list': 'ordered'
                }, {
                    'list': 'bullet'
                }],
                [{
                    'script': 'sub'
                }, {
                    'script': 'super'
                }],
                [{
                    'indent': '-1'
                }, {
                    'indent': '+1'
                }],
                [{
                    'direction': 'rtl'
                }],
                [{
                    'size': ['small', false, 'large', 'huge']
                }],
                [{
                    'header': [1, 2, 3, 4, 5, 6, false]
                }],
                [{
                    'color': []
                }, {
                    'background': []
                }],
                [{
                    'font': []
                }],
                [{
                    'align': []
                }],
                ['link', 'image'],
                ['clean']
            ];

            // Khởi tạo Quill
            var quill = new Quill('#editor-container', {
                modules: {
                    toolbar: toolbarOptions
                },
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

                input.onchange = function() {
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
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content'));

                // Hiển thị placeholder cho ảnh đang upload
                var range = quill.getSelection();
                quill.insertEmbed(range.index, 'image', '/path/to/placeholder-image.jpg');

                // Gửi request lên server
                fetch('{{ route('upload.image') }}', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(result => {
                        // Xóa placeholder
                        quill.deleteText(range.index, 1);

                        // Chèn ảnh thật đã upload
                        quill.insertEmbed(range.index, 'image', result.url);

                        // Di chuyển con trỏ sau ảnh
                        quill.setSelection(range.index + 1);
                    })
                    .catch(error => {
                        console.error('Lỗi khi upload ảnh:', error);
                        alert('Không thể tải ảnh lên. Vui lòng thử lại.');
                        quill.deleteText(range.index, 1);
                    });
            }

            // Cập nhật nội dung vào trường ẩn khi submit form
            var form = document.querySelector('form');
            form.addEventListener('submit', function() {
                var content = quill.root.innerHTML;
                document.getElementById('content').value = content;
            });
        });

        function previewImage(event) {
            var input = event.target;
            var preview = document.getElementById('thumbnail-preview');

            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
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
            <form action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data" class="d-flex">
                @csrf
                <meta name="csrf-token" content="{{ csrf_token() }}">
                <div class="col-lg-8 mx-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <input type="text" class="form-control" id="project-title-input"
                                    placeholder="Nhập tiêu đề...." name="title" value="{{ old('title') }}"
                                    style="font-size: 23px;">
                                @error('title')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="content">Nội dung</label>
                                <div id="editor-container" class="quill-editor"></div>
                                <input type="hidden" name="content" id="content" value="{{ old('content') }}">
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
                                <select class="form-select" id="choices-categories-input" name="category_id">
                                    <option value="" selected></option>
                                    @foreach ($listCategoryPost as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                            {{ $category->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="user_id" class="form-label">Chọn người dùng</label>
                                <select class="form-select" name="user_id" id="user_id">
                                    <option value="">Chọn người dùng</option>
                                    @foreach ($users as $user)
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
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label" for="project-thumbnail-img">Ảnh bìa</label>
                                <div class="file-upload-wrapper">
                                    <label for="project-thumbnail-img" class="custom-file-upload">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <span>Chọn ảnh bìa</span>
                                    </label>
                                    <input class="form-control" id="project-thumbnail-img" type="file"
                                        accept="image/png, image/gif, image/jpeg" name="image_thumbnail"
                                        onchange="previewImage(event)">
                                </div>
                            </div>
                            <!-- Hiển thị ảnh bìa đã chọn -->
                            <div class="mt-3">
                                <img id="thumbnail-preview" src="{{ isset($post) ? asset($post->image_thumbnail) : '' }}"
                                    alt="Ảnh bìa" class="img-fluid"
                                    style="display: {{ isset($post) ? 'block' : 'none' }};">
                            </div>
                        </div>
                    </div>
            </form>
        </div>
    </div>
@endsection
