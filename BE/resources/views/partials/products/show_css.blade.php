 <!-- Swiper css -->
 <link href="{{ asset('assets/admins/libs/swiper/swiper-bundle.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .product-image-container {
            position: relative;
            background-color: #fff;
            border-radius: 8px;
            padding: 10px;
            max-width: 500px;
            margin: 0 auto;
        }

        .main-image {
            width: 100%;
            height: 400px;
            object-fit: contain;
            background-color: #fff;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .gallery-section {
            margin-top: 20px !important;
            width: 100% !important;
            padding: 15px !important;
            background: #f8f9fa !important;
            border-radius: 8px !important;
            border: 1px solid #e9ecef !important;
        }

        .gallery-section h5 {
            font-size: 14px !important;
            margin-bottom: 15px !important;
            color: #495057 !important;
            font-weight: 600 !important;
        }

        /* Reset any inherited styles */
        .gallery-container * {
            box-sizing: border-box;
        }

        .gallery-container {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, 80px) !important;
            gap: 10px !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            justify-content: start !important;
        }

        .thumbnail-wrapper {
            position: relative !important;
            width: 80px !important;
            height: 80px !important;
            min-width: 80px !important;
            min-height: 80px !important;
            max-width: 80px !important;
            max-height: 80px !important;
            border: 1px solid #dee2e6 !important;
            border-radius: 6px !important;
            overflow: hidden !important;
            cursor: pointer !important;
            transition: all 0.2s ease !important;
            background: #fff !important;
            display: block !important;
            float: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .thumbnail-wrapper.add-photo {
            border: 2px dashed #0d6efd !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        .thumbnail-wrapper:hover {
            border-color: #0d6efd !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }

        .thumbnail-wrapper.active {
            border: 2px solid #0d6efd !important;
        }

        .thumbnail {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            display: block !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .add-photo-icon {
            color: #0d6efd !important;
            font-size: 20px !important;
        }

        /* Custom scrollbar styling */
        .gallery-container::-webkit-scrollbar {
            height: 4px;
        }

        .gallery-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .gallery-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-image {
                height: 300px;
            }
            
            .gallery-container {
                grid-template-columns: repeat(auto-fill, 70px) !important;
                gap: 8px !important;
            }
            
            .thumbnail-wrapper {
                width: 70px !important;
                height: 70px !important;
                min-width: 70px !important;
                min-height: 70px !important;
                max-width: 70px !important;
                max-height: 70px !important;
            }
        }

        @media (max-width: 480px) {
            .gallery-container {
                grid-template-columns: repeat(auto-fill, 60px) !important;
                gap: 6px !important;
            }
            
            .thumbnail-wrapper {
                width: 60px !important;
                height: 60px !important;
                min-width: 60px !important;
                min-height: 60px !important;
                max-width: 60px !important;
                max-height: 60px !important;
            }
        }
    </style>