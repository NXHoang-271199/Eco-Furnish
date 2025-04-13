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
            ->where('orders.payment_status', 'paid')
            ->sum(DB::raw('order_items.price * order_items.quantity'));
        
        // Doanh thu tháng trước
        $lastMonthEarnings = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
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
            ->where('orders.payment_status', 'paid');
            
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
            ->where('orders.payment_status', 'paid')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar')
            ->orderByDesc('orders_count');
            
        // Đếm tổng số người mua để phân trang
        $totalTopBuyers = count(DB::select(
            "SELECT users.id FROM users 
            JOIN orders ON users.id = orders.user_id 
            WHERE orders.payment_status = 'paid' 
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
        
        // Tên tháng bằng tiếng Việt
        $vietnameseMonths = [
            1 => 'Th1', 2 => 'Th2', 3 => 'Th3', 4 => 'Th4', 5 => 'Th5', 6 => 'Th6',
            7 => 'Th7', 8 => 'Th8', 9 => 'Th9', 10 => 'Th10', 11 => 'Th11', 12 => 'Th12'
        ];
        
        // Tạo mảng chứa dữ liệu các tháng
        for ($month = 1; $month <= 12; $month++) {
            $startDate = Carbon::createFromDate($currentYear, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($currentYear, $month, 1)->endOfMonth();
            
            // Tính tổng doanh thu trong tháng bằng join
            $revenue = DB::table('orders')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.payment_status', 'paid')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->sum(DB::raw('order_items.price * order_items.quantity'));
            
            // Đếm số đơn hàng trong tháng
            $orders = Order::whereBetween('created_at', [$startDate, $endDate])->count();
            
            // Tính số lượng hoàn tiền từ bảng refund_requests thay vì orders
            $refundCount = DB::table('refund_requests')
                ->where('status', 'Đã Duyệt')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count();
            
            // Tính tổng tiền hoàn lại trong tháng - Sử dụng join để lấy tổng tiền cho các đơn hoàn tiền đã duyệt
            $refundAmount = DB::table('refund_requests')
                ->join('orders', 'refund_requests.order_id', '=', 'orders.id')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->where('refund_requests.status', 'Đã Duyệt')
                ->whereBetween('refund_requests.created_at', [$startDate, $endDate])
                ->sum(DB::raw('order_items.price * order_items.quantity'));
            
            // Sử dụng tên tháng tiếng Việt thay vì tiếng Anh
            $monthlyData[] = [
                'month' => $vietnameseMonths[$month],
                'revenue' => $revenue ?: 0,
                'orders' => $orders ?: 0,
                'refunds' => $refundCount ?: 0,
                'refundAmount' => $refundAmount ?: 0
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
        // Lấy dữ liệu doanh thu theo tháng
        $monthlyData = $this->getMonthlyRevenueData();
        
        // Tạo mảng tên tháng và dữ liệu cho các series
        $months = array_column($monthlyData, 'month');
        $revenueData = array_column($monthlyData, 'revenue');
        $ordersData = array_column($monthlyData, 'orders');
        $refundsData = array_column($monthlyData, 'refunds');
        $refundAmountData = array_column($monthlyData, 'refundAmount');
        
        // Mô phỏng dữ liệu lượt truy cập (visits) - sẽ được thay thế bằng dữ liệu thực từ bảng thống kê truy cập
        // trong phiên bản sau nếu có theo dõi lượt truy cập
        $visitsData = [];
        foreach ($ordersData as $orderCount) {
            // Mô phỏng số lượt truy cập khoảng 5-10 lần số đơn hàng
            $visits = $orderCount * rand(5, 10);
            $visitsData[] = $visits > 0 ? $visits : rand(50, 100);
        }
        
        // Tính tỷ lệ chuyển đổi (đơn hàng / lượt truy cập)
        $conversionRateData = [];
        foreach ($ordersData as $key => $orderCount) {
            $visits = $visitsData[$key];
            // Tránh chia cho 0
            if ($visits > 0) {
                $conversionRateData[] = round(($orderCount / $visits) * 100, 2);
            } else {
                $conversionRateData[] = 0;
            }
        }
        
        // Tính tỷ lệ chuyển đổi trung bình
        $averageConversionRate = !empty($conversionRateData) ? array_sum($conversionRateData) / count($conversionRateData) : 0;
        $averageConversionRate = round($averageConversionRate, 2);
        
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
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->sum(DB::raw('order_items.price * order_items.quantity'));
        
        // Doanh thu tháng trước (vẫn giữ để hiển thị % tăng/giảm)
        $lastMonthEarnings = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
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
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('products.id', 'products.name', 'products.price', 'products.image_thumnail', 'products.quantity', 'products.created_at')
            ->orderByDesc('total_sold');
        
        // Đếm tổng số bản ghi để phân trang
        $totalBestSellingProducts = count(DB::select(
            "SELECT products.id FROM order_items 
            JOIN products ON order_items.product_id = products.id 
            JOIN orders ON order_items.order_id = orders.id 
            WHERE orders.payment_status = 'paid' 
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
            ->where('orders.payment_status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->groupBy('users.id', 'users.name', 'users.email', 'users.avatar')
            ->orderByDesc('orders_count');
            
        // Đếm tổng số người mua để phân trang
        $totalTopBuyers = count(DB::select(
            "SELECT users.id FROM users 
            JOIN orders ON users.id = orders.user_id 
            WHERE orders.payment_status = 'paid' 
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
            ->whereBetween('orders.created_at', [$startDate, $endDate])
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
        
        // Top 10 danh mục phổ biến nhất trong khoảng ngày đã chọn (dựa vào sản phẩm bán được)
        $topCategories = Category::withCount(['products' => function($query) use ($startDate, $endDate) {
                $query->whereHas('orderItems', function($q) use ($startDate, $endDate) {
                    $q->whereHas('order', function($o) use ($startDate, $endDate) {
                        $o->whereBetween('created_at', [$startDate, $endDate]);
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
