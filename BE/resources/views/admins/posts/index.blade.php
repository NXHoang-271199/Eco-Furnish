@extends('layouts.admin')

@section('title')
    Bài Viết
@endsection
@section('CSS')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        .fixed-thumbnail {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;

        }

        .post-thumbnail-container {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .post-thumbnail {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;

        }

        .button-list {
            display: flex;
            gap: 10px;
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 10;
        }

        .button-list .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .button-list .btn i {
            font-size: 1.25rem;
        }

        .button-list .btn:hover {
            opacity: 0.8;
        }

        .nav-tabs {
            border-bottom: 2px solid #ddd;
        }

        .nav-tabs .nav-link:hover {
            background: #f1f1f1;
            color: #333;
        }

        .tab-bg {
            background: #fff;
            padding: 20px;
        }

        .nav-tabs .nav-link.active {
            background: #28a745;
            color: #fff;
            border-color: #28a745;
        }

        .button-list {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            align-items: center;
            z-index: 10;
        }

        .post-actions {
            display: flex;
            gap: 5px;
            justify-content: flex-end;
            align-items: center;
            width: auto;
        }

        .bg-warning-subtle {
            background-color: #fff3cd !important;
        }

        .post-card {
            position: relative;
        }

        .post-actions {
            display: flex;
            gap: 5px;
            z-index: 100;
        }

        .post-actions .btn {
            padding: 3px 9px;
            font-size: 14px;
        }

        .post-actions .btn i {
            font-size: 16px;
        }

        .post-actions .btn:hover {
            opacity: 0.8;
        }
    </style>
@endsection

@section('JS')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>

    <script>
        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();

            let form = $(this).closest("form");

            console.log("Nút xóa đã được nhấn!");

            Swal.fire({
                title: "Bạn có chắc chắn muốn xóa?",
                text: "Hành động này không thể hoàn tác!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Có, xóa!",
                cancelButtonText: "Hủy"
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log("Đã xác nhận xóa!");
                    form.submit();
                } else {
                    console.log("Hủy xóa!");
                }
            });
        });
    </script>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                    <h4 class="mb-sm-0">Danh sách bài viết</h4>
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
            <div class="col-xxl-3">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="pt-4 border-top border-dashed border-bottom-0 border-start-0 border-end-0">
                            <p class="text-muted">Chuyên mục</p>
                            <ul class="list-unstyled fw-medium">
                                <li>
                                    <a href="{{ route('posts.index', array_merge(request()->except('category_post_id'))) }}"
                                        class="text-muted py-2 d-block {{ request('category_post_id') ? '' : 'fw-bold text-primary' }}">
                                        <i class="mdi mdi-chevron-right me-1"></i> Tất cả chuyên mục
                                    </a>
                                </li>
                                @foreach ($listCategoryPost as $category)
                                    <li>
                                        <a href="{{ route('posts.index', array_merge(request()->query(), ['category_post_id' => $category->id])) }}"
                                            class="text-muted py-2 d-block {{ request('category_post_id') == $category->id ? 'fw-bold text-primary' : '' }}">
                                            <i class="mdi mdi-chevron-right me-1"></i>{{ $category->title }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="pt-4 border-top border-dashed border-bottom-0 border-start-0 border-end-0">
                            <p class="text-muted">Lưu trữ</p>
                            <ul class="list-unstyled fw-medium">
                                <li>
                                    <a href="{{ route('posts.index', array_merge(request()->except('year'))) }}"
                                        class="text-muted py-2 d-block {{ request('year') ? '' : 'fw-bold text-primary' }}">
                                        <i class="mdi mdi-chevron-right me-1"></i> Tất cả năm
                                    </a>
                                </li>
                                @foreach ($years as $year)
                                    <li>
                                        <a href="{{ route('posts.index', array_merge(request()->query(), ['year' => $year->year])) }}"
                                            class="text-muted py-2 d-block {{ request('year') == $year->year ? 'fw-bold text-primary' : '' }}">
                                            <i class="mdi mdi-chevron-right me-1"></i> {{ $year->year }}
                                            <span class="badge badge-soft-success rounded-pill float-end ms-1 font-size-12">
                                                {{ $postsByYear[$year->year] }}
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="pt-4 border-top border-dashed border-bottom-0 border-start-0 border-end-0">
                            <p class="text-muted mb-2">Bài viết mới nhất</p>
                            <div class="list-group list-group-flush">
                                @foreach ($featuredPosts as $post)
                                    <a href="{{ route('posts.show', $post->slug) }}"
                                        class="list-group-item text-muted py-3 px-2">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <img src="{{ Storage::url($post->image_thumbnail) }}"
                                                    alt="{{ $post->title }}" class="fixed-thumbnail rounded">
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h5 class="fs-15 text-truncate">{{ $post->title }}</h5>
                                                <p class="mb-0 text-truncate">{{ $post->created_at->format('d M, Y') }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-9">
                <div class="row g-4 mb-3">
                    <div class="col-sm-auto">
                        <div>
                            <a href="{{ route('posts.create') }}" class="btn btn-success"><i
                                    class="ri-add-line align-bottom me-1"></i> Thêm mới</a>
                        </div>
                    </div>
                    <div class="col-sm">
                        <div class="d-flex justify-content-sm-end gap-2">
                            <form action="" method="GET" style="display: contents">
                                <div class="search-box ms-2">
                                    <input type="text" class="form-control" placeholder="Search..." name="title"
                                        value="{{ request('title') }}">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                                <div class="ms-2">
                                    <select name="status" class="form-control">
                                        <option value="">--Tất cả trạng thái--</option>
                                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Đã duyệt
                                        </option>
                                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Chưa duyệt
                                        </option>
                                    </select>
                                </div>
                                <div class="d-flex justify-content-start ms-2">
                                    <button type="submit" class="btn btn-success">Tìm kiếm</button>
                                </div>
                            </form>
                        </div>
                    </div>


                </div><!--end row-->

                <div class="col-xxl-12">
                    @forelse ($listPosts as $post)
                        <div
                            class="card position-relative post-card {{ $post->status == 1 ? 'post-published' : 'post-unpublished bg-warning-subtle' }}">
                            <div class="position-absolute top-0 end-0 m-2">
                                <span class="badge rounded-pill {{ $post->status == 1 ? 'bg-success' : 'bg-danger' }}">
                                    <i class="{{ $post->status == 1 ? 'ri-check-double-line' : 'ri-time-line' }}"></i>
                                    {{ $post->status == 1 ? 'Đã xuất bản' : 'Chưa xuất bản' }}
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-xxl-3 col-lg-5">
                                        <div class="post-thumbnail-container rounded">
                                            <img src="{{ Storage::url($post->image_thumbnail) }}"
                                                alt="ảnh {{ $post->title }}" class="post-thumbnail rounded">
                                        </div>
                                    </div>
                                    <div class="col-xxl-9 col-lg-7">
                                        <p class="mb-2 text-primary text-uppercase">
                                            {{ $post->categoryPost?->title ?? 'Không có chuyên mục' }}
                                        </p>

                                        <a href="{{ route('posts.show', $post->id) }}">
                                            <h5 class="fs-15 fw-semibold">{{ $post->title }}</h5>
                                        </a>

                                        <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                            <span class="text-muted"><i class="ri-calendar-event-line me-1"></i>
                                                {{ $post->created_at->format('d/m/Y') }}
                                            </span>
                                            |
                                            <span class="text-muted">
                                                <a href="{{ route('users.show', $post->user->id) }}">
                                                    <i class="ri-user-3-line me-1"></i> {{ $post->user->name }}
                                                </a>
                                            </span>
                                        </div>

                                        <p>{{ $post->short_content }}</p>
                                        <a href="{{ route('posts.show', $post->id) }}" class="text-decoration-underline">
                                            Read more <i class="ri-arrow-right-line"></i>
                                        </a>

                                        <!-- Đưa hành động xuống dưới -->
                                        <div class="post-actions d-flex justify-content-end">
                                            <a href="{{ route('posts.edit', $post->id) }}"
                                                class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                            <form action="{{ route('posts.destroy', $post->id) }}" method="POST"
                                                class="delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-btn"
                                                    title="Xóa">
                                                    <i class="ri-delete-bin-line"></i>
                                                </button>
                                            </form>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <p class="text-muted text-center">Không có bài viết</p>
                    @endforelse
                </div>
                {{ $listPosts->appends(['tab' => 'baiviet'])->links('pagination::bootstrap-5') }}
            </div>
        </div><!--end col-->
    </div><!--end row-->
    </div>
@endsection
