<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = 10;

        $orders = Order::with(['user', 'paymentMethod', 'voucher'])
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('order_code', 'like', "%$search%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%$search%");
                        });
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->appends($request->query());

        // Gom nhóm đơn hàng theo trạng thái
        $statuses = [
            'Tất cả' => Order::query(),
            'Chưa Xác Nhận' => Order::where('order_status', 'Chưa Xác Nhận'),
            'Đã Xác Nhận' => Order::where('order_status', 'Đã Xác Nhận'),
            'Đang Chuẩn Bị Hàng' => Order::where('order_status', 'Đang Chuẩn Bị Hàng'),
            'Đang Giao' => Order::where('order_status', 'Đang Giao'),
            'Đã Giao' => Order::where('order_status', 'Đã Giao'),
            'Đã Nhận' => Order::where('order_status', 'Đã Nhận'),
            'Thành Công' => Order::where('order_status', 'Thành Công'),
            'Hoàn Hàng' => Order::where('order_status', 'Hoàn Hàng'),
            'Hủy Đơn' => Order::where('order_status', 'Hủy Đơn'),
        ];

        $groupedOrders = [];

        foreach ($statuses as $status => $query) {
            $groupedOrders[$status] = $query->with(['user', 'paymentMethod', 'voucher'])
                ->where(function ($query) use ($search) {
                    if ($search) {
                        $query->where('order_code', 'like', "%$search%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%$search%");
                            });
                    }
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
                // ->appends($request->query());
        }

        return view('admins.orders.index', compact('orders', 'search', 'groupedOrders'));
    }



    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with([
            'orderItems.product',
            'orderItems.product.productVariant.variant',
            'orderItems.product.productVariant.variantValue'
        ])->findOrFail($id);

        return view('admins.orders.detail', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function updateStatus(Request $request, $id) {
        $order = Order::findOrFail($id);

        if ($request->input('current_status') !== $order->order_status) {
            return back()->with('error', 'Trạng thái đơn hàng đã được cập nhật bởi người khác. Vui lòng tải lại trang.');
        }

        $validTransitions = [
            'Chưa Xác Nhận' => ['Đã Xác Nhận', 'Hủy Đơn'],
            'Đã Xác Nhận' => ['Đang Chuẩn Bị Hàng', 'Hủy Đơn'],
            'Đang Chuẩn Bị Hàng' => ['Đang Giao', 'Hủy Đơn'],
            'Đang Giao' => ['Đã Giao'],
            'Đã Giao' => ['Đã Nhận', 'Hoàn Hàng'],
            'Đã Nhận' => ['Thành Công', 'Hoàn Hàng'],
            'Thành Công' => ['Hoàn Hàng'],
        ];

        if (!in_array($request->order_status, $validTransitions[$order->order_status] ?? [])) {
            return back()->with('error', 'Không thể chuyển sang trạng thái này.');
        }

        $order->update(['order_status' => $request->order_status]);

        return back()->with('success', 'Cập nhật trạng thái thành công.');
    }

}
