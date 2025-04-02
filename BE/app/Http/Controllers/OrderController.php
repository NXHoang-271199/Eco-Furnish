<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Variant;
use App\Models\VariantValue;
use Illuminate\Http\Request;
use App\Models\RefundRequest;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Constructor để kiểm tra quyền
     */
    public function __construct()
    {
        $this->middleware('permission:view-orders');
        $this->middleware('permission:create-orders', ['only' => ['create', 'store']]);
        $this->middleware('permission:update-orders', ['only' => ['edit', 'update', 'updateStatus']]);
        $this->middleware('permission:delete-orders', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $perPage = 10;

        $orders = Order::with(['user', 'paymentMethod', 'voucher', 'refundRequest']) // Thêm 'refundRequest' vào eager load
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
            'Hoàn Hàng' => Order::where('order_status', 'Hoàn Hàng'),
            'Hủy Đơn' => Order::where('order_status', 'Hủy Đơn'),
            'Từ Chối Hoàn Hàng' => Order::where('order_status', 'Từ Chối Hoàn Hàng'),
        ];

        $groupedOrders = [];

        foreach ($statuses as $status => $query) {
            $groupedOrders[$status] = $query->with(['user', 'paymentMethod', 'voucher', 'refundRequest']) // Thêm 'refundRequest' vào đây
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
        $order = Order::with(['orderItems.product'])->findOrFail($id);

        foreach ($order->orderItems as $item) {
            $variantInfo = [];

            if ($item->product_variant_id) {
                $productVariant = ProductVariant::find($item->product_variant_id);

                if ($productVariant && !empty($productVariant->variant_details)) {
                    foreach ($productVariant->variant_details as $detail) {
                        $variantInfo[] = "{$detail['name']}: {$detail['value']}";
                    }
                }
            }

            // Gán vào orderItem để dùng trong view
            $item->setAttribute('variant_info', $variantInfo);
        }

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
    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with('orderItems')->findOrFail($id);

            if ($request->input('current_status') !== $order->order_status) {
                return back()->with('error', 'Trạng thái đơn hàng đã được cập nhật bởi người khác. Vui lòng tải lại trang.');
            }

            $validTransitions = [
                'Chưa Xác Nhận' => ['Đã Xác Nhận', 'Hủy Đơn'],
                'Đã Xác Nhận' => ['Đang Chuẩn Bị Hàng', 'Hủy Đơn'],
                'Đang Chuẩn Bị Hàng' => ['Đang Giao'],
                'Đang Giao' => ['Đã Giao'],
                'Đã Giao' => ['Đã Nhận', 'Hoàn Hàng'],
                'Đã Nhận' => ['Hoàn Hàng']
            ];

            if (!in_array($request->order_status, $validTransitions[$order->order_status] ?? [])) {
                return back()->with('error', 'Không thể chuyển sang trạng thái này.');
            }

            // Nếu trạng thái chuyển sang "Hủy Đơn" hoặc "Hoàn Hàng", hoàn lại số lượng sản phẩm
            if (in_array($request->order_status, ['Hủy Đơn', 'Hoàn Hàng'])) {
                foreach ($order->orderItems as $item) {
                    if ($item->product_variant_id) {
                        ProductVariant::where('id', $item->product_variant_id)->increment('quantity', $item->quantity);
                    } else {
                        Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
                    }
                }
            }

            // Cập nhật trạng thái đơn hàng
            $order->update(['order_status' => $request->order_status]);

            DB::commit();
            return back()->with('success', 'Cập nhật trạng thái thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi cập nhật trạng thái: ' . $e->getMessage());
        }
    }

    public function approveRefundRequest($orderId, $refundRequestId, Request $request)
{
    DB::beginTransaction();
    try {
        // Tìm yêu cầu hoàn hàng
        $refundRequest = RefundRequest::where('id', $refundRequestId)->where('order_id', $orderId)->first();

        if (!$refundRequest) {
            return back()->with('error', 'Yêu cầu hoàn hàng không tồn tại.');
        }

        // Kiểm tra xem yêu cầu hoàn hàng có trạng thái 'Chờ Duyệt'
        if ($refundRequest->status !== 'Chờ Duyệt') {
            return back()->with('error', 'Yêu cầu hoàn hàng không thể duyệt vì đã được xử lý hoặc không còn trong trạng thái chờ duyệt.');
        }

        // Duyệt yêu cầu hoàn hàng
        $refundRequest->update(['status' => 'Đã Duyệt']);

        // Cập nhật trạng thái đơn hàng (nếu cần)
        $order = $refundRequest->order;
        if ($order) {
            $order->update(['order_status' => 'Hoàn Hàng']);
        }

        // Nếu cần hoàn lại sản phẩm vào kho
        foreach ($order->orderItems as $item) {
            if ($item->product_variant_id) {
                ProductVariant::where('id', $item->product_variant_id)->increment('quantity', $item->quantity);
            } else {
                Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
            }
        }

        DB::commit();
        return back()->with('success', 'Yêu cầu hoàn hàng đã được duyệt và trạng thái đơn hàng đã được cập nhật.');
    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', 'Lỗi khi duyệt yêu cầu hoàn hàng: ' . $e->getMessage());
    }
}

public function rejectRefundRequest($orderId, $refundRequestId)
{
    $order = Order::findOrFail($orderId);
    $refundRequest = RefundRequest::findOrFail($refundRequestId);

    // Cập nhật trạng thái đơn hàng
    $order->update([
        'order_status' => 'Từ Chối Hoàn Hàng',  // Đặt trạng thái "Hoàn Hàng Từ Chối"
    ]);

    // Cập nhật trạng thái yêu cầu hoàn hàng
    $refundRequest->update([
        'status' => 'Từ Chối',  // Nếu có cột "status" trong bảng refund_requests
    ]);

    return redirect()->route('orders.index')->with('success', 'Yêu cầu hoàn hàng đã bị từ chối.');
}


}
