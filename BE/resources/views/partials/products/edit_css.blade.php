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
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: none; /* Ẩn mặc định */
        }

        .variant-section.show {
            display: block;
        }

        .variant-section .card-header {
            background: #3b5998;
            color: white;
            border-radius: 8px 8px 0 0;
            padding: 15px 20px;
        }

        .variant-type-select {
            max-width: 300px;
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
        }

        .variant-value-form select {
            border-color: #e0e0e0;
            transition: all 0.3s ease;
        }

        .variant-value-form select:focus {
            border-color: #3b5998;
            box-shadow: 0 0 0 0.2rem rgba(59, 89, 152, 0.25);
        }

        .variant-info-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #e0e0e0;
        }

        #variant-values-container:empty {
            display: none !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            height: 0 !important;
        }

        #variant-values-container {
            margin-bottom: 20px;
        }

        #addVariantTypeBtn {
            background-color: #3b5998;
            border-color: #3b5998;
            transition: all 0.3s ease;
            height: 38px;
        }

        #addVariantTypeBtn:hover {
            background-color: #2d4373;
            border-color: #2d4373;
        }

        #add-variant {
            background-color: #3b5998;
            border-color: #3b5998;
            transition: all 0.3s ease;
        }

        #add-variant:hover {
            background-color: #2d4373;
            border-color: #2d4373;
        }

        .form-select {
            min-width: 200px;
        }

        .input-group-text {
            font-size: 13px;
            padding: 0.375rem 0.75rem;
            background-color: #f8f9fa;
            border-color: #e0e0e0;
            color: #6c757d;
            height: 38px;
            display: flex;
            align-items: center;
        }

        .remove-variant-type {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.7;
            z-index: 1;
        }

        .remove-variant-type:hover {
            opacity: 1;
        }

        .variant-item {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .variant-item:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .variant-header {
            padding: 15px;
            cursor: pointer;
        }

        .variant-form {
            border-top: 1px solid #e0e0e0;
            padding: 15px;
            background: white;
            border-radius: 0 0 8px 8px;
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

        /* Button container styles */
        .form-actions {
            margin-top: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .form-actions .btn {
            margin-left: 10px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
        }

        .form-actions .btn i {
            font-size: 14px;
        }

        .form-actions .btn-secondary {
            background-color: #74788d;
            border-color: #74788d;
            color: #fff;
        }

        .form-actions .btn-secondary:hover {
            background-color: #636678;
            border-color: #636678;
        }

        /* Responsive adjustments */
        @media (max-width: 576px) {
            .form-actions {
                text-align: center;
            }
            
            .form-actions .btn {
                margin: 5px;
                width: 100%;
                justify-content: center;
            }
        }

        .input-group .invalid-feedback {
            display: none;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        .input-group .is-invalid ~ .invalid-feedback {
            display: block;
        }

        .input-group.has-validation {
            flex-wrap: wrap;
        }

        .input-group.has-validation > .invalid-feedback {
            width: 100%;
            margin-left: 0;
        }

        /* Form validation styles */
        .form-control.is-invalid,
        .form-select.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .form-control.is-valid,
        .form-select.is-valid {
            border-color: #198754;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%23198754' d='M2.3 6.73L.6 4.53c-.4-1.04.46-1.4 1.1-.8l1.1 1.4 3.4-3.8c.6-.63 1.6-.27 1.2.7l-4 4.6c-.43.5-.8.4-1.1.1z'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }

        .valid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #198754;
        }

        .was-validated .form-control:valid,
        .form-control.is-valid,
        .was-validated .form-select:valid,
        .form-select.is-valid {
            border-color: #198754;
        }

        .was-validated .form-control:valid:focus,
        .form-control.is-valid:focus,
        .was-validated .form-select:valid:focus,
        .form-select.is-valid:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }

        .was-validated .form-control:valid ~ .valid-feedback,
        .form-control.is-valid ~ .valid-feedback,
        .was-validated .form-select:valid ~ .valid-feedback,
        .form-select.is-valid ~ .valid-feedback {
            display: block;
        }

        .input-group .form-control.is-invalid,
        .input-group .form-select.is-invalid {
            z-index: 3;
        }

        .input-group.has-validation {
            flex-wrap: wrap;
        }

        .input-group.has-validation > :nth-last-child(n + 3):not(.last-child):not(.dropdown-toggle):not(.dropdown-menu),
        .input-group.has-validation > .dropdown-toggle:nth-last-child(n + 4) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .input-group.has-validation > .invalid-feedback {
            margin-top: 0.25rem;
            flex-basis: 100%;
        }

        /* Alert styles */
        .alert {
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }

        .alert-danger {
            color: #842029;
            background-color: #f8d7da;
            border-color: #f5c2c7;
        }

        .combination-error {
            width: 100%;
            text-align: left;
            font-size: 14px;
            margin-bottom: 15px;
        }

        /* Switch toggle styles */
        .switch-container {
            display: flex;
            align-items: center;
        }

        .form-check-input {
            width: 2.5em;
            height: 1.25em;
            margin-top: 0;
            vertical-align: middle;
            background-color: #fff;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%280, 0, 0, 0.25%29'/%3e%3c/svg%3e");
            background-position: left center;
            background-repeat: no-repeat;
            background-size: contain;
            border: 1px solid rgba(0, 0, 0, 0.25);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            -webkit-print-color-adjust: exact;
            color-adjust: exact;
            transition: background-position 0.15s ease-in-out;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
            background-position: right center;
        }

        .form-switch .form-check-input {
            border-radius: 2em;
            margin-right: 0.5em;
        }

        .form-check-label {
            margin-bottom: 0;
            cursor: pointer;
        }
    </style>