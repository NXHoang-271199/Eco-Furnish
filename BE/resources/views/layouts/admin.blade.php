<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg"
    data-sidebar-image="none" data-preloader="disable" data-theme="default" data-theme-colors="default">


<!-- Mirrored from themesbrand.com/velzon/html/master/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 29 Oct 2024 07:29:52 GMT -->

<head>

    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesbrand" name="author" />

    <title>@yield('title')</title>
    {{-- Điền các link CSS dùng chung --}}
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/admins/images/favicon.ico') }}">
    <link href="{{ asset('assets/libs/quill/quill.core.css" rel="stylesheet" type="text/css')}}" />
    <link href="{{ asset('assets/libs/quill/quill.bubble.css" rel="stylesheet" type="text/css')}}" />
    <link href="{{ asset('assets/libs/quill/quill.snow.css" rel="stylesheet" type="text/css')}}" />
    <!-- jsvectormap css -->
    <link href="{{ asset('assets/admins/libs/jsvectormap/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />

    <!--Swiper slider css-->
    <link href="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Layout config Js -->
    <script src="{{ asset('assets/admins/js/layout.js') }}"></script>
    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/admins/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/admins/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/admins/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- custom Css-->
    <link href="{{ asset('assets/admins/css/custom.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Sweet Alert css-->
    <link href="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Toastify CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    @yield('CSS')
</head>

<body>
    <div id="layout-wrapper">

        @include('admins.blocks.header')

        @include('admins.blocks.siderbar')

        <div class="vertical-overlay"></div>

        <div class="main-content">
            <div class="page-content">
                <x-alert />
                @yield('content')
            </div>

            @include('admins.blocks.footer')

        </div>
    </div>

    {{-- Các đoạn script dùng chung --}}
    <script src="{{ asset('assets/admins/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/admins/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/admins/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('assets/admins/libs/feather-icons/feather.min.js') }}"></script>
    <script src="{{ asset('assets/admins/js/pages/plugins/lord-icon-2.1.0.js') }}"></script>
    <script src="{{ asset('assets/admins/js/plugins.js') }}"></script>

    <!-- apexcharts -->
    <script src="{{ asset('assets/admins/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- Vector map-->
    <script src="{{ asset('assets/admins/libs/jsvectormap/jsvectormap.min.js') }}"></script>
    <script src="{{ asset('assets/admins/libs/jsvectormap/maps/world-merc.js') }}"></script>


    <!--Swiper slider js-->
    <script src="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.js') }}"></script>

  <!-- ckeditor -->
  {{-- <script src="{{ asset('assets/libs/%40ckeditor/ckeditor5-build-classic/build/ckeditor.js')}}"></script> --}}

    <!-- quill js -->
    {{-- <script src="{{ asset('assets/libs/quill/quill.min.js')}}"></script> --}}

    <!-- Dashboard init -->
    <script src="{{ asset('assets/admins/js/pages/dashboard-ecommerce.init.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/admins/js/app.js') }}"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Sweet Alerts js -->
    <script src="{{ asset('assets/admins/libs/sweetalert2/sweetalert2.min.js') }}"></script>
    <!-- Toastify JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    @yield('JS')
</body>

</html>
