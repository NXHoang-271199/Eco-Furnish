<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    // Danh sách ví người dùng
    public function index()
    {
        $wallets = Wallet::with('user')->orderBy('balance', 'desc')->paginate(10);
        return view('admins.wallets.index', compact('wallets'));
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
}
