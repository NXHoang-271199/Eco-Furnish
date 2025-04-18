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
</style>