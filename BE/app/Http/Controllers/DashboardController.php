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
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Lấy thời gian hiện tại và thời gian tháng trước để so sánh
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();
        
        // Tổng doanh thu hiện tại - Sử dụng join thay vì subquery để cải thiện hiệu suất
        $totalEarnings = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 1)
            ->sum(DB::raw('order_items.price * order_items.quantity'));
        
        // Doanh thu tháng trước
        $lastMonthEarnings = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 1)
            ->whereBetween('orders.created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum(DB::raw('order_items.price * order_items.quantity'));
        
        // Tính phần trăm tăng/giảm doanh thu
        $earningsPercentage = $lastMonthEarnings > 0 
            ? round((($totalEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100, 2) 
            : 100; // Nếu tháng trước không có doanh thu, coi như tăng 100%
        
        // Tổng số đơn hàng hiện tại
        $totalOrders = Order::count();
        
        // Số đơn hàng tháng trước
        $lastMonthOrders = Order::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        
        // Tính phần trăm tăng/giảm đơn hàng
        $ordersPercentage = $lastMonthOrders > 0 
            ? round((($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 2) 
            : 100; // Nếu tháng trước không có đơn hàng, coi như tăng 100%
        
        // Tổng số khách hàng hiện tại
        $totalCustomers = User::where('role_id', '!=', 1)->count();
        
        // Số khách hàng đăng ký tháng trước
        $lastMonthCustomers = User::where('role_id', '!=', 1)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();
        
        // Tính phần trăm tăng/giảm khách hàng
        $customersPercentage = $lastMonthCustomers > 0 
            ? round((($totalCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 2) 
            : 100; // Nếu tháng trước không có khách hàng mới, coi như tăng 100%
        
        // Xử lý lọc theo ngày cho sản phẩm bán chạy
        $sortType = request()->get('sort', 'today'); // Mặc định là hôm nay
        $dateRange = $this->getDateRangeBySort($sortType);
        
        // Sản phẩm bán chạy nhất - Tổng số sản phẩm bán chạy
        $bestSellingProductsQuery = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select(
                'products.id', 
                'products.name', 
                'products.price', 
                'products.image_thumnail', 
                'products.quantity as stock',
                'products.created_at', 
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_amount')
            )
            ->where('orders.payment_status', 1);
            
        // Thêm điều kiện ngày nếu có
        if ($dateRange) {
            $bestSellingProductsQuery->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']]);
        }
        
        // Hoàn thành câu truy vấn với group by và order by
        $bestSellingProductsQuery->groupBy('products.id', 'products.name', 'products.price', 'products.image_thumnail', 'products.quantity', 'products.created_at')
            ->orderByDesc('total_sold');
        
        // Đếm tổng số bản ghi để phân trang
        $totalBestSellingProducts = count(DB::select(
            "SELECT products.id FROM order_items 
            JOIN products ON order_items.product_id = products.id 
            JOIN orders ON order_items.order_id = orders.id 
            WHERE orders.payment_status = 'paid' " .
            ($dateRange ? "AND orders.created_at BETWEEN '".$dateRange['start']."' AND '".$dateRange['end']."' " : "") .
            "GROUP BY products.id"
        ));
        
        // Lấy tham số phân trang từ request hoặc sử dụng giá trị mặc định
        $currentPage = request()->get('product_page', 1);
        $perPage = 5;
        $bestSellingProducts = $bestSellingProductsQuery->skip(($currentPage - 1) * $perPage)->take($perPage)->get();
        $bestSellingProductsPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $bestSellingProducts,
            $totalBestSellingProducts,
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
        
        // Top người mua hàng nhiều nhất - Query builder
        $topBuyersQuery = DB::table('users')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select(
                'users.id', 
                'users.name', 
                'users.email', 
                'users.avatar',
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'), 
                DB::raw('SUM(order_items.price * order_items.quantity) as total_spent')
            )
            ->where('orders.payment_status', 1)
            ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar')
            ->orderByDesc('orders_count');
            
        // Đếm tổng số người mua để phân trang
        $totalTopBuyers = count(DB::select(
            "SELECT users.id FROM users 
            JOIN orders ON users.id = orders.user_id 
            WHERE orders.payment_status = 1 
            GROUP BY users.id"
        ));
            
        // Phân trang cho người mua hàng nhiều nhất
        $buyerCurrentPage = request()->get('buyer_page', 1);
        $buyerPerPage = 5;
        $topBuyers = $topBuyersQuery->skip(($buyerCurrentPage - 1) * $buyerPerPage)->take($buyerPerPage)->get();
        $topBuyersPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $topBuyers,
            $totalTopBuyers,
            $buyerPerPage,
            $buyerCurrentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
            
        // Thống kê cho người mua hàng nhiều nhất
        $topBuyerStats = (object)[
            'max_orders' => $topBuyers->isEmpty() ? 0 : $topBuyers->max('orders_count'),
            'total' => $totalTopBuyers
        ];
        
        // Đơn hàng gần đây - với dữ liệu đánh giá
        $recentOrders = Order::with([
                'user', 
                'paymentMethod', 
                'orderItems.product',
                'reviews' => function($query) {
                    $query->where('is_hidden', false)
                        ->orderByDesc('rating');
                }
            ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function($order) {
                // Tính trung bình đánh giá
                $avgRating = $order->reviews->avg('rating') ?: 0;
                $ratingsCount = $order->reviews->count();
                
                // Làm tròn xếp hạng đến 1 chữ số thập phân
                $order->avg_rating = number_format($avgRating, 1);
                $order->ratings_count = $ratingsCount;
                
                return $order;
            });
        
        // Thống kê doanh thu theo tỉnh/thành từ bảng user_addresses thay vì users.province
        $salesByLocations = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->join('user_addresses', 'users.id', '=', 'user_addresses.user_id')
            ->select(
                'user_addresses.province as region', 
                DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue')
            )
            ->where('orders.payment_status', 'paid')
            ->whereNotNull('user_addresses.province')
            ->where('user_addresses.is_default', 1) // Chỉ sử dụng địa chỉ mặc định
            ->groupBy('user_addresses.province')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get()
            ->map(function ($item) use ($totalEarnings) {
                // Tính phần trăm doanh thu so với tổng doanh thu
                $percentage = $totalEarnings > 0 ? round(($item->total_revenue / $totalEarnings) * 100) : 0;
                return [
                    'region' => $item->region,
                    'percentage' => $percentage,
                    'revenue' => $item->total_revenue,
                    'order_count' => $item->order_count
                ];
            });
        
        // Nếu không có dữ liệu khu vực, tạo dữ liệu trống
        if ($salesByLocations->isEmpty()) {
            $salesByLocations = collect([]);
        }
        
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
        
        // Lấy dữ liệu doanh thu theo tháng cho biểu đồ
        $monthlyData = $this->getMonthlyRevenueData();
        
        // Định dạng tổng doanh thu để hiển thị
        $formattedTotalEarnings = number_format($totalEarnings, 0, ',', '.');
        
        // Chuẩn bị dữ liệu biểu đồ
        $chartData = $this->prepareChartData();
        
        return view('admins.dashboard', [
            'totalEarnings' => $totalEarnings,
            'formattedTotalEarnings' => $formattedTotalEarnings,
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'bestSellingProducts' => $bestSellingProductsPaginator,
            'recentOrders' => $recentOrders,
            'salesByLocations' => $salesByLocations,
            'topCategories' => $topCategories,
            'productReviews' => $productReviews,
            'topBuyers' => $topBuyersPaginator,
            'topBuyerStats' => $topBuyerStats,
            'monthlyData' => $monthlyData, // Dữ liệu mới cho biểu đồ
            'chartData' => $chartData, // Dữ liệu đã định dạng cho biểu đồ kết hợp
            'earningsPercentage' => $earningsPercentage,
            'ordersPercentage' => $ordersPercentage,
            'customersPercentage' => $customersPercentage,
            'currentSort' => $sortType // Thêm biến currentSort để hiển thị trạng thái đang chọn
        ]);
    }
    
    /**
     * Lấy khoảng thời gian dựa vào loại sắp xếp
     * @param string $sortType
     * @return array|null
     */
    private function getDateRangeBySort($sortType)
    {
        $now = Carbon::now();
        
        switch ($sortType) {
            case 'today':
                return [
                    'start' => $now->copy()->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'yesterday':
                return [
                    'start' => $now->copy()->subDay()->startOfDay(),
                    'end' => $now->copy()->subDay()->endOfDay(),
                ];
            case 'week':
                return [
                    'start' => $now->copy()->subDays(7)->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'month':
                return [
                    'start' => $now->copy()->subDays(30)->startOfDay(),
                    'end' => $now->copy()->endOfDay(),
                ];
            case 'current_month':
                return [
                    'start' => $now->copy()->startOfMonth(),
                    'end' => $now->copy()->endOfMonth(),
                ];
            case 'last_month':
                return [
                    'start' => $now->copy()->subMonth()->startOfMonth(),
                    'end' => $now->copy()->subMonth()->endOfMonth(),
                ];
            case 'custom':
                // Khoảng ngày tùy chỉnh, được xử lý riêng trong phương thức filter
                return null;
            default:
                return null;
        }
    }
    
    /**
     * Lấy dữ liệu doanh thu, đơn hàng và hoàn tiền theo tháng
     * Cải thiện bằng cách sử dụng join thay vì subquery và đảm bảo dữ liệu thực
     */
    private function getMonthlyRevenueData()
    {
        $currentYear = Carbon::now()->year;
        $monthlyData = [];
        
        // Lấy dữ liệu từ tháng 1 đến tháng 12 của năm hiện tại
        for ($month = 1; $month <= 12; $month++) {
            $startOfMonth = Carbon::createFromDate($currentYear, $month, 1)->startOfMonth();
            $endOfMonth = Carbon::createFromDate($currentYear, $month, 1)->endOfMonth();
            
            // Tổng doanh thu trong tháng
            $revenue = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.payment_status', 1)
                ->whereBetween('orders.created_at', [$startOfMonth, $endOfMonth])
                ->sum(DB::raw('order_items.price * order_items.quantity'));
            
            // Tổng số đơn hàng trong tháng
            $orderCount = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            
            // Số đơn hoàn tiền trong tháng
            $refundCount = Order::where('order_status', 'Hoàn Hàng')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
            
            $monthlyData[] = [
                'month' => $month . '/' . $currentYear,
                'revenue' => $revenue,
                'orders' => $orderCount,
                'refunds' => $refundCount
            ];
        }
        
        return $monthlyData;
    }
    
    /**
     * Chuẩn bị dữ liệu biểu đồ cho trang Dashboard
     * Dữ liệu được định dạng theo yêu cầu của thư viện ApexCharts
     */
    private function prepareChartData()
    {
        // Mảng chứa tên các tháng
        $months = [];
        $revenueData = [];
        $ordersData = [];
        $refundsData = [];
        $refundAmountData = [];
        $visitsData = [];
        $conversionRateData = [];
        
        $currentYear = Carbon::now()->year;
        
        // Lấy dữ liệu từ tháng 1 đến tháng 12 của năm hiện tại
        for ($month = 1; $month <= 12; $month++) {
            $startOfMonth = Carbon::createFromDate($currentYear, $month, 1)->startOfMonth();
            $endOfMonth = Carbon::createFromDate($currentYear, $month, 1)->endOfMonth();
            
            // Thêm tên tháng vào mảng
            $months[] = 'Th' . $month;
            
            // Doanh thu trong tháng
            $revenue = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.payment_status', 1)
                ->whereBetween('orders.created_at', [$startOfMonth, $endOfMonth])
                ->sum(DB::raw('order_items.price * order_items.quantity'));
            $revenueData[] = $revenue;
            
            // Số đơn hàng trong tháng
            $orderCount = Order::whereBetween('created_at', [$startOfMonth, $endOfMonth])->count();
            $ordersData[] = $orderCount;
            
            // Số đơn hoàn tiền trong tháng
            $refundCount = Order::where('order_status', 'Hoàn Hàng')
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->count();
            $refundsData[] = $refundCount;
            
            // Số tiền hoàn trả trong tháng
            $refundAmount = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.order_status', 'Hoàn Hàng')
                ->whereBetween('orders.created_at', [$startOfMonth, $endOfMonth])
                ->sum(DB::raw('order_items.price * order_items.quantity'));
            $refundAmountData[] = $refundAmount;
            
            // Giả lập số lượt truy cập (có thể thay thế bằng dữ liệu thực sau này)
            $visits = $orderCount * rand(10, 20);
            $visitsData[] = $visits;
            
            // Tỷ lệ chuyển đổi (đơn hàng / lượt truy cập)
            $conversionRate = $visits > 0 ? ($orderCount / $visits) * 100 : 0;
            $conversionRateData[] = round($conversionRate, 2);
        }
        
        // Tính tỷ lệ chuyển đổi trung bình
        $totalOrders = array_sum($ordersData);
        $totalVisits = array_sum($visitsData);
        $averageConversionRate = $totalVisits > 0 ? ($totalOrders / $totalVisits) * 100 : 0;
        
        // Chuẩn bị dữ liệu cho biểu đồ kết hợp (cột + đường)
        $chartData = [
            'months' => $months,
            'series' => [
                [
                    'name' => 'Đơn hàng',
                    'type' => 'line',
                    'data' => $ordersData,
                    'color' => '#3b76e1'
                ],
                [
                    'name' => 'Doanh thu',
                    'type' => 'column',
                    'data' => $revenueData,
                    'color' => '#63ad6f'
                ],
                [
                    'name' => 'Hoàn tiền',
                    'type' => 'line',
                    'data' => $refundsData,
                    'color' => '#f34e4e',
                    'dashArray' => 4
                ]
            ],
            'rawData' => [
                'revenue' => $revenueData,
                'orders' => $ordersData,
                'refunds' => $refundsData,
                'refundAmount' => $refundAmountData,
                'visits' => $visitsData,
                'conversionRate' => $conversionRateData,
                'averageConversionRate' => $averageConversionRate
            ]
        ];
        
        return $chartData;
    }

    /**
     * Lọc dữ liệu dashboard theo khoảng ngày
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\View\View
     */
    public function filter(Request $request)
    {
        // Lấy khoảng ngày từ request
        $dateRange = $request->input('date_range');
        
        if (empty($dateRange)) {
            // Nếu không có giá trị date_range, chuyển hướng về dashboard mặc định
            return redirect()->route('dashboard');
        }
        
        // Phân tích chuỗi ngày từ format Y-m-d đến Y-m-d
        $dates = explode(" đến ", $dateRange);
        
        // Xác định ngày bắt đầu và kết thúc
        $startDate = isset($dates[0]) ? Carbon::parse($dates[0])->startOfDay() : null;
        $endDate = isset($dates[1]) ? Carbon::parse($dates[1])->endOfDay() : 
                 (isset($dates[0]) ? Carbon::parse($dates[0])->endOfDay() : null);
        
        if (!$startDate || !$endDate) {
            // Nếu không thể phân tích ngày, chuyển hướng về dashboard mặc định
            return redirect()->route('dashboard')->with('error', 'Định dạng ngày không hợp lệ');
        }
        
        // Lấy thời gian hiện tại và thời gian tháng trước để so sánh (vẫn giữ để hiển thị % tăng/giảm)
        $now = Carbon::now();
        $currentMonthStart = $now->copy()->startOfMonth();
        $lastMonthStart = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();
        
        // Tổng doanh thu trong khoảng ngày đã chọn
        $totalEarnings = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 1)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum(DB::raw('order_items.price * order_items.quantity'));
        
        // Doanh thu tháng trước (vẫn giữ để hiển thị % tăng/giảm)
        $lastMonthEarnings = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 1)
            ->whereBetween('orders.created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum(DB::raw('order_items.price * order_items.quantity'));
        
        // Tính phần trăm tăng/giảm doanh thu
        $earningsPercentage = $lastMonthEarnings > 0 
            ? round((($totalEarnings - $lastMonthEarnings) / $lastMonthEarnings) * 100, 2) 
            : 100; // Nếu tháng trước không có doanh thu, coi như tăng 100%
        
        // Tổng số đơn hàng trong khoảng ngày đã chọn
        $totalOrders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
        
        // Số đơn hàng tháng trước (vẫn giữ để hiển thị % tăng/giảm)
        $lastMonthOrders = Order::whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();
        
        // Tính phần trăm tăng/giảm đơn hàng
        $ordersPercentage = $lastMonthOrders > 0 
            ? round((($totalOrders - $lastMonthOrders) / $lastMonthOrders) * 100, 2) 
            : 100; // Nếu tháng trước không có đơn hàng, coi như tăng 100%
        
        // Tổng số khách hàng đăng ký trong khoảng ngày đã chọn
        $totalCustomers = User::where('role_id', '!=', 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        // Số khách hàng đăng ký tháng trước (vẫn giữ để hiển thị % tăng/giảm)
        $lastMonthCustomers = User::where('role_id', '!=', 1)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])
            ->count();
        
        // Tính phần trăm tăng/giảm khách hàng
        $customersPercentage = $lastMonthCustomers > 0 
            ? round((($totalCustomers - $lastMonthCustomers) / $lastMonthCustomers) * 100, 2) 
            : 100; // Nếu tháng trước không có khách hàng mới, coi như tăng 100%
        
        // Sử dụng 'custom' làm sortType cho khoảng ngày tùy chỉnh
        $sortType = 'custom';
        
        // Sửa lỗi "Undefined array key custom"
        $sortLabels = [
            'today' => 'Hôm nay',
            'yesterday' => 'Hôm qua',
            'week' => '7 ngày qua',
            'month' => '30 ngày qua',
            'current_month' => 'Tháng này',
            'last_month' => 'Tháng trước',
            'custom' => 'Tùy chỉnh' // Thêm label cho 'custom'
        ];
        
        // Sản phẩm bán chạy nhất trong khoảng ngày đã chọn
        $bestSellingProductsQuery = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select(
                'products.id', 
                'products.name', 
                'products.price', 
                'products.image_thumnail', 
                'products.quantity as stock',
                'products.created_at', 
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_amount')
            )
            ->where('orders.payment_status', 1)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name', 'products.price', 'products.image_thumnail', 'products.quantity', 'products.created_at')
            ->orderByDesc('total_sold');
        
        // Đếm tổng số bản ghi để phân trang
        $totalBestSellingProducts = count(DB::select(
            "SELECT products.id FROM order_items 
            JOIN products ON order_items.product_id = products.id 
            JOIN orders ON order_items.order_id = orders.id 
            WHERE orders.payment_status = 1 
            AND orders.created_at BETWEEN ? AND ?
            GROUP BY products.id",
            [$startDate, $endDate]
        ));
        
        // Lấy tham số phân trang từ request hoặc sử dụng giá trị mặc định
        $currentPage = $request->get('product_page', 1);
        $perPage = 5;
        $bestSellingProducts = $bestSellingProductsQuery->skip(($currentPage - 1) * $perPage)->take($perPage)->get();
        $bestSellingProductsPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $bestSellingProducts,
            $totalBestSellingProducts,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Top người mua hàng nhiều nhất trong khoảng ngày đã chọn
        $topBuyersQuery = DB::table('users')
            ->join('orders', 'users.id', '=', 'orders.user_id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select(
                'users.id', 
                'users.name', 
                'users.email', 
                'users.avatar',
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'), 
                DB::raw('SUM(order_items.price * order_items.quantity) as total_spent')
            )
            ->where('orders.payment_status', 1)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar')
            ->orderByDesc('orders_count');
            
        // Đếm tổng số người mua để phân trang
        $totalTopBuyers = count(DB::select(
            "SELECT users.id FROM users 
            JOIN orders ON users.id = orders.user_id 
            WHERE orders.payment_status = 1 
            AND orders.created_at BETWEEN ? AND ?
            GROUP BY users.id",
            [$startDate, $endDate]
        ));
            
        // Phân trang cho người mua hàng nhiều nhất
        $buyerCurrentPage = $request->get('buyer_page', 1);
        $buyerPerPage = 5;
        $topBuyers = $topBuyersQuery->skip(($buyerCurrentPage - 1) * $buyerPerPage)->take($buyerPerPage)->get();
        $topBuyersPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $topBuyers,
            $totalTopBuyers,
            $buyerPerPage,
            $buyerCurrentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
            
        // Thống kê cho người mua hàng nhiều nhất
        $topBuyerStats = (object)[
            'max_orders' => $topBuyers->isEmpty() ? 0 : $topBuyers->max('orders_count'),
            'total' => $totalTopBuyers
        ];
        
        // Đơn hàng gần đây trong khoảng ngày đã chọn
        $recentOrders = Order::with([
                'user', 
                'paymentMethod', 
                'orderItems.product',
                'reviews' => function($query) {
                    $query->where('is_hidden', false)
                        ->orderByDesc('rating');
                }
            ])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function($order) {
                // Tính trung bình đánh giá
                $avgRating = $order->reviews->avg('rating') ?: 0;
                $ratingsCount = $order->reviews->count();
                
                // Làm tròn xếp hạng đến 1 chữ số thập phân
                $order->avg_rating = number_format($avgRating, 1);
                $order->ratings_count = $ratingsCount;
                
                return $order;
            });
        
        // Thống kê các đơn hàng theo khu vực (dựa vào địa chỉ của khách)
        $salesByLocations = DB::table('orders')
            ->select(
                DB::raw('
                    CASE 
                        WHEN user_address LIKE "%Hà Nội%" OR user_address LIKE "%Ha Noi%" THEN "Miền Bắc"
                        WHEN user_address LIKE "%Hồ Chí Minh%" OR user_address LIKE "%Ho Chi Minh%" THEN "Miền Nam"
                        WHEN user_address LIKE "%Đà Nẵng%" OR user_address LIKE "%Da Nang%" THEN "Miền Trung"
                        ELSE "Khu vực khác"
                    END as region
                '),
                DB::raw('COUNT(id) as order_count'),
                DB::raw('SUM(total_price) as total_revenue')
            )
            ->where('payment_status', 1)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('region')
            ->get();
        
        // Nếu không có dữ liệu khu vực, tạo dữ liệu trống
        if ($salesByLocations->isEmpty()) {
            $salesByLocations = collect([]);
        }
        
        // Top 10 danh mục phổ biến nhất trong khoảng ngày đã chọn (dựa vào sản phẩm bán được)
        $topCategories = Category::withCount(['products' => function($query) use ($startDate, $endDate) {
                $query->whereHas('orderItems', function($q) use ($startDate, $endDate) {
                    $q->whereHas('order', function($o) use ($startDate, $endDate) {
                        $o->where('payment_status', 1)
                          ->whereBetween('created_at', [$startDate, $endDate]);
                    });
                });
            }])
            ->orderByDesc('products_count')
            ->limit(10)
            ->get();
            
        // Đánh giá sản phẩm gần đây
        $productReviews = Comment::with(['product', 'user'])
            ->where('status', 'Hiển thị')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        
        // Lấy dữ liệu doanh thu theo tháng cho biểu đồ (giữ nguyên vì vẫn hiển thị toàn bộ năm)
        $monthlyData = $this->getMonthlyRevenueData();
        
        // Định dạng tổng doanh thu để hiển thị
        $formattedTotalEarnings = number_format($totalEarnings, 0, ',', '.');
        
        // Chuẩn bị dữ liệu biểu đồ
        $chartData = $this->prepareChartData();
        
        // Format chuỗi hiển thị khoảng ngày
        $formattedStartDate = Carbon::parse($startDate)->format('d/m/Y');
        $formattedEndDate = Carbon::parse($endDate)->format('d/m/Y');
        $formattedDateRange = $formattedStartDate . ' - ' . $formattedEndDate;
        
        return view('admins.dashboard', [
            'totalEarnings' => $totalEarnings,
            'formattedTotalEarnings' => $formattedTotalEarnings,
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'bestSellingProducts' => $bestSellingProductsPaginator,
            'recentOrders' => $recentOrders,
            'salesByLocations' => $salesByLocations,
            'topCategories' => $topCategories,
            'productReviews' => $productReviews,
            'topBuyers' => $topBuyersPaginator,
            'topBuyerStats' => $topBuyerStats,
            'monthlyData' => $monthlyData, 
            'chartData' => $chartData,
            'earningsPercentage' => $earningsPercentage,
            'ordersPercentage' => $ordersPercentage,
            'customersPercentage' => $customersPercentage,
            'currentSort' => $sortType,
            'dateRange' => $dateRange,
            'formattedDateRange' => $formattedDateRange,
            'sortLabels' => $sortLabels,
            'isFiltered' => true
        ]);
    }
} 
