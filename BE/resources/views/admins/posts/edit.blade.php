@extends('layouts.admin')

@section('title')
    Cập nhật danh mục bài viết: {{ $singerPost->title }}
@endsection
@section('JS')
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize toolbar with all options including image upload
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

            // Initialize Quill with full toolbar options
            var quill = new Quill('#editor-container', {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: 'snow'
            });

            // Load existing content from singer post
            quill.root.innerHTML = @json($singerPost->content);

            // Add image handler
            var toolbar = quill.getModule('toolbar');
            toolbar.addHandler('image', imageHandler);

            // Handle image upload when user selects an image
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

            // Function to upload image to server
            function uploadImage(file) {
                var formData = new FormData();
                formData.append('image', file);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute(
                    'content'));

                // Hiển thị văn bản "đang tải" thay vì placeholder image
                var range = quill.getSelection();
                quill.insertText(range.index, "Đang tải ảnh...", {
                    'color': '#999999',
                    'italic': true
                });

                // Gửi request lên server
                fetch('{{ route('upload.image') }}', {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin' // Quan trọng cho CSRF
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Phản hồi server không thành công');
                        }
                        return response.json();
                    })
                    .then(result => {
                        // Xóa văn bản "đang tải"
                        quill.deleteText(range.index, "Đang tải ảnh...".length);

                        // Chèn ảnh đã upload
                        quill.insertEmbed(range.index, 'image', result.url);

                        // Di chuyển con trỏ sau ảnh
                        quill.setSelection(range.index + 1);
                    })
                    .catch(error => {
                        console.error('Lỗi khi upload ảnh:', error);
                        // Xóa văn bản "đang tải"
                        quill.deleteText(range.index, "Đang tải ảnh...".length);
                        alert('Không thể tải ảnh lên. Vui lòng thử lại. Chi tiết lỗi: ' + error.message);
                    });
            }

            // Update content to hidden field when form is submitted
            var form = document.querySelector('form');
            form.addEventListener('submit', function() {
                var content = quill.root.innerHTML;
                document.getElementById('content').value = content;
            });
        });

        // Preview image function (if needed for thumbnail)
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

        document.getElementById('project-thumbnail-img').addEventListener('change', function(event) {
            var input = event.target;
            var preview = document.querySelector('.image-container img'); // Ảnh hiển thị

            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result; // Thay đổi ảnh hiển thị ngay lập tức
                };
                reader.readAsDataURL(input.files[0]);
            }
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
                <meta name="csrf-token" content="{{ csrf_token() }}">
                <div class="col-lg-8 mx-1">
                    <div class="card">
                        <div class="card-body">
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
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Trạng thái</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="post-status" class="form-label">Trạng thái bài viết</label>
                                <select class="form-select" name="status" id="post-status" required>
                                    <option value="1" {{ $singerPost->status == '1' ? 'selected' : '' }}>Xuất bản
                                    </option>
                                    <option value="0" {{ $singerPost->status == '0' ? 'selected' : '' }}>Chưa xuất
                                        bản</option>
                                </select>
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
