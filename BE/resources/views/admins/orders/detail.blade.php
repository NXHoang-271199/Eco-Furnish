@extends('layouts.admin')

@section('title')
    Quản lý đơn hàng
@endsection

@section('CSS')
<style>
    .order-detail-card {
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .order-detail-card:hover {
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        transform: translateY(-2px);
    }
    
    .order-header {
        background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
        color: white;
        padding: 20px;
        border-radius: 12px 12px 0 0;
    }
    
    .order-status {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .order-id {
        font-weight: 700;
        font-size: 1.2rem;
    }
    
    .customer-info-section {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .info-row {
        transition: all 0.2s;
        border-radius: 8px;
        margin-bottom: 8px;
    }
    
    .info-row:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }
    
    .info-label {
        font-weight: 600;
        color: #495057;
    }
    
    .info-value {
        color: #212529;
    }
    
    .divider {
        height: 3px;
        background: linear-gradient(90deg, #ff9a9e 0%, #fad0c4 99%, #fad0c4 100%);
        margin: 25px 0;
        border-radius: 3px;
    }
    
    .store-header {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .product-item {
        border-radius: 8px;
        transition: all 0.2s;
        padding: 15px;
        margin-bottom: 10px;
        border: 1px solid #eaeaea;
    }
    
    .product-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }
    
    .product-image {
        border-radius: 8px;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.08);
        transition: all 0.3s;
    }
    
    .product-image:hover {
        transform: scale(1.05);
    }
    
    .product-name {
        font-weight: 600;
        color: #343a40;
    }
    
    .variant-info {
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .quantity-badge {
        background-color: #e9ecef;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.9rem;
    }
    
    .price-info {
        font-weight: 700;
        color: #dc3545;
    }
    
    .summary-section {
        background-color: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
    }
    
    .summary-row {
        padding: 10px 0;
        display: flex;
        justify-content: space-between;
        border-bottom: 1px solid #eaeaea;
    }
    
    .summary-row:last-child {
        border-bottom: none;
    }
    
    .summary-label {
        font-weight: 600;
        color: #495057;
    }
    
    .total-price {
        font-size: 1.3rem;
        font-weight: 700;
        color: #dc3545;
    }
    
    .payment-alert {
        border-radius: 8px;
        margin: 20px 0;
        padding: 15px;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(220, 53, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(220, 53, 69, 0);
        }
    }
    
    .back-button {
        display: inline-flex;
        align-items: center;
        color: #fff;
        background: linear-gradient(135deg, #ff9a9e 0%, #f6416c 100%);
        padding: 8px 15px;
        border-radius: 30px;
        text-decoration: none;
        transition: all 0.3s;
        font-weight: 600;
    }
    
    .back-button:hover {
        transform: translateX(-5px);
        box-shadow: 0 5px 15px rgba(246, 65, 108, 0.4);
        color: white;
    }
    
    .back-button i {
        margin-right: 6px;
    }
</style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <a href="{{ route('orders.index') }}" class="back-button">
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
                <h5 class="mb-3"><i class="fas fa-user-circle me-2"></i>Thông tin khách hàng</h5>
                <div class="customer-info-section">
                    <div class="row info-row p-2">
                        <div class="col-md-4 info-label">Tên người nhận</div>
                        <div class="col-md-8 info-value">{{ $order->user_name }}</div>
                    </div>
                    
                    <div class="row info-row p-2">
                        <div class="col-md-4 info-label">Số điện thoại</div>
                        <div class="col-md-8 info-value">{{ $order->user_phone }}</div>
                    </div>
                    
                    <div class="row info-row p-2">
                        <div class="col-md-4 info-label">Địa chỉ</div>
                        <div class="col-md-8 info-value">{{ $order->user_address }}</div>
                    </div>
                    
                    <div class="row info-row p-2">
                        <div class="col-md-4 info-label">Email</div>
                        <div class="col-md-8 info-value">{{ $order->user_email ?? 'Không có' }}</div>
                    </div>
                  <hr>
                    <div class="row info-row p-2">
                        <div class="col-md-4 info-label">Tên người đặt</div>
                        <div class="col-md-8 info-value">{{ $order->user?->name ?? 'Khách vãng lai' }}</div>
                    </div>
                    
                    @if(!empty($order->user?->phone))
                    <div class="row info-row p-2">
                        <div class="col-md-4 info-label">Số điện thoại người đặt</div>
                        <div class="col-md-8 info-value">{{ $order->user->phone }}</div>
                    </div>
                    @endif
                    
                    <div class="row info-row p-2">
                        <div class="col-md-4 info-label">Tài khoản đặt hàng (Email)</div>
                        <div class="col-md-8 info-value">{{ $order->user?->email ?? 'Không có' }}</div>
                    </div>
                </div>
                
                <div class="divider"></div>
                
                <!-- Order Items -->
                <div class="store-header d-flex align-items-center">
                    <i class="fa-solid fa-shop me-2"></i>
                    <h5 class="mb-0">Eco - Furnish</h5>
                </div>
                
                <div class="product-list">
                    @foreach ($order->orderItems as $item)
                        <div class="product-item">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    @if (!empty($item->image_url))
                                        <img src="{{ Storage::url($item->image_url) }}" alt="Product" class="img-fluid rounded product-image" style="max-width: 100px;">
                                    @else
                                        <div class="text-center bg-light p-3 rounded">
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
                <div class="summary-section">
                    <div class="summary-row">
                        <span class="summary-label">Tổng tiền hàng</span>
                        <span>{{ number_format($order->orderItems->sum('total_price'), 0, ',', '.') }} đ</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Giảm giá</span>
                        <span>{{ number_format($order->discount_amount, 0, ',', '.') }} đ</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Thành tiền</span>
                        <span class="total-price">{{ number_format($order->total_price, 0, ',', '.') }} đ</span>
                    </div>
                    
                    <div class="summary-row">
                        <span class="summary-label">Phương thức thanh toán</span>
                        <span><i class="fas fa-credit-card me-1"></i> {{ $order->paymentMethod->name }}</span>
                    </div>
                </div>
                
                <!-- Payment Status -->
                @if ($order->payment_status == 0)
                    <div class="alert alert-danger text-center payment-alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Đơn hàng chưa được thanh toán. Tổng số tiền cần thanh toán là
                            {{ number_format($order->total_price, 0, ',', '.') }} đ.</strong>
                    </div>
                @elseif ($order->payment_status == 1)
                    <div class="alert alert-success text-center payment-alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Đơn đã được thanh toán. Số tiền cần thanh toán là 0 đồng.</strong>
                    </div>
                @elseif ($order->payment_status == 2)
                    <div class="alert alert-warning text-center payment-alert">
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
        const orderCard = document.querySelector('.order-detail-card');
        
        // Thêm class để kích hoạt animation
        setTimeout(function() {
            orderCard.style.opacity = '1';
        }, 100);
        
        // Hiệu ứng hover cho các dòng sản phẩm
        const productItems = document.querySelectorAll('.product-item');
        productItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s ease';
            });
        });
    });
</script>
@endsection
