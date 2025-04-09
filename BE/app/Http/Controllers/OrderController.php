<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Variant;
use App\Models\VariantValue;
use Illuminate\Http\Request;
use App\Models\RefundRequest;
use App\Models\ProductVariant;
use App\Mail\RefundRequestMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

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

        $ordersQuery = Order::with(['user', 'paymentMethod', 'voucher', 'refundRequest', 'orderItems.product']) // Thêm 'orderItems.product'
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('order_code', 'like', "%$search%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%$search%");
                        })
                        // Thêm tìm kiếm theo tên sản phẩm trong orderItems
                        ->orWhereHas('orderItems', function ($itemQuery) use ($search) {
                            $itemQuery->where('product_name', 'like', "%$search%");
                        });
                }
            })
            ->orderBy('created_at', 'desc');

        // Áp dụng paginate cho query chính
        $orders = $ordersQuery->paginate($perPage)->appends($request->query());

        // Gom nhóm đơn hàng theo trạng thái
        $statuses = [
            'Tất cả' => Order::query(), // Bắt đầu với query cơ bản
            'Chưa Xác Nhận' => Order::where('order_status', 'Chưa Xác Nhận'),
            'Đã Xác Nhận' => Order::where('order_status', 'Đã Xác Nhận'),
            'Đang Chuẩn Bị Hàng' => Order::where('order_status', 'Đang Chuẩn Bị Hàng'),
            'Đang Giao' => Order::where('order_status', 'Đang Giao'),
            'Đã Giao' => Order::where('order_status', 'Đã Giao'),
            'Đã Nhận' => Order::where('order_status', 'Đã Nhận'),
            'Hoàn Hàng' => Order::where('order_status', 'Hoàn Hàng'),
            'Hủy Đơn' => Order::where('order_status', 'Hủy Đơn'),
        ];

        $groupedOrders = [];

        foreach ($statuses as $status => $query) {
            // Sao chép query gốc để không ảnh hưởng lẫn nhau
            $statusQuery = $query->with(['user', 'paymentMethod', 'voucher', 'refundRequest', 'orderItems.product']) // Thêm 'orderItems.product'
                ->where(function ($query) use ($search) {
                    if ($search) {
                        $query->where('order_code', 'like', "%$search%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%$search%");
                            })
                            // Thêm tìm kiếm theo tên sản phẩm trong orderItems
                            ->orWhereHas('orderItems', function ($itemQuery) use ($search) {
                                $itemQuery->where('product_name', 'like', "%$search%");
                            });
                    }
                })
                ->orderBy('created_at', 'desc');

            // Áp dụng điều kiện trạng thái nếu không phải là "Tất cả"
            if ($status !== 'Tất cả') {
                $statusQuery->where('order_status', $status);
            }

            $groupedOrders[$status] = $statusQuery->paginate($perPage);
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
            'orderItems.product' => function ($query) {
                $query->withTrashed(); // Load product even if soft-deleted
            },
            'orderItems.productVariant' => function ($query) {
                $query->withTrashed(); // Load product variant even if soft-deleted
            }
        ])->findOrFail($id);

        foreach ($order->orderItems as $item) {
            $variantInfo = [];

            // We already loaded the variant with withTrashed above
            // Use the loaded relation instead of querying again
            $productVariant = $item->productVariant;

            if ($productVariant && !empty($productVariant->variant_details)) {
                foreach ($productVariant->variant_details as $detail) {
                    $variantInfo[] = "{$detail['name']}: {$detail['value']}";
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

            // ❌ Không cho chuyển đến trạng thái Đã Nhận hoặc Hoàn Hàng
            if (in_array($request->order_status, ['Đã Nhận', 'Hoàn Hàng'])) {
                return back()->with('error', 'Bạn không có quyền chuyển đơn sang trạng thái này.');
            }

            // ❌ Không cho chuyển trạng thái nếu hiện tại là Đã Nhận
            if ($order->order_status === 'Đã Nhận') {
                return back()->with('error', 'Đơn đã nhận không thể thay đổi trạng thái.');
            }

            // ❌ Nếu đã thanh toán (payment_status = 2) thì chỉ được hủy đơn
            if ($order->payment_status == 2 && $request->order_status !== 'Hủy Đơn') {
                return back()->with('error', 'Đơn hàng đang chờ thanh toán. Chỉ có thể hủy đơn.');
            }

            // Danh sách trạng thái cho phép chuyển đổi
            $validTransitions = [
                'Chưa Xác Nhận' => ['Đã Xác Nhận', 'Hủy Đơn'],
                'Đã Xác Nhận' => ['Đang Chuẩn Bị Hàng', 'Hủy Đơn'],
                'Đang Chuẩn Bị Hàng' => ['Đang Giao'],
                'Đang Giao' => ['Đã Giao']
            ];

            if (!in_array($request->order_status, $validTransitions[$order->order_status] ?? [])) {
                return back()->with('error', 'Không thể chuyển sang trạng thái này.');
            }

            // Nếu là Hủy Đơn → hoàn số lượng
            if (in_array($request->order_status, ['Hủy Đơn'])) {
                foreach ($order->orderItems as $item) {
                    if ($item->product_variant_id) {
                        ProductVariant::where('id', $item->product_variant_id)->increment('quantity', $item->quantity);
                    } else {
                        Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
                    }
                }
            }

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
            $refundRequest = RefundRequest::where('id', $refundRequestId)
                ->where('order_id', $orderId)
                ->first();

            if (!$refundRequest) {
                return back()->with('error', 'Yêu cầu hoàn hàng không tồn tại.');
            }

            if ($refundRequest->status !== 'Chờ Duyệt') {
                return back()->with('error', 'Yêu cầu hoàn hàng không thể duyệt.');
            }

            $refundRequest->update(['status' => 'Đã Duyệt']);

            $order = $refundRequest->order;
            if ($order) {
                $order->update(['order_status' => 'Hoàn Hàng']);
            }

            // Hoàn lại số lượng sản phẩm
            foreach ($order->orderItems as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::where('id', $item->product_variant_id)->increment('quantity', $item->quantity);
                } else {
                    Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
                }
            }

            DB::commit();

            // Gửi mail
            Mail::to($order->user->email)->send(new RefundRequestMail($refundRequest, $order, 'đã được duyệt.'));

            return back()->with('success', 'Yêu cầu hoàn hàng đã được duyệt.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi duyệt yêu cầu hoàn hàng: ' . $e->getMessage());
        }
    }

    public function rejectRefundRequest($orderId, $refundRequestId)
    {
        $order = Order::findOrFail($orderId);
        $refundRequest = RefundRequest::findOrFail($refundRequestId);

        $refundRequest->update([
            'status' => 'Từ Chối',
        ]);

        // Gửi mail thông báo từ chối
        Mail::to($order->user->email)->send(new RefundRequestMail($refundRequest, $order, 'đã bị từ chối.'));

        return redirect()->route('orders.index')->with('success', 'Yêu cầu hoàn hàng đã bị từ chối.');
    }

    /**
     * Cập nhật trạng thái cho nhiều đơn hàng cùng lúc
     */
    public function bulkUpdateStatus(Request $request)
    {
        $orderIds = $request->input('order_ids', []);
        $newStatus = $request->input('order_status');
        $currentStatuses = $request->input('current_statuses', []);

        if (empty($orderIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có đơn hàng nào được chọn.'
            ]);
        }

        $successCount = 0;
        $errorCount = 0;
        $results = [];

        // Xử lý từng đơn hàng
        foreach ($orderIds as $orderId) {
            try {
                $order = Order::with('orderItems')->findOrFail($orderId);
                $currentStatus = $currentStatuses[$orderId] ?? null;

                // Kiểm tra nếu trạng thái hiện tại khác với trạng thái đã lưu (có thể đã bị thay đổi)
                if ($currentStatus !== $order->order_status) {
                    $results[$orderId] = [
                        'success' => false,
                        'order' => [
                            'id' => $order->id,
                            'order_code' => $order->order_code,
                            'user_name' => $order->user_name,
                            'order_status' => $order->order_status,
                        ],
                        'message' => 'Trạng thái đơn hàng đã bị thay đổi bởi người khác.'
                    ];
                    $errorCount++;
                    continue;
                }

                // Kiểm tra các chuyển đổi trạng thái hợp lệ
                $validTransitions = [
                    'Chưa Xác Nhận' => ['Đã Xác Nhận', 'Hủy Đơn'],
                    'Đã Xác Nhận' => ['Đang Chuẩn Bị Hàng', 'Hủy Đơn'],
                    'Đang Chuẩn Bị Hàng' => ['Đang Giao'],
                    'Đang Giao' => ['Đã Giao'],
                    'Đã Giao' => ['Đã Nhận', 'Hoàn Hàng'],
                    'Đã Nhận' => ['Hoàn Hàng']
                ];

                if (!in_array($newStatus, $validTransitions[$currentStatus] ?? [])) {
                    $results[$orderId] = [
                        'success' => false,
                        'order' => [
                            'id' => $order->id,
                            'order_code' => $order->order_code,
                            'user_name' => $order->user_name,
                            'order_status' => $order->order_status,
                        ],
                        'message' => "Không thể chuyển từ '$currentStatus' sang '$newStatus'."
                    ];
                    $errorCount++;
                    continue;
                }

                DB::beginTransaction();

                // Nếu trạng thái chuyển sang "Hủy Đơn" hoặc "Hoàn Hàng", hoàn lại số lượng sản phẩm
                if (in_array($newStatus, ['Hủy Đơn', 'Hoàn Hàng'])) {
                    foreach ($order->orderItems as $item) {
                        if ($item->product_variant_id) {
                            ProductVariant::where('id', $item->product_variant_id)->increment('quantity', $item->quantity);
                        } else {
                            Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
                        }
                    }
                }

                // Cập nhật trạng thái đơn hàng
                $order->update(['order_status' => $newStatus]);

                DB::commit();

                $results[$orderId] = [
                    'success' => true,
                    'order' => [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'user_name' => $order->user_name,
                        'current_status' => $currentStatus,
                        'new_status' => $newStatus,
                        'order_status' => $newStatus,
                    ],
                    'message' => "Đã chuyển từ '$currentStatus' sang '$newStatus'."
                ];
                $successCount++;
            } catch (\Exception $e) {
                DB::rollBack();
                $results[$orderId] = [
                    'success' => false,
                    'order' => isset($order) ? [
                        'id' => $order->id,
                        'order_code' => $order->order_code,
                        'user_name' => $order->user_name,
                        'order_status' => $order->order_status,
                    ] : null,
                    'message' => 'Lỗi: ' . $e->getMessage()
                ];
                $errorCount++;
            }
        }

        // Trả về kết quả dưới dạng JSON để xử lý bằng JS
        return response()->json([
            'success' => true,
            'results' => $results,
            'successCount' => $successCount,
            'errorCount' => $errorCount,
            'totalCount' => count($orderIds)
        ]);
    }
}
