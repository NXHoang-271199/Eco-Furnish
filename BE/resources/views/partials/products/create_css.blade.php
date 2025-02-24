
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Thêm container wrapper để thu nhỏ form */
        .form-container {
            transform: scale(0.75);
            transform-origin: top left;
            width: 133.33%; /* Bù lại kích thước để tránh lệch layout */
            padding-bottom: 2rem;
        }
 
        .variant-section {
            display: none;
            transition: all 0.3s ease;
        }
        .variant-section.show {
            display: block;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            border-radius: 12px;
            margin-bottom: 24px;
        }
        .card-header {
            background: linear-gradient(45deg, #3b7ddd, #2f69b8);
            border-radius: 12px 12px 0 0 !important;
            padding: 20px;
        }
        .card-header h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
        }
        .card-body {
            padding: 24px;
        }
        .form-label {
            font-weight: 500;
            margin-bottom: 8px;
            color: #344767;
        }
        .form-control, .form-select {
            border-radius: 8px;
            padding: 10px 15px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3b7ddd;
            box-shadow: 0 0 0 0.2rem rgba(59, 125, 221, 0.1);
        }
        .switch-container {
            background: rgba(255,255,255,0.1);
            padding: 6px 16px;
            border-radius: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .form-switch {
            padding-left: 3.5em;
            margin-bottom: 0;
        }
        .form-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            background-color: rgba(255,255,255,0.3);
            border-color: rgba(255,255,255,0.3);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba(255,255,255,1)'/%3e%3c/svg%3e");
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .form-switch .form-check-input:checked {
            background-color: #00d084;
            border-color: #00d084;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
            box-shadow: 0 0 8px rgba(0, 208, 132, 0.5);
        }
        .form-switch .form-check-input:focus {
            border-color: rgba(255,255,255,0.5);
            box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.2);
        }
        .form-switch .form-check-input:checked:focus {
            border-color: #00d084;
            box-shadow: 0 0 0 0.2rem rgba(0, 208, 132, 0.3);
        }
        .form-check-label {
            font-weight: 500;
            cursor: pointer;
            font-size: 0.95rem;
            user-select: none;
        }
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .btn-primary {
            background: linear-gradient(45deg, #3b7ddd, #2f69b8);
            border: none;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #2f69b8, #3b7ddd);
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #f8f9fa;
            color: #344767;
            border: 1px solid #e9ecef;
        }
        .btn-secondary:hover {
            background: #e9ecef;
            border-color: #dde1e5;
        }
        .gallery-preview {
            margin-top: 10px;
        }
        .gallery-preview-title {
            font-size: 14px;
            margin-bottom: 10px;
            color: #495057;
        }
        .gallery-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .gallery-item {
            width: 80px;
            height: 80px;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
            border: 1px solid #dee2e6;
        }
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .gallery-item.add-photo {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px dashed #0d6efd;
            cursor: pointer;
            position: relative;
        }
        .gallery-item.add-photo::before,
        .gallery-item.add-photo::after {
            content: '';
            position: absolute;
            background: #0d6efd;
        }
        .gallery-item.add-photo::before {
            width: 2px;
            height: 20px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .gallery-item.add-photo::after {
            width: 20px;
            height: 2px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }
        .gallery-item .remove-photo {
            position: absolute;
            top: 4px;
            right: 4px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .gallery-item .remove-photo i {
            color: #dc3545;
            font-size: 12px;
            line-height: 1;
        }
        .gallery-item:hover .remove-photo {
            opacity: 1;
        }
        #thumbnailPreview {
            position: relative;
            display: inline-block;
            background: #f8f9fa;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            max-width: fit-content;
        }
      
        #thumbnailPreview img {
            max-width: 150px;
            max-height: 150px;
            object-fit: contain;
            border-radius: 4px;
            display: block;
            margin: 0;
        }
        #thumbnailPreview .remove-photo {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #fff;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 1;
            z-index: 2;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #dee2e6;
        }
        #thumbnailPreview .remove-photo i {
            color: #dc3545;
            font-size: 12px;
            line-height: 1;
        }
        .main-image-preview {
            position: relative;
            display: inline-block;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
    </style>