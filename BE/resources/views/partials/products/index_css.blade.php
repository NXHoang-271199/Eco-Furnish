  <!-- nouisliderute css -->
  <link rel="stylesheet" href="{{ asset('assets/admins/libs/nouislider/nouislider.min.css') }}">
    <!-- gridjs css -->
    <link rel="stylesheet" href="{{ asset('assets/admins/libs/gridjs/theme/mermaid.min.css') }}">

    <style>
        .category-filter {
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .category-filter:hover {
            background-color: #f8f9fa;
        }
        
        .category-filter.active {
            background-color: #405189;
            color: #fff !important;
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
            padding: 8px 12px;
            background: #fff;
            border: 1px solid #e2e5e8;
            border-radius: 4px;
            font-size: 14px;
            color: #495057;
            width: 100%;
        }

        .price-input:focus {
            border-color: #405189;
            outline: none;
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
            padding: 10px;
            background: #405189;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .filter-button:hover {
            background: #364574;
        }

        .filter-button i {
            margin-right: 8px;
        }

        .filter-button-reset {
            background: #6c757d;
        }

        .filter-button-reset:hover {
            background: #5a6268;
        }

        .d-flex.gap-2 {
            gap: 0.5rem !important;
        }

        /* Thêm style cho mũi tên phân trang */
        .pagination-nav {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
        }

        .pagination-nav a {
            color: #6c757d;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .pagination-nav a:hover {
            background-color: #f8f9fa;
        }

        .pagination-info {
            color: #6c757d;
            font-size: 14px;
        }

        /* Tối giản hóa thanh trượt */
        .noUi-target {
            height: 4px;
            border: none;
            box-shadow: none;
            background: #e9ecef;
            margin: 15px 0 30px;
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
        }

        .noUi-connect {
            background: #405189;
        }

        /* Ẩn các đường kẻ trong handle */
        .noUi-handle:before,
        .noUi-handle:after {
            display: none;
        }

        /* Style cho input giá */
        .price-input {
            border: 1px solid #e2e5e8;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
            color: #495057;
            width: 100%;
            transition: all 0.3s ease;
        }

        .price-input:focus {
            border-color: #405189;
            outline: none;
            box-shadow: 0 0 0 2px rgba(64, 81, 137, 0.1);
        }

        /* Style cho ô tìm kiếm sản phẩm */
        .filter-search {
            position: relative;
            margin-bottom: 20px;
        }

        .filter-search input {
            width: 100%;
            padding: 10px 15px;
            padding-right: 35px;
            border: 1px solid #e2e5e8;
            border-radius: 4px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .filter-search input:focus {
            border-color: #405189;
            outline: none;
            box-shadow: 0 0 0 2px rgba(64, 81, 137, 0.1);
        }

        .filter-search i {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }

        /* Style cho offcanvas filter */
        .offcanvas-filter {
            position: fixed;
            top: 0;
            left: -100%;
            width: 300px;
            height: 100%;
            background: #fff;
            z-index: 1045;
            transition: all 0.3s ease;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
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
        }

        .offcanvas-backdrop.show {
            display: block;
        }

        /* Style cho phân trang */
        .pagination-wrapper {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #fff;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px rgba(56, 65, 74, 0.15);
        }

        .pagination-info {
            color: #878a99;
            font-size: 13px;
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
            font-size: 13px;
            font-weight: 500;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background-color: #405189;
            color: #fff;
            border-color: #405189;
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
        }

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

        /* Hiệu ứng hover cho tooltip */
        .tooltip {
            position: absolute;
            z-index: 1070;
            display: block;
            margin: 0;
            font-style: normal;
            font-weight: 400;
            line-height: 1.5;
            text-align: left;
            text-decoration: none;
            text-shadow: none;
            text-transform: none;
            letter-spacing: normal;
            word-break: normal;
            word-spacing: normal;
            white-space: normal;
            line-break: auto;
            font-size: 0.875rem;
            opacity: 0;
            transition: opacity 0.15s;
        }

        .tooltip.show {
            opacity: 1;
        }

        .tooltip-inner {
            max-width: 200px;
            padding: 0.25rem 0.5rem;
            color: #fff;
            text-align: center;
            background-color: #000;
            border-radius: 0.25rem;
        }
    </style>