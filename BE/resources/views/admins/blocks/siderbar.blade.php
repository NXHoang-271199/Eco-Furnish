<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="#" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/admins/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/admins/images/logo-dark.png') }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="#" class="logo logo-light">
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
                <img class="rounded header-profile-user" src="{{ asset('assets/admins/images/users/avatar-1.jpg') }}" alt="Header Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">Admin</span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text"><i
                            class="ri ri-circle-fill fs-10 text-success align-baseline"></i> <span
                            class="align-middle">Online</span></span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <!-- item-->
            <h6 class="dropdown-header">Xin chào Admin!</h6>
            <a class="dropdown-item" href="#">
                <i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> 
                <span class="align-middle">Thông tin cá nhân</span>
            </a>
            <a class="dropdown-item" href="#">
                <i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> 
                <span class="align-middle">Đăng xuất</span>
            </a>
        </div>
    </div>
    <div id="scrollbar">
        <div class="container-fluid">


            <div id="two-column-menu">
            </div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Quản lý</span></li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#" data-key="t-dashboard">
                        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
<<<<<<< HEAD
                    <a class="nav-link menu-link" href="#sidebarPayment" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarPayment">
                        <i class="las la-comments-dollar"></i> <span data-key="t-advance-ui">Quản Lý Thanh Toán</span>
=======
                    <a class="nav-link menu-link" href="#sidebarSanPham" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarSanPham" data-key="t-product-management">
                        <i class="ri-store-2-line"></i> <span data-key="t-advance-ui">Quản lý sản phẩm</span>
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarPayment">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
<<<<<<< HEAD
                                <a href="{{ route('payment-methods.index') }}" class="nav-link" data-key="t-sweet-alerts">
                                    Danh sách
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('payment-methods.create') }}" class="nav-link" data-key="t-nestable-list">
                                    Thêm mới
=======
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
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
<<<<<<< HEAD
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
=======
                <!-- <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarOrders" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarOrders" data-key="t-order-management">
                        <i class="ri-shopping-cart-2-line"></i>
                        <span data-key="t-orders">Quản lý đơn hàng</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarOrders">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link" data-key="t-order-list">
                                    <i class="ri-file-list-line"></i> Danh sách đơn hàng
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451
                                </a>
                            </li>
                        </ul>
                    </div>
<<<<<<< HEAD
                </li>
=======
                </li> -->
>>>>>>> 111b2cf7b331a3bd56268381dce795463d612451

                
                <!-- <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarCustomers" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarCustomers" data-key="t-customer-management">
                        <i class="ri-user-2-line"></i>
                        <span data-key="t-customers">Quản lý khách hàng</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarCustomers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link" data-key="t-customer-list">
                                    <i class="ri-team-line"></i> Danh sách khách hàng
                                </a>
                            </li>
                        </ul>
                    </div>
                </li> -->
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
