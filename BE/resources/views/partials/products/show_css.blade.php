<!-- Swiper css -->
<link href="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />

<style>
    .product-image-container {
        position: relative !important;
        background-color: #fff !important;
        border-radius: 15px !important;
        padding: 0 !important;
        max-width: 500px !important;
        margin: 0 auto !important;
    }

    .product-image-container:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .main-image {
        width: 100% !important;
        height: 500px !important;
        object-fit: contain !important;
        background-color: #fff !important;
        border-radius: 4px !important;
        margin-bottom: 10px !important;
        transition: transform 0.3s ease;
    }

    .main-image:hover {
        transform: scale(1.02);
    }

    .gallery-section {
        position: relative !important;
        width: 100% !important;
        padding: 0 40px !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
    }

    .gallery-section h5 {
        display: none !important;
    }

    .gallery-container {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 10px !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        scroll-behavior: smooth !important;
        align-items: center !important;
    }

    .gallery-nav-button {
        position: absolute !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 24px !important;
        height: 24px !important;
        border-radius: 50% !important;
        background: rgba(0, 0, 0, 0.5) !important;
        border: none !important;
        color: white !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        z-index: 10 !important;
        padding: 0 !important;
    }

    .gallery-nav-button:hover {
        background: rgba(0, 0, 0, 0.7) !important;
    }

    .gallery-nav-button.prev {
        left: 8px !important;
    }

    .gallery-nav-button.next {
        right: 8px !important;
    }

    .gallery-nav-button i {
        font-size: 14px !important;
        line-height: 1 !important;
    }

    /* Tùy chỉnh thanh cuộn cho đẹp hơn */
    .gallery-container::-webkit-scrollbar {
        height: 6px !important;
        background-color: #f0f0f0 !important;
        border-radius: 3px !important;
    }

    .gallery-container::-webkit-scrollbar-thumb {
        background-color: #405189 !important;
        border-radius: 3px !important;
    }

    .gallery-container::-webkit-scrollbar-track {
        background-color: #f0f0f0 !important;
        border-radius: 3px !important;
    }

    .thumbnail-wrapper {
        flex: 0 0 80px !important;
        width: 80px !important;
        height: 80px !important;
        position: relative !important;
        border: 2px solid #eef2f7 !important;
        border-radius: 8px !important;
        overflow: hidden !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        background: #fff !important;
        opacity: 0.5 !important;
        filter: grayscale(50%) !important;
        transform: scale(0.95) !important;
    }

    .thumbnail-wrapper:hover {
        opacity: 0.8 !important;
        filter: grayscale(20%) !important;
        transform: scale(0.98) !important;
    }

    .thumbnail-wrapper.active {
        border: 2px solid #405189 !important;
        opacity: 1 !important;
    }

    .thumbnail {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        padding: 0 !important;
    }

    /* Product Info Styling */
    .product-content {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }

    .nav-tabs-custom {
        border-bottom: 2px solid #eef2f7;
    }

    .nav-tabs-custom .nav-item .nav-link {
        padding: 15px 25px;
        font-weight: 600;
        color: #495057;
        transition: all 0.3s ease;
    }

    .nav-tabs-custom .nav-item .nav-link.active {
        color: #405189;
        border-bottom: 2px solid #405189;
    }

    .tab-content {
        padding: 25px !important;
        background: #fff;
        border-radius: 0 0 15px 15px;
    }

    .table {
        margin: 0;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
        color: #2c3e50;
    }

    .table td {
        vertical-align: middle;
        color: #495057;
    }

    .badge {
        padding: 8px 12px;
        font-weight: 500;
        letter-spacing: 0.3px;
    }

    .product-title {
        font-size: 24px;
        color: #2c3e50;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .price-section {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin: 20px 0;
    }

    .description-section {
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
        border: 1px solid #eef2f7;
    }

    /* Tùy chỉnh hiển thị cho biến thể hết hàng */
    tr.opacity-50 {
        opacity: 0.5;
        background-color: #f9f9f9;
    }

    .badge.bg-warning-subtle {
        background-color: #fff8e1 !important;
    }

    .badge.text-warning {
        color: #ff9800 !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .main-image {
            height: 350px !important;
        }
        
        .thumbnail-wrapper {
            flex: 0 0 60px !important;
            width: 60px !important;
            height: 60px !important;
        }
    }

    @media (max-width: 480px) {
        .main-image {
            height: 300px !important;
        }
    }

    /* Hiệu ứng Pulse cho các sự kiện click */
    .pulse {
        animation: pulse 0.6s ease-out;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(64, 81, 137, 0.4);
        }
        70% {
            transform: scale(1.03);
            box-shadow: 0 0 0 10px rgba(64, 81, 137, 0);
        }
        100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(64, 81, 137, 0);
        }
    }
    
    /* Hiệu ứng chuyển đổi tab mượt mà */
    .nav-link.active {
        position: relative;
        overflow: hidden;
    }
    
    .nav-link.active::before {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background-color: #405189;
        animation: slideInHorizontal 0.4s ease forwards;
    }
    
    @keyframes slideInHorizontal {
        from {
            transform: scaleX(0);
            transform-origin: left;
        }
        to {
            transform: scaleX(1);
            transform-origin: left;
        }
    }
    
    /* Hiệu ứng nút chuyển tab */
    .btn-switch-tab {
        animation: bounce 0.5s ease-in-out infinite alternate;
        animation-iteration-count: 3;
    }
    
    @keyframes bounce {
        0% {
            transform: translateY(0);
        }
        100% {
            transform: translateY(-5px);
        }
    }
    
    /* Hiệu ứng hiển thị nội dung tab */
    .tab-pane.show.active {
        animation: fadeInUp 0.5s ease forwards;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Hiệu ứng hover cho các mục biến thể */
    .variant-detail-item {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    
    .variant-detail-item::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(64, 81, 137, 0.05);
        border-radius: 6px;
        z-index: -1;
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }
    
    .variant-detail-item:hover::after {
        transform: scaleX(1);
    }
    
    /* Hiệu ứng loading skeleton cho khi nội dung đang tải */
    .skeleton-loading {
        position: relative;
        overflow: hidden;
        background-color: #f7f7f7;
        border-radius: 4px;
    }
    
    .skeleton-loading::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, 
            rgba(255, 255, 255, 0) 0%, 
            rgba(255, 255, 255, 0.2) 50%, 
            rgba(255, 255, 255, 0) 100%);
        animation: shimmer 1.5s infinite;
    }
    
    @keyframes shimmer {
        0% {
            transform: translateX(-100%);
        }
        100% {
            transform: translateX(100%);
        }
    }
    
    /* Hiệu ứng shadow hover cho bảng */
    .variant-table {
        transition: box-shadow 0.3s ease;
    }
    
    .variant-table:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }
    
    /* Hiệu ứng cuộn mượt mà */
    html {
        scroll-behavior: smooth;
    }
    
    /* Hiệu ứng thông báo nhấp nháy cho biến thể đang bán chạy */
    .status-badge.in-stock {
        position: relative;
    }
    
    .status-badge.in-stock::before {
        content: '';
        display: block;
        width: 6px;
        height: 6px;
        background: #10c469;
        border-radius: 50%;
        position: absolute;
        left: -3px;
        top: 50%;
        transform: translateY(-50%);
        animation: blink 2s infinite;
    }
    
    @keyframes blink {
        0% {
            opacity: 0;
        }
        50% {
            opacity: 1;
        }
        100% {
            opacity: 0;
        }
    }
</style>