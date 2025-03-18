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
    </div>
    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Quản lý</span></li>
                @if (Auth::user()->hasPermission('view-dashboard'))
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('dashboard') }}" data-key="t-dashboard">
                            <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboard</span>
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasPermission('view-users') || Auth::user()->hasPermission('view-roles') || auth()->user()->isAdmin())
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarUser" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarUser">
                            <i class="ri-account-circle-line"></i> <span data-key="t-advance-ui">Quản lý tài
                                khoản</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarUser">
                            <ul class="nav nav-sm flex-column">
                                @if (Auth::user()->hasPermission('view-users'))
                                    <li class="nav-item">
                                        <a href="{{ route('users.index') }}" class="nav-link" data-key="t-sweet-alerts">
                                            <i class="ri-team-line"></i>Khách hàng
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->hasPermission('view-roles'))
                                    <li class="nav-item">
                                        <a href="{{ route('roles.index') }}" class="nav-link"
                                            data-key="t-nestable-list">
                                            <i class="ri-user-settings-line"></i>Vai trò
                                        </a>
                                    </li>
                                @endif
                                @if (auth()->user()->isAdmin())
                                    <li class="nav-item">
                                        <a class="nav-link menu-link" href="#sidebarPermissions"
                                            data-bs-toggle="collapse" role="button" aria-expanded="false"
                                            aria-controls="sidebarPermissions">
                                            <i class="ri-shield-keyhole-line"></i> <span data-key="t-permissions">Phân
                                                quyền</span>
                                        </a>
                                        <div class="collapse menu-dropdown" id="sidebarPermissions">
                                            <ul class="nav nav-sm flex-column">
                                                <li class="nav-item">
                                                    <a href="{{ route('admin.permissions.index') }}" class="nav-link">
                                                        <i class="ri-user-settings-line"></i> Vai trò & Quyền
                                                    </a>
                                                </li>
                                                <li class="nav-item">
                                                    <a href="{{ route('admin.permissions.create-permission') }}"
                                                        class="nav-link">
                                                        <i class="ri-key-line"></i> Thêm quyền
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                @if (Auth::user()->hasPermission('view-posts') || Auth::user()->hasPermission('view-category-posts'))
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarArticle" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarArticle">
                            <i class="ri-article-line"></i> <span data-key="t-advance-ui">Quản lý bài viết</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarArticle">
                            <ul class="nav nav-sm flex-column">
                                @if (Auth::user()->hasPermission('view-posts'))
                                    <li class="nav-item">
                                        <a href="{{ route('posts.index') }}" class="nav-link">
                                            <i class="ri-newspaper-line"></i>Bài viết
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->hasPermission('view-category-posts'))
                                    <li class="nav-item">
                                        <a href="{{ route('category-posts.index') }}" class="nav-link">
                                            <i class="ri-bookmark-line"></i>Chuyên mục
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                @if (Auth::user()->hasPermission('view-vouchers'))
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('vouchers.index') }}">
                            <i class="bx bx-gift"></i> <span data-key="t-advance-ui">Quản lý mã giảm giá</span>
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasPermission('view-products') ||
                        Auth::user()->hasPermission('create-products') ||
                        Auth::user()->hasPermission('view-categories') ||
                        Auth::user()->hasPermission('view-variants'))
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarSanPham" data-bs-toggle="collapse"
                            role="button" aria-expanded="false" aria-controls="sidebarSanPham">
                            <i class="ri-store-2-line"></i> <span data-key="t-advance-ui">Quản lý sản phẩm</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarSanPham">
                            <ul class="nav nav-sm flex-column">
                                @if (Auth::user()->hasPermission('view-products'))
                                    <li class="nav-item">
                                        <a href="{{ route('products.index') }}" class="nav-link">
                                            <i class="ri-list-check-2"></i> Danh sách sản phẩm
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->hasPermission('create-products'))
                                    <li class="nav-item">
                                        <a href="{{ route('products.create') }}" class="nav-link">
                                            <i class="ri-add-circle-line"></i> Thêm sản phẩm
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->hasPermission('view-categories'))
                                    <li class="nav-item">
                                        <a href="{{ route('categories.index') }}" class="nav-link">
                                            <i class="ri-folder-2-line"></i> Danh mục sản phẩm
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->hasPermission('view-variants'))
                                    <li class="nav-item">
                                        <a href="{{ route('variants.index') }}" class="nav-link">
                                            <i class="ri-layout-grid-line"></i> Quản lý biến thể
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                @if (Auth::user()->hasPermission('view-comments'))
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="{{ route('comments.index') }}">
                            <i class="ri-chat-1-line"></i> <span data-key="t-advance-ui">Quản lý bình luận</span>
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasPermission('view-payment-methods') || Auth::user()->hasPermission('create-payment-methods'))
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarPayment" data-bs-toggle="collapse"
                            role="button" aria-expanded="false" aria-controls="sidebarPayment">
                            <i class="las la-comments-dollar"></i> <span data-key="t-advance-ui">Quản Lý Thanh
                                Toán</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarPayment">
                            <ul class="nav nav-sm flex-column">
                                @if (Auth::user()->hasPermission('view-payment-methods'))
                                    <li class="nav-item">
                                        <a href="{{ route('payment-methods.index') }}" class="nav-link">
                                            Danh sách
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->hasPermission('create-payment-methods'))
                                    <li class="nav-item">
                                        <a href="{{ route('payment-methods.create') }}" class="nav-link">
                                            Thêm mới
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                @if (Auth::user()->hasPermission('view-orders') || Auth::user()->hasPermission('view-order-notifications'))
                    <li class="nav-item">
                        <a class="nav-link menu-link" href="#sidebarOrder" data-bs-toggle="collapse" role="button"
                            aria-expanded="false" aria-controls="sidebarOrder">
                            <i class="ri-rocket-line"></i> <span data-key="t-advance-ui">Quản Lý Đơn Hàng</span>
                        </a>
                        <div class="collapse menu-dropdown" id="sidebarOrder">
                            <ul class="nav nav-sm flex-column">
                                @if (Auth::user()->hasPermission('view-orders'))
                                    <li class="nav-item">
                                        <a href="{{ route('orders.index') }}" class="nav-link">
                                            Đơn hàng
                                        </a>
                                    </li>
                                @endif
                                @if (Auth::user()->hasPermission('view-order-notifications'))
                                    <li class="nav-item">
                                        <a href="{{ route('order.notifications') }}" class="nav-link">
                                            Thông báo
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                <li class="nav-item">
                    <a class="nav-link menu-link" href="">
                        <i class="ri-settings-4-line"></i> <span data-key="t-advance-ui">Cài đặt</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
