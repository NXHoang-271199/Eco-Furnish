@extends('layouts.admin')

@section('title')
    Bài Viết
@endsection
@section('CSS')
    <style>
        .thumnail-sm {
            max-width: 200px;
            max-height: 200px;
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

        <style>

        /* Tùy chỉnh nav-tabs */
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

        /* Cải thiện hiệu ứng chuyển đổi tab */
        .tab-pane {
            animation: fadeEffect 0.3s ease-in-out;
        }

        @keyframes fadeEffect {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>

    </style>
@endsection

@section('JS')
    <script>
        $(document).on('click', '.delete-user', function() {
            let userId = $(this).data('id');
            let url = `/users/${userId}`;

            if (confirm("Bạn có chắc chắn muốn xóa người dùng này không?")) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        showNotification(response.message, 'success');
                        $(`#user-row-${userId}`).remove();
                    },
                    error: function(xhr) {
                        showNotification(xhr.responseJSON.message, 'error');
                    }
                });
            }
        });

        function showNotification(message, type) {
            let notificationHtml = `
        <div class="alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
            $("#notificationItemsTabContent").prepend(notificationHtml);
        }
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
            <div class="col-xxl-3">
                <div class="card">
                    <div class="card-body p-4">
                        <div class="pt-4 border-top border-dashed border-bottom-0 border-start-0 border-end-0">
                            <p class="text-muted">Chuyên mục</p>

                            <ul class="list-unstyled fw-medium">
                                @foreach ($listCategoryPost as $category)
                                    <li><a href="" class="text-muted py-2 d-block"><i
                                                class="mdi mdi-chevron-right me-1"></i>{{ $category->title }}</a></li>
                                @endforeach
                        </div>

                        <div class="pt-4 border-top border-dashed border-bottom-0 border-start-0 border-end-0">
                            <p class="text-muted">Lưu trữ</p>

                            <ul class="list-unstyled fw-medium">
                                @foreach ($years as $year)
                                    <li>
                                        <a href="javascript: void(0);" class="text-muted py-2 d-block">
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
                                    <a href="{{ route('posts.show', $post->id) }}"
                                        class="list-group-item text-muted py-3 px-2">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0 me-3">
                                                <img src="{{ Storage::url($post->image_thumbnail) }}"
                                                    alt="{{ $post->title }}"
                                                    class="avatar-md h-auto d-block rounded thumnail-sm">
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
                                    <input type="text" class="form-control" placeholder="Search..." name="title">
                                    <i class="ri-search-line search-icon"></i>
                                </div>
                                <div class="d-flex justify-content-start">
                                    <button type="submit" class="btn btn-success">Tìm kiếm</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div><!--end row-->
                <ul class="nav nav-tabs" id="contentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="list-tab" data-bs-toggle="tab" data-bs-target="#danhsach"
                            type="button" role="tab">Bài viết cần duyệt</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="posts-tab" data-bs-toggle="tab" data-bs-target="#baiviet"
                            type="button" role="tab">Bài viết</button>
                    </li>
                </ul>

                <div class="tab-content mt-3" id="contentTabsContent">
                    <div class="tab-pane fade show active tab-bg" id="danhsach" role="tabpanel">
                        <div class="table-responsive table-card">
                            <table class="table table-nowrap mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Tên bài</th>
                                        <th scope="col">Ngày đăng</th>
                                        <th scope="col">Trạng thái</th>
                                        <th scope="col">Duyệt</th>
                                    </tr>
                                </thead>
                                <tbody class="align-middle">
                                    @foreach ($listPosts->where('status', 0) as $key => $post)
                                        <tr>
                                            <td>{{ $post->title }}</td>
                                            <td>{{ $post->created_at }}</td>
                                            <td>
                                                @if ($post->status == 0)
                                                    <span class="badge bg-danger">Chưa duyệt</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('posts.approve', $post->id) }}" method="POST"
                                                    style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success">
                                                        <i class="ri-checkbox-circle-line"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="baiviet" role="tabpanel">
                        <div class="col-xxl-12">
                            @foreach ($listPosts->where('status', 1) as $key => $post)
                                <div class="card position-relative">
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-xxl-3 col-lg-5">
                                                <img src="{{ Storage::url($post->image_thumbnail) }}"
                                                    alt="ảnh {{ $post->title }}"
                                                    class="img-fluid rounded w-100 object-fit-cover thumnail-sm">
                                            </div>
                                            <div class="col-xxl-9 col-lg-7">
                                                <p class="mb-2 text-primary text-uppercase">
                                                    {{ $post->categoryPost->title }}</p>
                                                <a href="{{ route('posts.show', $post->id) }}">
                                                    <h5 class="fs-15 fw-semibold">{{ $post->title }}</h5>
                                                </a>
                                                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                                    <span class="text-muted"><i
                                                            class="ri-calendar-event-line me-1"></i>{{ $post->created_at->format('d/m/Y') }}</span>
                                                    | <span class="text-muted"> |
                                                        <a href="{{ route('users.show', $post->user->id) }}"><i
                                                                class="ri-user-3-line me-1"></i>{{ $post->user->name }}</a>
                                                </div>
                                                <p>{{ $post->short_content }}</p>
                                                <a href="{{ route('posts.show', $post->id) }}"
                                                    class="text-decoration-underline">Read more <i
                                                        class="ri-arrow-right-line"></i></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="button-list position-absolute top-0 end-0 d-flex gap-2 mx-2 my-2">
                                        <a href="{{ route('posts.edit', $post->id) }}" class="btn btn-ghost-success">
                                            <i class="ri-edit-line"></i>
                                        </a>
                                        <form action="{{ route('posts.destroy', $post->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-ghost-danger waves-effect waves-light material-shadow-none">
                                                <i class="ri-delete-bin-line"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        {{ $listPosts->links('pagination::bootstrap-5') }}
                    </div>

                </div>




            </div><!--end col-->
        </div><!--end row-->


    </div>
@endsection
