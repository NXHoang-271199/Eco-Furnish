<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="index.html" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/admins/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/admins/images/logo-dark.png') }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="index.html" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('assets/admins/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/admins/images/logo-light.png') }}" alt="" height="17">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover"
            id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>
    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown"
            aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <img class="rounded header-profile-user" src="{{ asset('assets/admins/images/users/avatar-1.jpg') }}"
                    alt="Header Avatar">
                <img class="rounded header-profile-user" src="{{ asset('assets/admins/images/users/avatar-1.jpg') }}"
                    alt="Header Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">Anna Adame</span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text"><i
                            class="ri ri-circle-fill fs-10 text-success align-baseline"></i> <span
                            class="align-middle">Online</span></span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <!-- item-->
            <h6 class="dropdown-header">Welcome Anna!</h6>
            <a class="dropdown-item" href="pages-profile.html"><i
                    class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span
                    class="align-middle">Profile</span></a>
            <a class="dropdown-item" href="auth-logout-basic.html"><i
                    class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle"
                    data-key="t-logout">Logout</span></a>
        </div>
    </div>
    <div id="scrollbar">
        <div class="container-fluid">


            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Quản lý</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('dashboard') }}" data-key="t-dashboard">
                        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarUser" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarUser">
                        <i class="ri-account-circle-line"></i> <span data-key="t-advance-ui">Quản lý tài khoản</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarUser">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}" class="nav-link" data-key="t-sweet-alerts">
                                    <i class="ri-team-line"></i>Khách hàng
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('roles.index') }}" class="nav-link" data-key="t-nestable-list">
                                    <i class="ri-user-settings-line"></i>Vai trò
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="" class="nav-link" data-key="t-nestable-list">
                                    <i class="ri-admin-line"></i>Phân quyền
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarArticle" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarArticle">
                        <i class="ri-article-line"></i> <span data-key="t-advance-ui">Quản lý bài viết</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarArticle">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('posts.index') }}" class="nav-link" data-key="t-sweet-alerts">
                                    <i class="ri-newspaper-line"></i>Bài viết
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('category-posts.index') }}" class="nav-link"
                                    data-key="t-nestable-list">
                                    <i class="ri-bookmark-line"></i>Chuyên mục
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <a class="nav-link menu-link" href="{{ route('vouchers.index') }}">
                    <i class="bx bx-gift"></i> <span data-key="t-advance-ui">Quản lý mã giảm giá</span>
                </a>

                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarSanPham" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarSanPham">
                        <i class="ri-store-2-line"></i> <span data-key="t-advance-ui">Quản lý sản phẩm</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarSanPham">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('products.index') }}" class="nav-link" data-key="t-sweet-alerts">
                                    <i class="ri-list-check-2"></i> Danh sách sản phẩm
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('products.create') }}" class="nav-link" data-key="t-nestable-list">
                                    <i class="ri-add-circle-line"></i> Thêm sản phẩm
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('categories.index') }}" class="nav-link" data-key="t-nestable-list">
                                    <i class="ri-folder-2-line"></i> Danh mục sản phẩm
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('variants.index') }}" class="nav-link" data-key="t-nestable-list">
                                    <i class="ri-layout-grid-line"></i> Quản lý biến thể
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="{{ route('comments.index') }}">
                        <i class="ri-chat-1-line"></i> <span data-key="t-advance-ui">Quản lý bình luận</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarPayment" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarPayment">
                        <i class="las la-comments-dollar"></i> <span data-key="t-advance-ui">Quản Lý Thanh Toán</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarPayment">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('payment-methods.index') }}" class="nav-link" data-key="t-sweet-alerts">
                                    Danh sách
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('payment-methods.create') }}" class="nav-link" data-key="t-nestable-list">
                                    Thêm mới
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarOrder" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarOrder">
                        <i class="ri-rocket-line"></i> <span data-key="t-advance-ui">Quản Lý Đơn Hàng</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarOrder">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('orders.index') }}" class="nav-link" data-key="t-sweet-alerts">
                                    Đơn hàng
                                </a>
                                <a href="{{ route('order.notifications') }}" class="nav-link" data-key="t-sweet-alerts">
                                    Thông báo
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">Bán hàng</span></li>

            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
