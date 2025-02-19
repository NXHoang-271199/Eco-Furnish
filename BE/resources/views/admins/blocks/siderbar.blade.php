<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/admins/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/admins/images/logo-dark.png') }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
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
                    <a class="nav-link menu-link" href="{{ route('admin.dashboard') }}">
                        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarProducts" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarProducts">
                        <i class="ri-store-2-line"></i> <span data-key="t-products">Quản lý sản phẩm</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarProducts">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('admin.products.index') }}" class="nav-link">
                                    <i class="ri-list-check-2"></i> Danh sách sản phẩm
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.products.create') }}" class="nav-link">
                                    <i class="ri-add-circle-line"></i> Thêm sản phẩm
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.categories.index') }}" class="nav-link">
                                    <i class="ri-folder-2-line"></i> Danh mục sản phẩm
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('admin.variants.index') }}" class="nav-link">
                                    <i class="ri-layout-grid-line"></i> Quản lý biến thể
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarOrders" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarOrders">
                        <i class="ri-shopping-cart-2-line"></i>
                        <span data-key="t-orders">Quản lý đơn hàng</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarOrders">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="ri-file-list-line"></i> Danh sách đơn hàng
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarCustomers" data-bs-toggle="collapse" role="button"
                        aria-expanded="false" aria-controls="sidebarCustomers">
                        <i class="ri-user-2-line"></i>
                        <span data-key="t-customers">Quản lý khách hàng</span>
                    </a>
                    <div class="collapse menu-dropdown" id="sidebarCustomers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link">
                                    <i class="ri-team-line"></i> Danh sách khách hàng
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>

    <div class="sidebar-background"></div>
</div>
