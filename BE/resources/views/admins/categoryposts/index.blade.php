@extends('layouts.admin')

@section('title')
    Quản lý danh mục bài viết
@endsection

@section('CSS')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        button[type="submit"] {
            border: none;
            background: none;
            padding: 0;
            cursor: pointer;
        }

        #submit-btn {
            background-color: #28a745;
            /* Màu xanh lá */
            color: white;
            /* Màu chữ trắng */
            border: none;
            /* Loại bỏ viền */
            padding: 10px 20px;
            /* Kích thước */
            border-radius: 5px;
            /* Bo góc */
            transition: background 0.3s ease;
        }

        #submit-btn:hover {
            background-color: #218838;
            /* Màu đậm hơn khi hover */
        }
    </style>
@endsection
@section('JS')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById("category-form");
            const titleInput = document.getElementById("title");
            const categoryIdInput = document.getElementById("category-id");
            const methodField = document.getElementById("method-field"); // Thêm 1 div trống vào form
            const submitBtn = document.getElementById("submit-btn");
            const cancelBtn = document.getElementById("cancel-btn");
            const formTitle = document.querySelector(".card-header h5"); // Lấy tiêu đề của form

            // Khi bấm "Sửa"
            document.querySelectorAll(".edit-category").forEach(btn => {
                btn.addEventListener("click", function() {
                    const categoryId = this.getAttribute("data-id");
                    const categoryTitle = this.getAttribute("data-title");

                    // Điền dữ liệu vào form
                    titleInput.value = categoryTitle;
                    categoryIdInput.value = categoryId;

                    // Cập nhật action của form
                    form.action = `/admin/category-posts/${categoryId}`;

                    // Thêm input hidden để Laravel hiểu đây là PUT request
                    methodField.innerHTML = '<input type="hidden" name="_method" value="PUT">';

                    // Đổi tiêu đề thành "Cập nhật danh mục"
                    formTitle.textContent = "Cập nhật danh mục";

                    // Đổi nút thành "Cập nhật"
                    submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Cập nhật';
                    submitBtn.classList.remove("btn-success");
                    submitBtn.classList.add("btn-warning");

                    // Hiện nút "Hủy"
                    cancelBtn.classList.remove("d-none");
                });
            });

            // Khi bấm "Hủy"
            cancelBtn.addEventListener("click", function() {
                // Reset form về trạng thái thêm mới
                titleInput.value = "";
                categoryIdInput.value = "";
                form.action = "{{ route('category-posts.store') }}";

                // Xóa input `_method` để Laravel hiểu đây là POST request
                methodField.innerHTML = "";

                // Đổi tiêu đề về "Thêm danh mục mới"
                formTitle.textContent = "Thêm danh mục mới";

                // Đổi nút thành "Thêm danh mục"
                submitBtn.innerHTML = '<i class="ri-add-fill me-1 align-bottom"></i> Thêm danh mục';
                submitBtn.classList.remove("btn-warning");
                submitBtn.classList.add("btn-success");

                // Ẩn nút "Hủy"
                cancelBtn.classList.add("d-none");
            });
        });
    </script>
@endsection



@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Chuyên mục</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('category-posts.index') }}">Admin</a></li>
                            <li class="breadcrumb-item active">Chuyên mục</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-secondary alert-border-left alert-dismissible fade show material-shadow" role="alert">
                <i class="ri-check-double-line me-3 align-middle"></i>
                <strong>{{ session('success') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-warning alert-border-left alert-dismissible fade show material-shadow" role="alert">
                <i class="ri-alert-line me-3 align-middle"></i> <strong>{{ session('error') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        <!-- end page title -->

        <div class="row">
            <!-- Column for the form -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Thêm danh mục mới</h5>
                    </div>
                    <div class="card-body">
                        <form id="category-form" action="{{ route('category-posts.store') }}" method="POST">
                            @csrf
                            <input type="hidden" id="category-id" name="id">

                            <div id="method-field"></div>

                            <div class="form-group">
                                <label for="title">Tên danh mục</label>
                                <input type="text" id="title" name="title" class="form-control"
                                    placeholder="Nhập tên danh mục" required>
                            </div>
                            <button type="submit" id="submit-btn" class="btn btn-success mt-3">
                                <i class="ri-add-fill me-1 align-bottom"></i> Thêm danh mục
                            </button>

                            <button type="button" id="cancel-btn" class="btn btn-secondary mt-3 d-none">Hủy</button>
                        </form>

                    </div>
                </div>
            </div>

            <!-- Column for the categories list -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Danh sách chuyên mục</h5>
                    </div>
                    <div class="card-body">
                        <div class="row row-cols-xxl-5 row-cols-lg-3 row-cols-md-2 row-cols-1">
                            <!-- Tables Without Borders -->
                            <table class="table table-borderless table-nowrap">
                                <thead>
                                    <tr class="text-center">
                                        <th scope="col">Name</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($listCategoryPost as $cate)
                                        <tr class="text-center">
                                            <th scope="row">{{ $cate->title }}</th>
                                            <td>
                                                <div class="hstack gap-3 fs-15 justify-content-center">
                                                    <a href="javascript:void(0)" class="text-primary mx-2 edit-category"
                                                        data-id="{{ $cate->id }}" data-title="{{ $cate->title }}"
                                                        title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('category-posts.destroy', $cate->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-danger mx-2" title="Xóa"
                                                            onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?');">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
