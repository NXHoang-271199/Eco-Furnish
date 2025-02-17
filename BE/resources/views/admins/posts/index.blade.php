@extends('layouts.admin')

@section('title')
    Bài Viết
@endsection

@section('content')
<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">Danh sách bài Viết</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Bài Viết</a></li>
                        <li class="breadcrumb-item active">Bài Viết</li>
                    </ol>
                </div>

            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-xxl-3">
            <div class="card">
                <div class="card-body p-4">
                    <div class="mt-4 pt-4 border-top border-dashed border-bottom-0 border-start-0 border-end-0">
                        <p class="text-muted">Chuyên mục</p>

                        <ul class="list-unstyled fw-medium">
                            <li><a href="javascript: void(0);" class="text-muted py-2 d-block"><i class="mdi mdi-chevron-right me-1"></i> Art &amp; Design</a></li>
                    </div>

                    <div class="mt-4 pt-4 border-top border-dashed border-bottom-0 border-start-0 border-end-0">
                        <p class="text-muted">Lưu trữ</p>

                        <ul class="list-unstyled fw-medium">
                            <li><a href="javascript: void(0);" class="text-muted py-2 d-block"><i class="mdi mdi-chevron-right me-1"></i> 2024 <span class="badge badge-soft-success rounded-pill float-end ms-1 font-size-12">03</span></a></li>
                        </ul>
                    </div>

                    <div class="mt-4 pt-4 border-top border-dashed border-bottom-0 border-start-0 border-end-0">
                        <p class="text-muted mb-2">Bài viết nổi bật</p>

                        <div class="list-group list-group-flush">
                            <a href="javascript: void(0);" class="list-group-item text-muted py-3 px-2">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <img src="assets/images/small/img-7.jpg" alt="" class="avatar-md h-auto d-block rounded">
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="fs-15 text-truncate">Beautiful Day with Friends</h5>
                                        <p class="mb-0 text-truncate">10 Apr, 2024</p>
                                    </div>
                                </div>
                            </a>

                            <a href="javascript: void(0);" class="list-group-item text-muted py-3 px-2">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <img src="assets/images/small/img-4.jpg" alt="" class="avatar-md h-auto d-block rounded">
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="fs-15 text-truncate">Drawing a sketch</h5>
                                        <p class="mb-0 text-truncate">24 Mar, 2024</p>
                                    </div>
                                </div>
                            </a>

                            <a href="javascript: void(0);" class="list-group-item text-muted py-3 px-2">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <img src="assets/images/small/img-6.jpg" alt="" class="avatar-md h-auto d-block rounded">
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <h5 class="fs-15 text-truncate">Project discussion with team</h5>
                                        <p class="mb-0 text-truncate">11 Mar, 2024</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xxl-9">
            <div class="row g-4 mb-3">
                <div class="col-sm-auto">
                    <div>
                        <a href="{{ route('posts.create') }}" class="btn btn-success"><i class="ri-add-line align-bottom me-1"></i> Thêm mới</a>
                    </div>
                </div>
                <div class="col-sm">
                    <div class="d-flex justify-content-sm-end gap-2">
                        <div class="search-box ms-2">
                            <input type="text" class="form-control" placeholder="Search...">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                        <div class="d-flex justify-content-start">
                            <button type="submit" class="btn btn-success">Tìm kiếm</button>
                        </div>
                    </div>
                </div>
            </div><!--end row-->
            <div class="row gx-4">
                <div class="col-xxl-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-xxl-3 col-lg-5">
                                    <img src="assets/images/blog/img-1.jpg" alt="" class="img-fluid rounded w-100 object-fit-cover">
                                </div><!--end col-->
                                <div class="col-xxl-9 col-lg-7">
                                    <p class="mb-2 text-primary text-uppercase">Modern aesthetics</p>
                                    <a href="">
                                        <h5 class="fs-15 fw-semibold">The Evolution of Minimalism in Design</h5>
                                    </a>
                                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                        <span class="text-muted"><i class="ri-calendar-event-line me-1"></i> 05 Apr, 2024</span> | <span class="text-muted"><i class="ri-eye-line me-1"></i> 451</span> | <a href="pages-profile.html"><i class="ri-user-3-line me-1"></i> Admin</a>
                                    </div>
                                    <p class="text-muted mb-2">Minimalism is more than just a trend; it's a movement that continues to influence design worldwide. Learn how simplicity in design evolved and why it remains impactful today.</p>
                                    <a href="" class="text-decoration-underline">Read more <i class="ri-arrow-right-line"></i></a>
                                </div><!--end col-->
                            </div><!--end row-->
                        </div>
                    </div>
                </div><!--end col-->
            </div><!--end row-->
            <div class="row g-0 text-center text-sm-start align-items-center mb-4">
                <div class="col-sm-6">
                    <div>
                        <p class="mb-sm-0 text-muted">Showing <span class="fw-semibold">1</span> to <span class="fw-semibold">6</span> of <span class="fw-semibold text-decoration-underline">21</span> entries</p>
                    </div>
                </div>
                <!-- end col -->
                <div class="col-sm-6">
                    <ul class="pagination pagination-separated justify-content-center justify-content-sm-end mb-sm-0">
                        <li class="page-item disabled">
                            <a href="#" class="page-link">Previous</a>
                        </li>
                        <li class="page-item">
                            <a href="#" class="page-link">1</a>
                        </li>
                        <li class="page-item active">
                            <a href="#" class="page-link">2</a>
                        </li>
                        <li class="page-item">
                            <a href="#" class="page-link">3</a>
                        </li>
                        <li class="page-item">
                            <a href="#" class="page-link">4</a>
                        </li>
                        <li class="page-item">
                            <a href="#" class="page-link">Next</a>
                        </li>
                    </ul>
                </div><!-- end col -->
            </div><!--end row-->
        </div><!--end col-->
    </div><!--end row-->


</div>
@endsection
