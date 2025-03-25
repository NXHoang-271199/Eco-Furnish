<!-- Swiper css -->
<link href="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />

<style>
    .product-image-container {
        position: relative;
        background-color: #fff;
        border-radius: 15px;
        padding: 20px;
        max-width: 500px;
        margin: 0 auto;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .product-image-container:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }

    .main-image {
        width: 100%;
        height: 450px;
        object-fit: contain;
        background-color: #fff;
        border-radius: 12px;
        margin-bottom: 20px;
        transition: transform 0.3s ease;
    }

    .main-image:hover {
        transform: scale(1.02);
    }

    .gallery-section {
        position: relative !important;
        margin-top: 25px !important;
        width: 100% !important;
        padding: 20px 40px !important;
        background: #ffffff !important;
        border-radius: 12px !important;
        border: 1px solid #e9ecef !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05) !important;
    }

    .gallery-section h5 {
        font-size: 16px !important;
        margin-bottom: 20px !important;
        color: #2c3e50 !important;
        font-weight: 600 !important;
        letter-spacing: 0.3px !important;
    }

    .gallery-container {
        display: flex !important;
        flex-wrap: nowrap !important;
        gap: 15px !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
        scroll-behavior: smooth !important;
    }

    .gallery-nav-button {
        position: absolute !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 28px !important;
        height: 28px !important;
        border-radius: 50% !important;
        background: rgba(64, 81, 137, 0.8) !important;
        backdrop-filter: blur(4px) !important;
        -webkit-backdrop-filter: blur(4px) !important;
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        color: white !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        z-index: 10 !important;
        padding: 0 !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15) !important;
    }

    .gallery-nav-button:hover {
        background: rgba(64, 81, 137, 1) !important;
        transform: translateY(-50%) scale(1.1) !important;
        box-shadow: 0 4px 12px rgba(64, 81, 137, 0.3) !important;
    }

    .gallery-nav-button.prev {
        left: 8px !important;
    }

    .gallery-nav-button.next {
        right: 8px !important;
    }

    .gallery-nav-button i {
        font-size: 16px !important;
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
        flex: 0 0 90px !important;
        width: 90px !important;
        height: 90px !important;
        position: relative !important;
        border: 2px solid #eef2f7 !important;
        border-radius: 10px !important;
        overflow: hidden !important;
        cursor: pointer !important;
        transition: all 0.3s ease !important;
        background: #fff !important;
    }

    .thumbnail-wrapper:hover {
        border-color: #405189 !important;
        transform: translateY(-3px) !important;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
    }

    .thumbnail-wrapper.active {
        border: 2px solid #405189 !important;
        box-shadow: 0 3px 10px rgba(64,81,137,0.2) !important;
    }

    .thumbnail {
        position: absolute !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        padding: 5px !important;
        transition: transform 0.3s ease !important;
    }

    .thumbnail:hover {
        transform: scale(1.1) !important;
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

    /* Responsive Design */
    @media (max-width: 768px) {
        .main-image {
            height: 350px;
        }
        
        .gallery-container {
            grid-template-columns: repeat(auto-fill, minmax(75px, 1fr)) !important;
            gap: 10px !important;
        }

        .nav-tabs-custom .nav-item .nav-link {
            padding: 10px 15px;
        }
    }

    @media (max-width: 480px) {
        .main-image {
            height: 300px;
        }
        
        .gallery-container {
            grid-template-columns: repeat(auto-fill, minmax(65px, 1fr)) !important;
            gap: 8px !important;
        }
    }
</style>