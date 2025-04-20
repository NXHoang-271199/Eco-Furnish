<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-wallets');
        $this->middleware('permission:view-wallets-transactions', ['only' => ['allTransactions']]);
        $this->middleware('permission:edit-wallets', ['only' => ['updateBalance', 'approveWithdraw', 'rejectWithdraw']]);
    }

    // Danh sách ví người dùng
    public function index(Request $request)
    {
        $search = $request->search;
        $withdrawFilter = $request->withdraw_filter;

        $wallets = Wallet::with('user')
            ->leftJoin('withdraw_requests', function ($join) {
                $join->on('wallets.user_id', '=', 'withdraw_requests.user_id')
                    ->where('withdraw_requests.status', 'dang_xu_ly');
            })
            ->when($search, function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%");
                });
            })
            // Lọc theo yêu cầu rút tiền nếu có
            ->when($withdrawFilter, function ($query) use ($withdrawFilter) {
                if ($withdrawFilter == '1') {
                    // Chỉ lấy ví có yêu cầu rút tiền đang chờ xử lý
                    $query->whereHas('user.withdrawRequests', function ($q) {
                        $q->where('status', 'dang_xu_ly');
                    });
                } elseif ($withdrawFilter == '2') {
                    // Lấy ví không có yêu cầu rút tiền đang chờ xử lý
                    $query->whereDoesntHave('user.withdrawRequests', function ($q) {
                        $q->where('status', 'dang_xu_ly');
                    });
                }
            })
            ->groupBy('wallets.id', 'wallets.user_id', 'wallets.balance', 'wallets.created_at', 'wallets.updated_at')
            ->select('wallets.*', DB::raw('COUNT(withdraw_requests.id) as withdraw_count'))
            ->orderBy('withdraw_count', 'desc')
            ->orderBy('balance', 'desc')
            ->paginate(10);

        $withdrawRequests = WithdrawRequest::where('status', 'dang_xu_ly')
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        return view('admins.wallets.index', compact('wallets', 'withdrawRequests'));
    }
    // Chi tiết ví + giao dịch
    public function show($id, Request $request)
    {
        $wallet = Wallet::with('user')->findOrFail($id);

        // Tạo query mặc định cho các giao dịch của ví
        $query = WalletTransaction::where('wallet_id', $id)
            ->orderByDesc('created_at')
            ->with('createdBy');

        // Tìm kiếm theo tên, email hoặc số điện thoại của người dùng
        if ($request->has('search') && $request->search) {
            $query->whereHas('wallet.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Lọc theo loại giao dịch
        if ($request->has('transaction_type') && $request->transaction_type) {
            $query->where('type', $request->transaction_type);
        }

        // Lọc theo trạng thái giao dịch
        if ($request->has('transaction_status') && $request->transaction_status) {
            $query->where('status', $request->transaction_status);
        }

        // Lọc theo khoảng thời gian
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Pagination - mặc định sẽ lấy 10 giao dịch
        $transactions = $query->paginate(10);

        return view('admins.wallets.detail', compact('wallet', 'transactions'));
    }
    // Xử lý cộng tiền
    public function updateBalance(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $wallet = Wallet::findOrFail($id);

        DB::beginTransaction();

        try {
            $balanceBefore = $wallet->balance;
            $wallet->balance += $request->amount;
            $wallet->save();

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'type' => 'nap_tien',
                'status' => 'thanh_cong',
                'description' => $request->description ?? 'Giao dịch cộng tiền từ admin',
                'created_by' => auth()->user()->id,
                'balance_before' => $balanceBefore,
                'balance_after' => $wallet->balance,
            ]);

            // Gửi thông báo realtime đến client
            try {
                // Lấy thông tin về user
                $user = $wallet->user;

                // Dữ liệu thông báo
                $notificationData = [
                    'id' => $transaction->id,
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'amount' => $request->amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'transaction_type' => 'nap_tien',
                    'message' => "Tài khoản của bạn vừa được cộng " . number_format($request->amount, 0, ',', '.') . " đ",
                    'description' => $request->description ?? 'Giao dịch cộng tiền từ admin',
                    'created_at' => now()->toIso8601String(),
                    'is_read' => false
                ];

                // Gửi thông báo đến Socket Server
                \Illuminate\Support\Facades\Http::post(env('SOCKET_SERVER_URL', 'http://localhost:3002') . '/broadcast-client', [
                    'event' => 'wallet_deposit_notification',
                    'userId' => $user->id,
                    'data' => $notificationData
                ]);

                \Log::info('Đã gửi thông báo cộng tiền đến user ' . $user->id . ' cho số tiền ' . $request->amount);
            } catch (\Exception $e) {
                \Log::error('Không thể gửi thông báo realtime khi cộng tiền: ' . $e->getMessage());
                // Không throw exception để vẫn thực hiện giao dịch ngay cả khi không gửi được thông báo
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cộng tiền thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi cộng tiền',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    public function allTransactions(Request $request)
    {
        // Tạo query mặc định cho tất cả giao dịch
        $query = WalletTransaction::with('wallet.user', 'createdBy')->latest();

        // Tìm kiếm theo tên, email hoặc số điện thoại
        if ($request->has('search') && $request->search) {
            $query->whereHas('wallet.user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // Lọc theo loại giao dịch
        if ($request->has('transaction_type') && $request->transaction_type) {
            $query->where('type', $request->transaction_type);
        }

        // Lọc theo trạng thái giao dịch
        if ($request->has('transaction_status') && $request->transaction_status) {
            $query->where('status', $request->transaction_status);
        }

        // Lọc theo khoảng thời gian
        if ($request->has('start_date') && $request->start_date) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Pagination - mặc định sẽ lấy 20 giao dịch
        $transactions = $query->paginate(15);

        return view('admins.wallets.transactions', compact('transactions'));
    }

    public function getWithdrawDetail($id)
    {
        try {
            // Lấy yêu cầu rút tiền cùng với thông tin giao dịch và người dùng liên quan
            $withdraw = WithdrawRequest::with('walletTransaction', 'user', 'bankAccount')->findOrFail($id);

            // Trả về HTML view chi tiết yêu cầu rút tiền
            return response()->json([
                'html' => view('admins.wallets.withdraw_detail', compact('withdraw'))->render()
            ]);
        } catch (\Exception $e) {
            // Nếu không tìm thấy yêu cầu rút tiền, quay lại và hiển thị thông báo lỗi
            return response()->json([
                'error' => 'Không tìm thấy yêu cầu rút tiền.'
            ]);
        }
    }
    // duyệt rút
    public function approveWithdraw($id)
    {
        DB::beginTransaction();

        try {
            $withdraw = WithdrawRequest::with('walletTransaction', 'user')->findOrFail($id);

            if ($withdraw->status !== 'dang_xu_ly') {
                return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
            }

            $transaction = $withdraw->walletTransaction;

            if (!$transaction || $transaction->status !== 'cho_thanh_toan') {
                return back()->with('error', 'Không thể duyệt vì trạng thái giao dịch không hợp lệ.');
            }

            // Cập nhật trạng thái
            $withdraw->status = 'da_duyet';
            $withdraw->save();

            $transaction->status = 'thanh_cong';
            $transaction->updated_by = auth()->id();
            $transaction->description = "Đã duyệt yêu cầu rút tiền";
            $transaction->save();

            DB::commit();

            return back()->with('success', 'Duyệt yêu cầu rút tiền thành công.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Lỗi: ' . $e->getMessage());
        }
    }
    // từ chối rút
    public function rejectWithdraw($id, Request $request)
    {
        DB::beginTransaction();

        try {
            $withdraw = WithdrawRequest::with('walletTransaction', 'user')->findOrFail($id);

            if ($withdraw->status !== 'dang_xu_ly') {
                return response()->json(['error' => 'Yêu cầu này đã được xử lý trước đó.'], 400);
            }

            $transaction = $withdraw->walletTransaction;

            if (!$transaction || $transaction->status !== 'cho_thanh_toan') {
                return response()->json(['error' => 'Không thể từ chối vì trạng thái giao dịch không hợp lệ.'], 400);
            }

            $wallet = $transaction->wallet;

            $request->validate([
                'description' => 'required|string|max:255',
            ]);

            $wallet->balance += $transaction->amount;
            $wallet->save();

            $withdraw->status = 'tu_choi';
            $withdraw->save();

             // Cập nhật lại giao dịch ví
             $balanceBefore = $wallet->balance;
             $balanceAfter = $wallet->balance;

             $transaction->status = 'that_bai';
             $transaction->updated_by = auth()->id();
             $transaction->description = $request->input('description');
             $transaction->balance_before = $balanceBefore;
             $transaction->balance_after = $balanceAfter;
             $transaction->save();

            DB::commit();

            return response()->json(['success' => 'Từ chối yêu cầu và hoàn tiền thành công.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}
