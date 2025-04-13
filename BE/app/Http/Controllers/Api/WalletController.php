<?php

namespace App\Http\Controllers\Api;

use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    // lấy số dư ví của user
    public function getBalance()
    {
        $userId = Auth::id();
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return response()->json(['message' => 'Không tìm thấy ví người dùng'], 404);
        }

        return response()->json([
            'balance' => $wallet->balance
        ]);
    }

    /**
     * Nạp tiền vào ví bằng MoMo hoặc VNPAY (chỉ tạo bản ghi và chuyển hướng đến payment link).
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10000|max:10000000',
            'payment_method_id' => 'required|exists:payment_methods,id',
        ], [
            'amount.min' => 'Số tiền nạp tối thiểu là 10.000 VNĐ',
            'amount.max' => 'Số tiền nạp tối đa là 10.000.000 VNĐ',
        ]);

        $userId = Auth::id();
        $wallet = Wallet::firstOrCreate(['user_id' => $userId]);
        $paymentMethod = PaymentMethod::find($request->payment_method_id);
        $balanceBefore = $wallet->balance;
        $balanceAfter = $balanceBefore + $request->amount;

        if (!$paymentMethod || !$paymentMethod->is_connected) {
            return response()->json(['message' => 'Phương thức thanh toán chưa được kết nối'], 400);
        }
        if (!in_array($paymentMethod->name, ['MoMo', 'VNPAY'])) {
            return response()->json([
                'message' => 'Chỉ hỗ trợ nạp tiền bằng MoMo hoặc VNPAY'
            ], 400);
        }


        DB::beginTransaction();
        try {
            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'wallet_code' => 'NAP' . rand(100000, 999999),
                'amount' => $request->amount,
                'type' => 'nap_tien',
                'payment_method_id' => $paymentMethod->id,
                'description' => 'Nạp tiền vào ví bằng ' . $paymentMethod->name,
                'status' => 'cho_thanh_toan',
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
            ]);

            DB::commit();

            if (in_array($paymentMethod->name, ['MoMo', 'VNPAY'])) {
                return app(PaymentMethodController::class)->processWalletPayment(new Request([
                    'transaction_id' => $transaction->id,
                    'wallet_code' => $transaction->wallet_code,
                    'amount' => $transaction->amount,
                    'payment_method' => $paymentMethod->name,
                    'payment_method_id' => $paymentMethod->id,
                ]));
            }

            return response()->json([
                'message' => 'Khởi tạo giao dịch thành công',
                'transaction_id' => $transaction->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi khi khởi tạo giao dịch',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Trả về danh sách giao dịch ví của người dùng
     */
    public function transactions(Request $request)
    {
        $userId = Auth::id();
        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return response()->json(['message' => 'Không tìm thấy ví người dùng'], 404);
        }

        $transactions = WalletTransaction::with(['paymentMethod', 'order', 'createdBy', 'updatedBy'])
            ->where('wallet_id', $wallet->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($trx) {
                $actor = $trx->createdBy ?? $trx->updatedBy;
                return [
                    'id' => $trx->id,
                    'wallet_code' => $trx->wallet_code, // Mã GD
                    'order_code' => optional($trx->order)->order_code, // Mã đơn hàng (nếu có)
                    'type' => $trx->type,               // Loại GD
                    'amount' => $trx->amount,           // Số tiền
                    'balance_before' => $trx->balance_before,
                    'balance_after' => $trx->balance_after,
                    'status' => $trx->status,           // Trạng thái
                    'payment_method' => optional($trx->paymentMethod)->name, // Kênh
                    'actor_name' => optional($actor)->name,      // Người thực hiện
                    'description' => $trx->description,  // Mô tả
                    'created_at' =>   $trx->created_at->format('d-m-Y H:i:s'), // Thời gian
                    'updated_at' => $trx->updated_at->format('d-m-Y H:i:s'),
                ];
            });


        return response()->json([
            'transactions' => $transactions
        ]);
    }

    // hủy giao dịch
    public function cancelTransaction($id)
    {
        $userId = Auth::id();

        $transaction = WalletTransaction::whereHas('wallet', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->where('id', $id)
            ->where('type', 'nap_tien') // Chỉ áp dụng với giao dịch nạp tiền
            ->where('status', 'cho_thanh_toan') // Chỉ khi đang chờ thanh toán
            ->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Không tìm thấy giao dịch nạp tiền đang chờ hoặc bạn không có quyền hủy'
            ], 404);
        }

        try {
            $transaction->status = 'da_huy';
            $transaction->description .= ' (Giao dịch đã bị hủy)';
            $transaction->save();

            return response()->json([
                'message' => 'Hủy giao dịch thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi hủy giao dịch',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
