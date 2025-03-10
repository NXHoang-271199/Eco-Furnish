    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Fix logo styles */
        .logo.logo-light img {
            max-height: 36px !important;
            margin: 0 !important;
            vertical-align: middle !important;
        }

        .variant-section {
            display: none;
            transition: all 0.3s ease;
        }

        .variant-section.show {
            display: block;
        }

        .form-container {
            padding: 1rem;
            max-width: 1600px;
            margin: 0 auto;
        }

        .form-content {
            font-size: 14px;
        }

        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .card-header {
            background: linear-gradient(45deg, #3b7ddd, #2f69b8);
            border-radius: 8px 8px 0 0 !important;
            padding: 15px;
        }

        .card-header h5 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: white;
        }

        .card-body {
            padding: 20px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
            color: #344767;
        }

        .form-control, .form-select {
            font-size: 13px;
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .btn {
            font-size: 13px;
            padding: 8px 16px;
        }

        .gallery-item {
            width: 60px;
            height: 60px;
        }

        #thumbnailPreview img {
            max-width: 120px;
            max-height: 120px;
        }

        .page-title-box h4 {
            font-size: 18px;
        }

        .breadcrumb {
            font-size: 12px;
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

        /* CSS cho phần chọn biến thể */
        .variant-selector {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* CSS cho danh sách biến thể đã chọn */
        #selectedVariantTypes {
            background: #fff;
            border-radius: 8px;
        }

        .selected-variant-type {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .selected-variant-type:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .selected-variant-type .variant-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .selected-variant-type .variant-values {
            margin-top: 10px;
        }

        .variant-value-select {
            height: auto !important;
            min-height: 120px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #e0e6ed;
        }

        .variant-value-select option {
            padding: 10px 12px;
            margin-bottom: 4px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .variant-value-select option:checked {
            background: linear-gradient(45deg, #3b7ddd, #2f69b8) !important;
            color: white;
            font-weight: 500;
        }

        .variant-value-select:focus {
            border-color: #3b7ddd;
            box-shadow: 0 0 0 0.2rem rgba(59, 125, 221, 0.1);
        }

        .variant-value-select.is-invalid {
            border-color: #dc3545;
        }

        .variant-value-select.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        /* CSS cho container tạo biến thể tự động */
        #generate-variants-container {
            transition: all 0.3s ease;
            margin: 25px 0;
        }

        #generate-variants-btn {
            background: linear-gradient(45deg, #00c6ff, #0072ff);
            border: none;
            box-shadow: 0 4px 12px rgba(0, 114, 255, 0.3);
            transition: all 0.3s ease;
            padding: 12px 25px;
            font-weight: 600;
            letter-spacing: 0.5px;
            font-size: 1.05rem;
        }

        #generate-variants-btn:hover {
            background: linear-gradient(45deg, #0072ff, #00c6ff);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 114, 255, 0.4);
        }

        #generate-variants-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(0, 114, 255, 0.3);
        }

        /* CSS cho các tổ hợp biến thể được tạo ra */
        .variant-combination {
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 15px;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }

        .variant-combination:hover {
            border-color: #3b7ddd;
            box-shadow: 0 5px 15px rgba(59, 125, 221, 0.1);
            transform: translateY(-2px);
        }

        .variant-combination.active {
            border-color: #3b7ddd;
            box-shadow: 0 5px 20px rgba(59, 125, 221, 0.15);
            background-color: #f8fbff;
        }

        .variant-combination .combination-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 500;
            color: #344767;
            margin-bottom: 5px;
        }

        .variant-combination .combination-attributes {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 10px;
        }

        .variant-combination .attribute-tag {
            background: #f0f4f8;
            border-radius: 20px;
            padding: 5px 12px;
            font-size: 13px;
            color: #506690;
            font-weight: 500;
        }

        .variant-combination .attribute-tag span {
            font-weight: 600;
            color: #3b7ddd;
        }

        /* CSS cho form chỉnh sửa biến thể */
        .variant-edit-form {
            background: #f8fbff;
            border: 1px solid #d0e1fd;
            border-radius: 8px;
            padding: 16px;
            margin-top: 10px;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .variant-edit-form.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .variant-edit-form .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .variant-edit-form .form-group {
            flex: 1;
        }

        .variant-edit-form .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 10px;
        }

        .empty-variant-message {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            border: 1px dashed #d1d9e6;
            color: #6c757d;
        }

        .empty-variant-message i {
            font-size: 40px;
            color: #adb5bd;
            margin-bottom: 15px;
        }

        .empty-variant-message h5 {
            margin-bottom: 10px;
            color: #495057;
            font-weight: 500;
        }

        /* CSS cho thông báo lỗi và thành công trong input-group */
        .input-group ~ .invalid-feedback,
        .input-group ~ .valid-feedback {
            display: block;
            margin-top: 0.25rem;
        }

        .input-group .form-control.is-invalid,
        .input-group .form-control.is-valid {
            z-index: 1;
        }

        .input-group .form-control.is-invalid {
            border-color: #dc3545;
        }

        .input-group .form-control.is-valid {
            border-color: #198754;
        }

        .input-group .form-control.is-invalid:focus,
        .input-group .form-control.is-valid:focus {
            box-shadow: none;
        }

        .input-group .input-group-text {
            z-index: 0;
        }

        .input-group .form-control.is-invalid + .input-group-text {
            border-color: #dc3545;
        }

        .input-group .form-control.is-valid + .input-group-text {
            border-color: #198754;
        }

        /* CSS cho nút xóa biến thể */
        .btn-remove-variant {
            background: none;
            border: none;
            color: #dc3545;
            font-size: 16px;
            padding: 0;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 50%;
        }

        .btn-remove-variant:hover {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .btn-remove-variant i {
            font-size: 14px;
        }
    </style>
