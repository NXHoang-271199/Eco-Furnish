<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Variant;
use App\Models\Voucher;
use App\Models\VariantValue;
use App\Models\VoucherUsage;
use Illuminate\Http\Request;
use App\Models\RefundRequest;
use App\Models\ProductVariant;
use App\Mail\RefundRequestMail;
use App\Models\OrderNotification;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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

        $ordersQuery = Order::with(['user', 'paymentMethod', 'voucher', 'refundRequest', 'orderItems.product', 'updatedBy'])
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('order_code', 'like', "%$search%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%$search%");
                        })
                        ->orWhereHas('orderItems', function ($itemQuery) use ($search) {
                            $itemQuery->where('product_name', 'like', "%$search%");
                        });
                }
            })
            ->orderBy('created_at', 'desc');

        $orders = $ordersQuery->paginate($perPage)->appends($request->query());

        // Gom nhóm đơn hàng theo trạng thái + thêm "Yêu cầu hoàn hàng"
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
            'Yêu cầu hoàn hàng' => Order::whereHas('refundRequest'),
        ];

        $groupedOrders = [];

        foreach ($statuses as $status => $query) {
            $statusQuery = $query->with(['user', 'paymentMethod', 'voucher', 'refundRequest', 'orderItems.product'])
                ->where(function ($query) use ($search) {
                    if ($search) {
                        $query->where('order_code', 'like', "%$search%")
                            ->orWhereHas('user', function ($userQuery) use ($search) {
                                $userQuery->where('name', 'like', "%$search%");
                            })
                            ->orWhereHas('orderItems', function ($itemQuery) use ($search) {
                                $itemQuery->where('product_name', 'like', "%$search%");
                            });
                    }
                })
                ->orderBy('created_at', 'desc');

            if ($status !== 'Tất cả' && $status !== 'Yêu cầu hoàn hàng') {
                $statusQuery->where('order_status', $status);
            }

            $groupedOrders[$status] = $statusQuery->paginate($perPage)->appends($request->query());
        }

        return view('admins.orders.index', compact('orders', 'search', 'groupedOrders'));
    }

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

    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with(['orderItems', 'user'])->findOrFail($id);

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
            if ($order->payment_status == 2) {
                return back()->with('error', 'Đơn hàng đang chờ thanh toán. Không thể thay đổi trạng thái.');
            }

            // ❌ Nếu đã thanh toán (payment_status = 1) thì không được hủy đơn
            if ($order->payment_status == 1 && $request->order_status == 'Hủy Đơn') {
                return back()->with('error', 'Đơn hàng đã thanh toán. Không được hủy đơn');
            }

            // Danh sách trạng thái cho phép chuyển đổi
            $validTransitions = [
                'Chưa Xác Nhận' => ['Đã Xác Nhận', 'Hủy Đơn'],
                'Đã Xác Nhận' => ['Đang Chuẩn Bị Hàng'],
                'Đang Chuẩn Bị Hàng' => ['Đang Giao'],
                'Đang Giao' => ['Đã Giao']
            ];

            if (!in_array($request->order_status, $validTransitions[$order->order_status] ?? [])) {
                return back()->with('error', 'Không thể chuyển sang trạng thái này.');
            }

            // Nếu là Hủy Đơn → hoàn số lượng -> hoàn lại voucher
            if (in_array($request->order_status, ['Hủy Đơn'])) {
                foreach ($order->orderItems as $item) {
                    if ($item->product_variant_id) {
                        ProductVariant::where('id', $item->product_variant_id)->increment('quantity', $item->quantity);
                    } else {
                        Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
                    }
                }
                // Hoàn lại lượt sử dụng voucher
                if ($order->voucher_id) {
                    // Xóa lượt sử dụng voucher của người dùng
                    VoucherUsage::where('user_id', $order->user_id)
                        ->where('voucher_id', $order->voucher_id)
                        ->delete();

                    // Tăng lại số lượt sử dụng của voucher
                    Voucher::where('id', $order->voucher_id)
                        ->increment('usage_limit');
                }
            }
            // ** Cập nhật payment_status khi đơn hàng chuyển sang "Đã Giao" **
            if ($request->order_status === 'Đã Giao') {
                $order->payment_status = 1; // Đánh dấu là đã thanh toán
            }

            // Cập nhật trạng thái đơn hàng
            $order->update(['order_status' => $request->order_status]);

            // Tạo thông báo cho đơn hàng
            $notification = OrderNotification::create([
                'order_id' => $order->id,
                'is_read' => false
            ]);

            // Gửi thông báo realtime cho user
            try {
                // Lấy lại đơn hàng với thông tin mới nhất sau khi cập nhật
                $updatedOrder = Order::where('id', $order->id)->first();

                // Tạo nội dung thông báo
                $notificationData = [
                    'id' => $notification->id,
                    'order_id' => $updatedOrder->id,
                    'order_code' => $updatedOrder->order_code,
                    'user_id' => $updatedOrder->user_id,
                    'order_status' => $updatedOrder->order_status,
                    'message' => "Đơn hàng #{$updatedOrder->order_code} đã chuyển sang trạng thái: {$updatedOrder->order_status}",
                    'created_at' => now()->toIso8601String(),
                    'is_read' => false
                ];

                // Gửi thông báo đến Socket Server
                Http::post(env('SOCKET_SERVER_URL', 'http://localhost:3002') . '/broadcast-client', [
                    'event' => 'order_status_notification',
                    'userId' => $updatedOrder->user_id,
                    'data' => $notificationData
                ]);

                \Log::info('Thông báo đã được gửi đến user ' . $updatedOrder->user_id . ' cho đơn hàng #' . $updatedOrder->order_code);
            } catch (\Exception $e) {
                // Ghi log lỗi nhưng không dừng quá trình cập nhật
                \Log::error('Không thể gửi thông báo realtime: ' . $e->getMessage());
            }


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
                // Cập nhật trạng thái đơn hàng thành "Hoàn Hàng" khi duyệt yêu cầu
                $order->update(['order_status' => 'Hoàn Hàng']);
            }

            // ✅ Hoàn lại số lượng sản phẩm
            foreach ($order->orderItems as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::where('id', $item->product_variant_id)->increment('quantity', $item->quantity);
                } else {
                    Product::where('id', $item->product_id)->increment('quantity', $item->quantity);
                }
            }

            // ✅ Kiểm tra payment_status và chỉ hoàn tiền nếu payment_status == 1 (đã thanh toán)
            if ($order->payment_status == 1) {
                // ✅ Hoàn tiền nếu đơn đã thanh toán
                $user = User::with('wallet')->find($order->user_id);
                if (!$user || !$user->wallet) {
                    throw new \Exception('Không tìm thấy ví của người dùng');
                }

                $wallet = $user->wallet;

                // ✅ Tìm giao dịch hoàn tiền đang chờ xử lý
                $walletTransaction = WalletTransaction::where('order_id', $order->id)
                    ->where('type', 'hoan_tien')
                    ->where('status', 'cho_thanh_toan')
                    ->first();

                if (!$walletTransaction) {
                    throw new \Exception('Không tìm thấy giao dịch ví cần cập nhật');
                }

                $refundAmount = $walletTransaction->amount;
                $balanceBefore = $wallet->balance;
                $balanceAfter = $balanceBefore + $refundAmount;

                // ✅ Cập nhật số dư ví
                $wallet->update(['balance' => $balanceAfter]);

                // ✅ Cập nhật lại giao dịch ví
                $walletTransaction->update([
                    'status' => 'thanh_cong',
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'updated_by' => auth()->id(),
                    'description' => 'Hoàn tiền đơn hàng #' . $order->order_code . ' sau khi được duyệt yêu cầu hoàn hàng'
                ]);
            }

            // ✅ Tạo thông báo cho người dùng trong cơ sở dữ liệu
            $notification = OrderNotification::create([
                'order_id' => $order->id,
                'is_read' => false
            ]);

            // ✅ Gửi thông báo realtime cho người dùng
            try {
                // Tạo nội dung thông báo
                $notificationData = [
                    'id' => $notification->id,
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'user_id' => $order->user_id,
                    'order_status' => 'Hoàn Hàng',
                    'message' => "Yêu cầu hoàn hàng đơn hàng #{$order->order_code} đã được phê duyệt",
                    'created_at' => now()->toIso8601String(),
                    'is_read' => false,
                    'refund_status' => 'Đã Duyệt'
                ];

                // Gửi thông báo đến Socket Server
                Http::post(env('SOCKET_SERVER_URL', 'http://localhost:3002') . '/broadcast-client', [
                    'event' => 'refund_approval_notification',
                    'userId' => $order->user_id,
                    'data' => $notificationData
                ]);

                \Log::info('Thông báo phê duyệt hoàn hàng đã được gửi đến user ' . $order->user_id . ' cho đơn hàng #' . $order->order_code);
            } catch (\Exception $e) {
                // Ghi log lỗi nhưng không dừng quá trình cập nhật
                \Log::error('Không thể gửi thông báo realtime phê duyệt hoàn hàng: ' . $e->getMessage());
            }

            DB::commit();

            // ✅ Gửi mail
            Mail::to($order->user->email)->send(new RefundRequestMail($refundRequest, $order, 'đã được duyệt.'));

            return back()->with('success', 'Yêu cầu hoàn hàng đã được duyệt.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi duyệt yêu cầu hoàn hàng: ' . $e->getMessage());
        }
    }


    public function rejectRefundRequest($orderId, $refundRequestId)
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($orderId);
            $refundRequest = RefundRequest::findOrFail($refundRequestId);

            // Cập nhật trạng thái yêu cầu hoàn hàng
            $refundRequest->update(['status' => 'Từ Chối']);

            // ❌ Không hoàn tiền, chỉ cập nhật giao dịch ví nếu có
            if ($order->payment_status == 1) { // Chỉ cập nhật nếu payment_status == 1 (đã thanh toán)
                $walletTransaction = WalletTransaction::where('order_id', $order->id)
                    ->where('type', 'hoan_tien')
                    ->where('status', 'cho_thanh_toan')
                    ->first();

                if ($walletTransaction) {
                    $walletTransaction->update([
                        'status' => 'that_bai',
                        'description' => 'Hoàn tiền đơn hàng #' . $order->order_code . ' thất bại do yêu cầu hoàn hàng bị từ chối',
                        'updated_by' => auth()->id()
                    ]);
                }
            }

            // ✅ Tạo thông báo cho người dùng trong cơ sở dữ liệu
            $notification = OrderNotification::create([
                'order_id' => $order->id,
                'is_read' => false
            ]);

            // ✅ Gửi thông báo realtime cho người dùng
            try {
                // Tạo nội dung thông báo
                $notificationData = [
                    'id' => $notification->id,
                    'order_id' => $order->id,
                    'order_code' => $order->order_code,
                    'user_id' => $order->user_id,
                    'order_status' => $order->order_status,
                    'message' => "Yêu cầu hoàn hàng đơn hàng #{$order->order_code} đã bị từ chối",
                    'created_at' => now()->toIso8601String(),
                    'is_read' => false,
                    'refund_status' => 'Từ Chối'
                ];

                // Gửi thông báo đến Socket Server
                Http::post(env('SOCKET_SERVER_URL', 'http://localhost:3002') . '/broadcast-client', [
                    'event' => 'refund_rejection_notification',
                    'userId' => $order->user_id,
                    'data' => $notificationData
                ]);

                \Log::info('Thông báo từ chối hoàn hàng đã được gửi đến user ' . $order->user_id . ' cho đơn hàng #' . $order->order_code);
            } catch (\Exception $e) {
                // Ghi log lỗi nhưng không dừng quá trình cập nhật
                \Log::error('Không thể gửi thông báo realtime từ chối hoàn hàng: ' . $e->getMessage());
            }

            DB::commit();

            // Gửi mail thông báo từ chối
            Mail::to($order->user->email)->send(new RefundRequestMail($refundRequest, $order, 'đã bị từ chối.'));

            return redirect()->route('orders.index')->with('success', 'Yêu cầu hoàn hàng đã bị từ chối.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi khi từ chối yêu cầu hoàn hàng: ' . $e->getMessage());
        }
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

        $validTransitions = [
            'Chưa Xác Nhận' => ['Đã Xác Nhận', 'Hủy Đơn'],
            'Đã Xác Nhận' => ['Đang Chuẩn Bị Hàng'],
            'Đang Chuẩn Bị Hàng' => ['Đang Giao'],
            'Đang Giao' => ['Đã Giao'],
        ];

        foreach ($orderIds as $orderId) {
            try {
                $order = Order::with(['orderItems', 'user'])->findOrFail($orderId);
                $currentStatus = $currentStatuses[$orderId] ?? null;

                // Check trạng thái đơn hàng có bị thay đổi không
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

                // Không cho cập nhật nếu trạng thái mới là "Đã Nhận" hoặc "Hoàn Hàng"
                if (in_array($newStatus, ['Đã Nhận', 'Hoàn Hàng'])) {
                    $results[$orderId] = [
                        'success' => false,
                        'order' => [
                            'id' => $order->id,
                            'order_code' => $order->order_code,
                            'user_name' => $order->user_name,
                            'order_status' => $order->order_status,
                        ],
                        'message' => 'Bạn không có quyền chuyển đơn sang trạng thái này.'
                    ];
                    $errorCount++;
                    continue;
                }

                // Nếu đơn đã là "Đã Nhận" thì không thay đổi được nữa
                if ($order->order_status === 'Đã Nhận') {
                    $results[$orderId] = [
                        'success' => false,
                        'order' => [
                            'id' => $order->id,
                            'order_code' => $order->order_code,
                            'user_name' => $order->user_name,
                            'order_status' => $order->order_status,
                        ],
                        'message' => 'Đơn đã nhận không thể thay đổi trạng thái.'
                    ];
                    $errorCount++;
                    continue;
                }

                // Nếu chờ thanh toán thì chỉ được hủy đơn
                if ($order->payment_status == 2) {
                    $results[$orderId] = [
                        'success' => false,
                        'order' => [
                            'id' => $order->id,
                            'order_code' => $order->order_code,
                            'user_name' => $order->user_name,
                            'order_status' => $order->order_status,
                        ],
                        'message' => 'Đơn hàng đang chờ thanh toán. Không thể thay đổi trạng thái.'
                    ];
                    $errorCount++;
                    continue;
                }
                // Nếu đã thanh toán thì không được hủy đơn
                if ($order->payment_status == 1 && $newStatus == 'Hủy Đơn') {
                    $results[$orderId] = [
                        'success' => false,
                        'order' => [
                            'id' => $order->id,
                            'order_code' => $order->order_code,
                            'user_name' => $order->user_name,
                            'order_status' => $order->order_status,
                        ],
                        'message' => 'Đơn hàng đã thanh toán. Không thể hủy đơn.'
                    ];
                    $errorCount++;
                    continue;
                }


                // Kiểm tra trạng thái chuyển đổi có hợp lệ không
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

                // Nếu là Hủy Đơn → hoàn số lượng
                if ($newStatus === 'Hủy Đơn') {
                    foreach ($order->orderItems as $item) {
                        if ($item->product_variant_id) {
                            ProductVariant::where('id', $item->product_variant_id)
                                ->increment('quantity', $item->quantity);
                        } else {
                            Product::where('id', $item->product_id)
                                ->increment('quantity', $item->quantity);
                        }
                    }
                    // Hoàn lại lượt sử dụng voucher
                    if ($order->voucher_id) {
                        // Xóa lượt sử dụng voucher của người dùng
                        VoucherUsage::where('user_id', $order->user_id)
                            ->where('voucher_id', $order->voucher_id)
                            ->delete();

                        // Tăng lại số lượt sử dụng của voucher
                        Voucher::where('id', $order->voucher_id)
                            ->increment('usage_limit');
                    }
                }
                // Nếu trạng thái mới là "Đã Giao", cập nhật payment_status = 1
                if ($newStatus === 'Đã Giao') {
                    $order->update(['payment_status' => 1]);
                }


                // Cập nhật trạng thái đơn hàng
                $order->update(['order_status' => $newStatus]);
                // Tạo thông báo cho đơn hàng
                $notification = OrderNotification::create([
                    'order_id' => $order->id,
                    'is_read' => false
                ]);

                // Gửi thông báo realtime cho user
                try {
                    // Lấy lại đơn hàng với thông tin mới nhất sau khi cập nhật
                    $updatedOrder = Order::where('id', $order->id)->first();

                    // Tạo nội dung thông báo
                    $notificationData = [
                        'id' => $notification->id,
                        'order_id' => $updatedOrder->id,
                        'order_code' => $updatedOrder->order_code,
                        'user_id' => $updatedOrder->user_id,
                        'order_status' => $updatedOrder->order_status,
                        'message' => "Đơn hàng #{$updatedOrder->order_code} đã chuyển sang trạng thái: {$updatedOrder->order_status}",
                        'created_at' => now()->toIso8601String(),
                        'is_read' => false
                    ];

                    // Gửi thông báo đến Socket Server
                    Http::post(env('SOCKET_SERVER_URL', 'http://localhost:3002') . '/broadcast-client', [
                        'event' => 'order_status_notification',
                        'userId' => $updatedOrder->user_id,
                        'data' => $notificationData
                    ]);
                } catch (\Exception $e) {
                    // Ghi log lỗi nhưng không dừng quá trình cập nhật
                    \Log::error('Không thể gửi thông báo realtime: ' . $e->getMessage());
                }
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

        return response()->json([
            'success' => true,
            'results' => $results,
            'successCount' => $successCount,
            'errorCount' => $errorCount,
            'totalCount' => count($orderIds)
        ]);
    }
}
