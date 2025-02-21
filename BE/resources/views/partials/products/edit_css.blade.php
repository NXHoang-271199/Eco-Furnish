
 
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
        }
        #thumbnailPreview .remove-photo {
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
        #thumbnailPreview .remove-photo i {
            color: #dc3545;
            font-size: 12px;
            line-height: 1;
        }
        #thumbnailPreview:hover .remove-photo {
            opacity: 1;
        }
    </style>