  <!-- nouisliderute css -->
  <link rel="stylesheet" href="{{ asset('assets/admins/libs/nouislider/nouislider.min.css') }}">
    <!-- gridjs css -->
    <link rel="stylesheet" href="{{ asset('assets/admins/libs/gridjs/theme/mermaid.min.css') }}">
    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        /* Card styling */
        .card {
            border: none;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1.25rem 1.5rem;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        /* Table styling */
        .table {
            margin-bottom: 0;
        }
        
        .table th {
            font-weight: 600;
            color: #495057;
            border-bottom-width: 1px;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
            border-color: #f0f0f0;
            position: relative;
        }
        
        /* Đảm bảo cột thao tác có đủ không gian */
        .table td:last-child {
            padding-right: 1.5rem;
            min-width: 100px;
        }
        
        .table-light {
            background-color: #f8f9fa;
        }
        
        /* Product image styling */
        .avatar-sm {
            height: 3.5rem;
            width: 3.5rem;
            transition: transform 0.3s ease;
            overflow: hidden;
        }
        
        .avatar-sm:hover {
            transform: scale(1.05);
        }
        
        .avatar-sm img {
            object-fit: cover;
            height: 100%;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .avatar-sm img:hover {
            transform: scale(1.1);
        }
        
        /* Buttons styling */
        .btn {
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn i {
            font-size: 1rem;
        }
        
        .btn-primary {
            background-color: #405189;
            border-color: #405189;
        }
        
        .btn-primary:hover {
            background-color: #364574;
            border-color: #364574;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(64, 81, 137, 0.2);
        }
        
        .btn-success {
            background-color: #0ab39c;
            border-color: #0ab39c;
        }
        
        .btn-success:hover {
            background-color: #099885;
            border-color: #099885;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(10, 179, 156, 0.2);
        }
        
        .btn-info {
            background-color: #299cdb;
            border-color: #299cdb;
        }
        
        .btn-info:hover {
            background-color: #2589c1;
            border-color: #2589c1;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(41, 156, 219, 0.2);
        }
        
        /* Category filter styling */
        .category-filter {
            cursor: pointer;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            margin-bottom: 6px;
            border: 1px solid transparent;
        }
        
        .category-filter:hover {
            background-color: #f8f9fa;
            border-color: #e9ebec;
        }
        
        .category-filter.active {
            background-color: #405189;
            color: #fff !important;
            border-color: #405189;
            box-shadow: 0 5px 10px rgba(64, 81, 137, 0.2);
        }
        
        .category-filter.active .listname {
            color: #fff !important;
        }

        /* Price Range Slider Styles */
        .price-range-wrapper {
            padding: 10px 5px;
        }

        .noUi-connect {
            background: #405189;
        }

        .noUi-handle {
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }

        .noUi-handle:before,
        .noUi-handle:after {
            display: none;
        }

        .price-input {
            position: relative;
            padding: 10px 15px;
            background: #fff;
            border: 1px solid #e2e5e8;
            border-radius: 8px;
            font-size: 14px;
            color: #495057;
            width: 100%;
            transition: all 0.3s ease;
        }

        .price-input:focus {
            border-color: #405189;
            outline: none;
            box-shadow: 0 0 0 3px rgba(64, 81, 137, 0.1);
        }

        .price-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 15px;
        }

        .price-group .price-field {
            flex: 1;
        }

        .price-separator {
            color: #6c757d;
            font-weight: 500;
        }

        .price-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .filter-button {
            flex: 1;
            padding: 12px;
            background: #405189;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .filter-button:hover {
            background: #364574;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(64, 81, 137, 0.2);
        }

        .filter-button i {
            font-size: 1rem;
        }

        .filter-button-reset {
            background: #6c757d;
        }

        .filter-button-reset:hover {
            background: #5a6268;
            box-shadow: 0 5px 12px rgba(108, 117, 125, 0.2);
        }

        .d-flex.gap-2 {
            gap: 0.8rem !important;
        }

        /* Thêm style cho phân trang */
        .pagination-wrapper {
            margin-top: 1.5rem;
            padding: 1rem 1.5rem;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .pagination-info {
            color: #878a99;
            font-size: 14px;
        }

        .pagination {
            margin: 0;
            display: flex;
            gap: 5px;
        }

        .pagination .page-link {
            border: 1px solid #e9ebec;
            color: #405189;
            background-color: #fff;
            padding: 0.5rem 0.75rem;
            font-size: 14px;
            font-weight: 500;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
        }

        .pagination .page-link:hover {
            background-color: #405189;
            color: #fff;
            border-color: #405189;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(64, 81, 137, 0.2);
        }

        .pagination .page-link.disabled {
            color: #878a99;
            pointer-events: none;
            background-color: #f3f6f9;
            border-color: #e9ebec;
        }

        .pagination .page-link.active {
            background-color: #405189;
            color: #fff;
            border-color: #405189;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(64, 81, 137, 0.2);
        }

        /* Buttons styling */
        .btn-soft-danger {
            color: #f06548;
            background-color: rgba(240, 101, 72, 0.1);
            border-color: transparent;
            transition: all 0.2s ease;
        }

        .btn-soft-danger:hover {
            color: #fff;
            background-color: #f06548;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(240, 101, 72, 0.2);
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 37px;
            width: 37px;
            padding: 0;
            border-radius: 50%;
        }

        .btn-icon.btn-sm {
            height: 32px;
            width: 32px;
            line-height: 32px;
        }

        .fs-16 {
            font-size: 16px !important;
        }

        /* Dropdown menu styling */
        .dropdown-menu {
            padding: 0.5rem 0;
            font-size: 0.875rem;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            min-width: 10rem;
            z-index: 1050; /* Đảm bảo dropdown hiển thị trên cùng */
        }
        
        /* Fix cho dropdown menu khi ở cuối bảng */
        .dropdown-menu-end {
            right: 0 !important;
            left: auto !important;
        }
        
        /* Đảm bảo dropdown không bị ẩn */
        .table-responsive {
            overflow-x: visible !important;
            overflow-y: visible !important;
        }
        
        /* Đảm bảo dropdown có vị trí tuyệt đối so với nút toggle */
        .dropdown {
            position: relative !important;
        }
        
        /* Hiển thị dropdown ở lớp cao nhất, không bị ảnh hưởng bởi hover của dòng khác */
        .dropdown-menu.show {
            z-index: 9999 !important;
            position: absolute !important;
        }
        
        /* Sửa lỗi dropdown bị che bởi hiệu ứng hover */
        #orderTable tr:hover {
            z-index: 1;
            position: relative;
        }
        
        /* Đảm bảo dropdown sẽ hiển thị trên hiệu ứng hover của các dòng khác */
        #orderTable tr.has-dropdown-open {
            z-index: 1060 !important;
            position: relative;
        }
        
        .dropdown-item {
            padding: 0.5rem 1.2rem;
            font-weight: 500;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .dropdown-item:hover {
            background-color: rgba(64, 81, 137, 0.08);
        }
        
        .dropdown-item i {
            margin-right: 8px;
            font-size: 1rem;
            vertical-align: -2px;
        }
        
        .dropdown-divider {
            margin: 0.5rem 0;
        }

        /* Style cho ô tìm kiếm sản phẩm */
        .filter-search {
            position: relative;
            margin-bottom: 20px;
        }

        .filter-search input {
            width: 100%;
            padding: 12px 20px;
            padding-right: 40px;
            border: 1px solid #e2e5e8;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .filter-search input:focus {
            border-color: #405189;
            outline: none;
            box-shadow: 0 0 0 3px rgba(64, 81, 137, 0.1);
        }

        .filter-search i {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 18px;
        }

        /* Style cho offcanvas filter */
        .offcanvas-filter {
            position: fixed;
            top: 0;
            left: -350px;
            width: 320px;
            height: 100%;
            background: #fff;
            z-index: 1045;
            transition: all 0.3s ease;
            box-shadow: 0 0 25px rgba(0,0,0,0.1);
            overflow-y: auto;
        }

        .offcanvas-filter.show {
            left: 0;
        }

        .offcanvas-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1040;
            display: none;
            backdrop-filter: blur(2px);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .offcanvas-backdrop.show {
            display: block;
            opacity: 1;
        }
        
        /* Filter header */
        .filter-section {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        
        .filter-section:last-child {
            border-bottom: none;
        }
        
        .filter-section p.text-uppercase {
            font-weight: 600;
            color: #495057;
            margin-bottom: 15px;
        }
        
        /* Price styling */
        .text-danger {
            color: #f06548 !important;
        }
        
        .text-decoration-line-through {
            text-decoration: line-through;
        }
        
        /* Animation effects */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .card {
            animation: fadeIn 0.5s ease-out;
        }
        
        /* Tối giản hóa thanh trượt */
        .noUi-target {
            height: 6px;
            border: none;
            box-shadow: none;
            background: #e9ecef;
            margin: 15px 0 30px;
            border-radius: 6px;
        }

        .noUi-handle {
            width: 18px !important;
            height: 18px !important;
            border-radius: 50%;
            box-shadow: none;
            border: 2px solid #405189;
            background: #fff;
            cursor: pointer;
            right: -9px !important;
            top: -6px !important;
            transition: all 0.2s ease;
        }
        
        .noUi-handle:hover {
            transform: scale(1.1);
            box-shadow: 0 0 0 5px rgba(64, 81, 137, 0.1);
        }

        .noUi-connect {
            background: #405189;
        }
        
        /* Product name styling */
        .fs-14 {
            font-size: 14px;
        }
        
        a.text-dark {
            color: #212529;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        a.text-dark:hover {
            color: #405189;
        }
        
        /* Fix cho dropdown menu khi bị cắt ở cuối trang */
        #orderTable tr:last-child .dropdown-menu,
        #orderTable tr:nth-last-child(2) .dropdown-menu {
            bottom: 100%;
            top: auto !important;
            margin-bottom: 5px;
        }
        
        /* Sửa lỗi hiển thị ban đầu (ẩn sản phẩm trước khi animation) */
        #orderTable tbody tr {
            opacity: 0;
            transform: translateY(20px);
        }
        
        /* Responsive fixes */
        @media (max-width: 767.98px) {
            .pagination-wrapper .d-flex {
                flex-direction: column;
                gap: 10px;
            }
            
            .pagination {
                justify-content: center;
            }
            
            .pagination-info {
                text-align: center;
            }
            
            .offcanvas-filter {
                width: 280px;
            }
            
            /* Đảm bảo dropdown không bị cắt trên mobile */
            .dropdown-menu {
                position: fixed !important;
                top: auto !important;
                left: 50% !important;
                bottom: 20% !important;
                transform: translateX(-50%) !important;
                width: 80% !important;
                max-width: 250px;
            }
        }

        .ripple-effect {
            animation-name: ripple;
        }
            
        .fadeInDown {
            animation-name: fadeInDown;
        }
        
        .fadeOutUp {
            animation-name: fadeOutUp;
        }
        
        .faster {
            animation-duration: 0.3s;
        }

        /* Badge styling */
        .badge {
            padding: 0.35em 0.65em;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            display: inline-block;
        }

        .bg-success-subtle {
            background-color: rgba(10, 179, 156, 0.15);
        }

        .text-success {
            color: #0ab39c !important;
        }

        .bg-danger-subtle {
            background-color: rgba(240, 101, 72, 0.15);
        }

        .text-danger {
            color: #f06548 !important;
        }
    </style>