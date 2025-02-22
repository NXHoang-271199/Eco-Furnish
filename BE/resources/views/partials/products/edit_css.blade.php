    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Form styles */
        .card-header {
            background: #405189;
            padding: 15px 20px;
        }
        
        .card-header h5 {
            color: white;
            margin: 0;
        }

        .card-body {
            padding: 20px;
        }

        /* Form controls */
        .form-control {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 8px 12px;
            width: 100%;
            margin-bottom: 10px;
        }

        .form-control:focus {
            border-color: #86b7fe;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 5px;
            display: block;
        }

        /* Gallery styles */
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

        /* Thumbnail preview */
        #thumbnailPreview {
            position: relative;
            display: inline-block;
            margin-top: 10px;
        }

        #thumbnailPreview img {
            max-width: 200px;
            border-radius: 8px;
        }

        .remove-photo {
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

        .remove-photo i {
            color: #dc3545;
            font-size: 12px;
            line-height: 1;
        }

        .gallery-item:hover .remove-photo,
        #thumbnailPreview:hover .remove-photo {
            opacity: 1;
        }

        /* Variant section styles */
        .variant-section {
            margin-top: 20px;
        }

        .variant-combination {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .variant-combination .form-group {
            margin-bottom: 15px;
        }

        .variant-combination .btn-danger {
            padding: 5px 10px;
            font-size: 14px;
        }

        /* Button styles */
        .btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
        }

        .btn-primary {
            background-color: #405189;
            border-color: #405189;
        }

        .btn-primary:hover {
            background-color: #364574;
            border-color: #364574;
        }

        .btn-info {
            background-color: #299cdb;
            border-color: #299cdb;
            color: white;
        }

        .btn-info:hover {
            background-color: #2589c1;
            border-color: #2589c1;
            color: white;
        }

        .btn-danger {
            background-color: #f06548;
            border-color: #f06548;
        }

        .btn-danger:hover {
            background-color: #d85a40;
            border-color: #d85a40;
        }

        /* Error states */
        .is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 12px;
            margin-top: 4px;
        }
    </style>