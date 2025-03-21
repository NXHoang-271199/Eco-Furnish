<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Comment;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Tổng doanh thu
        $totalEarnings = Order::where('payment_status', 'paid')
            ->sum(DB::raw('(SELECT SUM(order_items.price * order_items.quantity) FROM order_items WHERE order_items.order_id = orders.id)'));
        
        // Tổng số đơn hàng
        $totalOrders = Order::count();
        
        // Tổng số khách hàng
        $totalCustomers = User::where('role_id', '!=', 1)->count();
        
        // Sản phẩm bán chạy nhất
        $bestSellingProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.id', 'products.name', 'products.price', 'products.image_thumnail', 
                    DB::raw('SUM(order_items.quantity) as total_sold'),
                    DB::raw('SUM(order_items.price * order_items.quantity) as total_amount'))
            ->groupBy('products.id', 'products.name', 'products.price', 'products.image_thumnail')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();
        
        // Đơn hàng gần đây
        $recentOrders = Order::with(['user', 'paymentMethod'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        
        // Thống kê doanh thu theo quốc gia (giả lập dữ liệu)
        $salesByLocations = [
            ['country' => 'Canada', 'percentage' => 75],
            ['country' => 'Greenland', 'percentage' => 47],
            ['country' => 'Russia', 'percentage' => 82]
        ];
        
        // Top 10 danh mục phổ biến nhất
        $topCategories = Category::withCount('products')
            ->orderByDesc('products_count')
            ->limit(10)
            ->get();
            
        // Đánh giá sản phẩm gần đây
        $productReviews = Comment::with(['product', 'user'])
            ->where('status', 'Hiển thị')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
            
        // Top người mua hàng nhiều nhất
        $topBuyers = DB::table('users')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select(
                'users.id', 
                'users.name', 
                'users.email', 
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'), 
                DB::raw('SUM(order_items.price * order_items.quantity) as total_spent')
            )
            ->where('orders.payment_status', 'paid')
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('orders_count')
            ->limit(5)
            ->get();
            
        // Thống kê cho người mua hàng nhiều nhất
        $topBuyerStats = (object)[
            'max_orders' => $topBuyers->isEmpty() ? 0 : $topBuyers->max('orders_count'),
            'total' => User::where('role_id', '!=', 1)->count()
        ];
        
        return view('admins.dashboard', [
            'totalEarnings' => $totalEarnings,
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'bestSellingProducts' => $bestSellingProducts,
            'recentOrders' => $recentOrders,
            'salesByLocations' => $salesByLocations,
            'topCategories' => $topCategories,
            'productReviews' => $productReviews,
            'topBuyers' => $topBuyers,
            'topBuyerStats' => $topBuyerStats
        ]);
    }
} 
