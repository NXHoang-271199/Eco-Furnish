@extends('layouts.admin')

@section('title')
    Quản lý đơn hàng
@endsection

@section('CSS')
    <style>
        .order-detail-card {
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .order-detail-card:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
            transform: translateY(-5px);
        }

        .order-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 24px;
            border-radius: 16px 16px 0 0;
            position: relative;
            overflow: hidden;
        }

        .order-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.05' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.3;
        }

        .order-status {
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .order-status:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .order-id {
            font-weight: 700;
            font-size: 1.3rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
        }

        .order-id:before {
            content: '';
            display: inline-block;
            width: 10px;
            height: 10px;
            background-color: #fff;
            border-radius: 50%;
            margin-right: 10px;
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
        }

        .customer-info-section {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
        }

        .customer-info-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 6px;
            height: 100%;
            background: linear-gradient(to bottom, #6a11cb, #2575fc);
            border-radius: 3px 0 0 3px;
        }

        .info-row {
            transition: all 0.3s;
            border-radius: 10px;
            margin-bottom: 10px;
            padding: 12px !important;
        }

        .info-row:hover {
            background-color: rgba(0, 0, 0, 0.03);
            transform: translateX(5px);
        }

        .info-label {
            font-weight: 600;
            color: #495057;
            display: flex;
            align-items: center;
        }

        .info-label::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background-color: #6a11cb;
            border-radius: 50%;
            margin-right: 8px;
        }

        .info-value {
            color: #212529;
            font-weight: 500;
        }

        .divider {
            height: 4px;
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            margin: 30px 0;
            border-radius: 4px;
            position: relative;
        }

        .divider::before, .divider::after {
            content: '';
            position: absolute;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #6a11cb;
            top: -3px;
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        .store-header {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            box-shadow: 0 3px 15px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
        }

        .store-header:hover {
            background-color: #e9ecef;
            transform: translateY(-3px);
        }

        .store-header i {
            font-size: 1.2rem;
            color: #6a11cb;
            margin-right: 10px;
        }

        .store-header h5 {
            margin: 0;
            font-weight: 700;
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .product-list {
            margin-bottom: 25px;
        }

        .product-item {
            border-radius: 12px;
            transition: all 0.3s ease;
            padding: 20px;
            margin-bottom: 15px;
            border: 1px solid #eaeaea;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.03);
            position: relative;
            overflow: hidden;
        }

        .product-item::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .product-item:hover {
            background-color: #f8f9fa;
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
        }

        .product-item:hover::after {
            transform: scaleX(1);
        }

        .product-image {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            object-fit: cover;
            height: 100px;
            width: 100px;
        }

        .product-image:hover {
            transform: scale(1.08);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .product-name {
            font-weight: 700;
            color: #343a40;
            margin-bottom: 10px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .product-item:hover .product-name {
            color: #6a11cb;
        }

        .variant-info {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 12px;
        }

        .variant-info .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            background-color: #e9ecef;
            color: #495057;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .variant-info .badge:hover {
            background-color: #dee2e6;
            transform: translateY(-2px);
        }

        .quantity-badge {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .quantity-badge:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .quantity-badge i {
            margin-right: 5px;
        }

        .price-info {
            font-weight: 700;
            font-size: 1.1rem;
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            transition: all 0.3s ease;
        }

        .product-item:hover .price-info {
            transform: scale(1.05);
        }

        .summary-section {
            background-color: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .summary-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            opacity: 0.05;
            border-radius: 0 0 0 100%;
        }

        .summary-row {
            padding: 15px 0;
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #eaeaea;
            transition: all 0.3s ease;
        }

        .summary-row:hover {
            background-color: rgba(0, 0, 0, 0.02);
            transform: translateX(5px);
            border-radius: 8px;
            padding-left: 10px;
            padding-right: 10px;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-label {
            font-weight: 600;
            color: #495057;
            display: flex;
            align-items: center;
        }

        .summary-label::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border-radius: 50%;
            margin-right: 8px;
        }

        .total-price {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .summary-row:hover .total-price {
            transform: scale(1.05);
        }

        .payment-alert {
            border-radius: 12px;
            margin: 25px 0;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .payment-alert::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23000000' fill-opacity='0.03' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.5;
            z-index: 0;
        }

        .payment-alert strong {
            position: relative;
            z-index: 1;
        }

        .payment-alert:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .payment-alert.alert-danger {
            background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 99%, #fad0c4 100%);
            border: none;
            color: #721c24;
        }

        .payment-alert.alert-success {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
            border: none;
            color: #155724;
        }

        .payment-alert.alert-warning {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            border: none;
            color: #856404;
        }

        .payment-alert i {
            font-size: 1.2rem;
            margin-right: 10px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            color: #fff;
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            padding: 10px 20px;
            border-radius: 30px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(106, 17, 203, 0.3);
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .back-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #2575fc 0%, #6a11cb 100%);
            z-index: -1;
            transition: opacity 0.3s ease;
            opacity: 0;
        }

        .back-button:hover {
            transform: translateX(-5px);
            box-shadow: 0 8px 25px rgba(106, 17, 203, 0.4);
            color: white;
        }

        .back-button:hover::before {
            opacity: 1;
        }

        .back-button i {
            margin-right: 8px;
            transition: transform 0.3s ease;
        }

        .back-button:hover i {
            transform: translateX(-3px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                gap: 10px;
                align-items: flex-start;
            }
            
            .order-status {
                align-self: flex-start;
            }
            
            .product-image {
                height: 80px;
                width: 80px;
            }
            
            .info-row {
                padding: 8px !important;
            }
            
            .total-price {
                font-size: 1.3rem;
            }
        }

        /* Animation for elements */
        @keyframes slideInFromLeft {
            0% {
                transform: translateX(-50px);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideInFromRight {
            0% {
                transform: translateX(50px);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeInUp {
            0% {
                transform: translateY(20px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .animate-left {
            animation: slideInFromLeft 0.5s ease-out forwards;
        }

        .animate-right {
            animation: slideInFromRight 0.5s ease-out forwards;
        }

        .animate-up {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        /* Staggered animation delays */
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }
        .delay-4 { animation-delay: 0.4s; }
        .delay-5 { animation-delay: 0.5s; }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <a href="{{ route('orders.index') }}" class="back-button animate-left">
                    <i class="fas fa-arrow-left"></i> Trở lại
                </a>
            </div>
        </div>

        <div class="card order-detail-card">
            <!-- Order Header -->
            <div class="order-header d-flex justify-content-between align-items-center">
                <div>
                    <span class="order-id">Đơn hàng #{{ $order->order_code }}</span>
                </div>
                <div>
                    <span class="order-status bg-{{ getOrderStatusColor($order->order_status) }}">
                        {{ $order->order_status }}
                    </span>
                </div>
            </div>

            <div class="card-body">
                <!-- Customer Information -->
                <h5 class="mb-3 animate-left"><i class="fas fa-user-circle me-2"></i>Thông tin khách hàng</h5>
                <div class="row">
                    <!-- Thông tin người nhận -->
                    <div class="col-md-6">
                        <div class="customer-info-section animate-up">
                            <h6 class="mb-3 text-primary"><i class="fas fa-shipping-fast me-2"></i>Thông tin giao hàng</h6>
                            <div class="row info-row p-2 delay-1">
                                <div class="col-md-5 info-label"><i class="fas fa-user me-1"></i> Người nhận</div>
                                <div class="col-md-7 info-value">{{ $order->user_name }}</div>
                            </div>

                            <div class="row info-row p-2 delay-2">
                                <div class="col-md-5 info-label"><i class="fas fa-phone me-1"></i> Điện thoại</div>
                                <div class="col-md-7 info-value">{{ $order->user_phone }}</div>
                            </div>

                            <div class="row info-row p-2 delay-3">
                                <div class="col-md-5 info-label"><i class="fas fa-map-marker-alt me-1"></i> Địa chỉ</div>
                                <div class="col-md-7 info-value">{{ $order->user_address }}</div>
                            </div>

                            <div class="row info-row p-2 delay-4">
                                <div class="col-md-5 info-label"><i class="fas fa-envelope me-1"></i> Email</div>
                                <div class="col-md-7 info-value">{{ $order->user_email ?? 'Không có' }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Thông tin người đặt -->
                    <div class="col-md-6">
                        <div class="customer-info-section animate-up">
                            <h6 class="mb-3 text-primary"><i class="fas fa-user-check me-2"></i>Thông tin tài khoản</h6>
                            <div class="row info-row p-2 delay-1">
                                <div class="col-md-5 info-label"><i class="fas fa-user me-1"></i> Người đặt</div>
                                <div class="col-md-7 info-value">{{ $order->user?->name ?? 'Khách vãng lai' }}</div>
                            </div>

                            @if (!empty($order->user?->phone))
                                <div class="row info-row p-2 delay-2">
                                    <div class="col-md-5 info-label"><i class="fas fa-phone me-1"></i> Điện thoại</div>
                                    <div class="col-md-7 info-value">{{ $order->user->phone }}</div>
                                </div>
                            @endif

                            <div class="row info-row p-2 delay-3">
                                <div class="col-md-5 info-label"><i class="fas fa-envelope me-1"></i> Email</div>
                                <div class="col-md-7 info-value">{{ $order->user?->email ?? 'Không có' }}</div>
                            </div>
                            
                            <div class="row info-row p-2 delay-4">
                                <div class="col-md-5 info-label"><i class="fas fa-calendar-alt me-1"></i> Ngày đặt</div>
                                <div class="col-md-7 info-value">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="divider"></div>

                <!-- Order Items -->
                <div class="store-header d-flex align-items-center animate-right">
                    <i class="fa-solid fa-shop me-2"></i>
                    <h5 class="mb-0">Eco - Furnish</h5>
                </div>

                <div class="product-list">
                    @foreach ($order->orderItems as $index => $item)
                        <div class="product-item animate-up delay-{{ ($index % 5) + 1 }}">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    @if (!empty($item->image_url))
                                        <img src="{{ Storage::url($item->image_url) }}" alt="Product"
                                            class="img-fluid rounded product-image">
                                    @else
                                        <div class="text-center bg-light p-3 rounded product-image d-flex align-items-center justify-content-center">
                                            <i class="fas fa-image fa-2x text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-7">
                                    <p class="product-name mb-2">{{ $item->product_name }}</p>

                                    @if (!empty($item->productVariant))
                                        <p class="variant-info mb-2">
                                            <span class="badge bg-light text-dark">
                                                {{ implode(' - ', $item->variant_info) }}
                                            </span>
                                        </p>
                                    @else
                                        <p class="variant-info mb-2">Không có phân loại</p>
                                    @endif

                                    <span class="quantity-badge">
                                        <i class="fas fa-times me-1"></i>{{ $item->quantity }}
                                    </span>
                                </div>
                                <div class="col-md-3 text-end">
                                    <p class="price-info mb-0">
                                        {{ number_format($item->total_price, 0, ',', '.') }} đ
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="divider"></div>

                <!-- Order Summary -->
                <div class="summary-section animate-up">
                    <div class="summary-row delay-1">
                        <span class="summary-label">Tổng tiền hàng</span>
                        <span>{{ number_format($order->orderItems->sum('total_price'), 0, ',', '.') }} đ</span>
                    </div>

                    <div class="summary-row delay-2">
                        <span class="summary-label">Giảm giá</span>
                        <span>{{ number_format($order->discount_amount, 0, ',', '.') }} đ</span>
                    </div>

                    <div class="summary-row delay-3">
                        <span class="summary-label">Thành tiền</span>
                        <span class="total-price">{{ number_format($order->total_price, 0, ',', '.') }} đ</span>
                    </div>

                    <div class="summary-row delay-4">
                        <span class="summary-label">Phương thức thanh toán</span>
                        <span><i class="fas fa-credit-card me-1"></i> {{ $order->paymentMethod->name }}</span>
                    </div>
                </div>

                <!-- Payment Status -->
                @if ($order->payment_status == 1 && $order->order_status == 'Hoàn Hàng' && $order->refundRequest->where('status', 'Đã Duyệt')->count() > 0)
                    <div class="alert alert-danger text-center payment-alert animate-up delay-5">
                        <i class="fas fa-undo-alt me-2"></i>
                        <strong>Đơn hàng đã hoàn. Số tiền đã hoàn lại là
                            {{ number_format($order->total_price, 0, ',', '.') }} đ.</strong>
                    </div>
                @elseif ($order->payment_status == 1 && $order->order_status == 'Hoàn Hàng' && $order->refundRequest->where('status', 'Chờ Duyệt')->count() > 0)
                    <div class="alert alert-warning text-center payment-alert animate-up delay-5">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Đơn hàng đang chờ duyệt yêu cầu hoàn. Số tiền chờ hoàn lại là
                            {{ number_format($order->total_price, 0, ',', '.') }} đ.</strong>
                    </div>
                @elseif ($order->payment_status == 1 && $order->order_status == 'Hoàn Hàng' && $order->refundRequest->where('status', 'Từ Chối')->count() > 0)
                    <div class="alert alert-danger text-center payment-alert animate-up delay-5">
                        <i class="fas fa-ban me-2"></i>
                        <strong>Yêu cầu hoàn hàng đã bị từ chối.</strong>
                    </div>
                @elseif ($order->payment_status == 1 && $order->order_status == 'Hoàn Hàng')
                    <div class="alert alert-warning text-center payment-alert animate-up delay-5">
                        <i class="fas fa-sync me-2"></i>
                        <strong>Đơn hàng đang trong quá trình xử lý hoàn hàng. Vui lòng xem xét yêu cầu hoàn.</strong>
                    </div>
                @elseif ($order->order_status == 'Hủy Đơn')
                    <div class="alert alert-danger text-center payment-alert animate-up delay-5">
                        <i class="fas fa-ban me-2"></i>
                        <strong>Đơn hàng đã bị hủy.</strong>
                    </div>
                @elseif ($order->payment_status == 0)
                    <div class="alert alert-danger text-center payment-alert animate-up delay-5">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Đơn hàng chưa được thanh toán. Tổng số tiền cần thanh toán là
                            {{ number_format($order->total_price, 0, ',', '.') }} đ.</strong>
                    </div>
                @elseif ($order->payment_status == 1)
                    <div class="alert alert-success text-center payment-alert animate-up delay-5">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Đơn đã được thanh toán. Số tiền cần thanh toán là 0 đồng.</strong>
                    </div>
                @elseif ($order->payment_status == 2)
                    <div class="alert alert-warning text-center payment-alert animate-up delay-5">
                        <i class="fas fa-clock me-2"></i>
                        <strong>Đơn hàng đang chờ thanh toán. Tổng số tiền cần thanh toán là
                            {{ number_format($order->total_price, 0, ',', '.') }} đ.</strong>
                    </div>
                @endif

            </div>
        </div>
    </div>
@endsection

@section('JS')
    <script>
        // Thêm hiệu ứng khi tải trang
        document.addEventListener('DOMContentLoaded', function() {
            // Thêm class để kích hoạt animation cho các phần tử
            const animateElements = document.querySelectorAll('.animate-left, .animate-right, .animate-up');
            animateElements.forEach(element => {
                element.style.opacity = '0';
            });
            
            setTimeout(() => {
                animateElements.forEach(element => {
                    element.style.opacity = '1';
                });
            }, 100);

            // Hiệu ứng hover cho các dòng sản phẩm
            const productItems = document.querySelectorAll('.product-item');
            productItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });
            
            // Hiệu ứng hover cho các dòng thông tin
            const infoRows = document.querySelectorAll('.info-row');
            infoRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });
            
            // Hiệu ứng hover cho các dòng tổng kết
            const summaryRows = document.querySelectorAll('.summary-row');
            summaryRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transition = 'all 0.3s ease';
                });
            });
        });
    </script>
@endsection
