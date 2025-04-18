<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Wallet;
use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\WithdrawRequest;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        $transactions = WalletTransaction::with([
            'paymentMethod',
            'order',
            'createdBy',
            'updatedBy',
            'withdrawRequest'
        ])
            ->where('wallet_id', $wallet->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($trx) {
                $actor = $trx->createdBy ?? $trx->updatedBy;

                $data = [
                    'id' => $trx->id,
                    'wallet_code' => $trx->wallet_code,
                    'order_code' => optional($trx->order)->order_code,
                    'type' => $trx->type,
                    'amount' => $trx->amount,
                    'balance_before' => $trx->balance_before,
                    'balance_after' => $trx->balance_after,
                    'status' => $trx->status,
                    'payment_method' => optional($trx->paymentMethod)->name,
                    'actor_name' => optional($actor)->name,
                    'description' => $trx->description,
                    'created_at' => $trx->created_at->format('d-m-Y H:i:s'),
                    'updated_at' => $trx->updated_at->format('d-m-Y H:i:s'),
                ];

                if ($trx->type === 'rut_tien' && $trx->withdrawRequest) {
                    $data['withdraw_request'] = [
                        'bank_name' => $trx->withdrawRequest->bank_name,
                        'bank_account_number' => $trx->withdrawRequest->bank_account_number,
                        'account_holder_name' => $trx->withdrawRequest->account_holder_name,
                        'bank_code' => $trx->withdrawRequest->bank_code,
                        'qr_code' => $trx->withdrawRequest->qr_code,
                        'status' => $trx->withdrawRequest->status,
                    ];
                }

                return $data;
            });

        return response()->json([
            'transactions' => $transactions
        ]);
    }

    // Hủy giao dịch
    public function cancelTransaction($id)
    {
        $userId = Auth::id();

        $transaction = WalletTransaction::with('withdrawRequest')
            ->whereHas('wallet', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->where('id', $id)
            ->whereIn('type', ['nap_tien', 'rut_tien']) // Áp dụng cho cả nạp và rút
            ->where('status', 'cho_thanh_toan') // Chỉ khi đang chờ thanh toán
            ->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Không tìm thấy giao dịch phù hợp hoặc bạn không có quyền hủy'
            ], 404);
        }

        try {
            // Cập nhật trạng thái giao dịch
            $transaction->status = 'da_huy';
            $transaction->description .= ' (Giao dịch đã bị hủy)';
            $transaction->save();

            // Nếu là giao dịch rút tiền, cập nhật luôn yêu cầu rút
            if ($transaction->type === 'rut_tien' && $transaction->withdrawRequest) {
                $transaction->withdrawRequest->status = 'da_huy';
                $transaction->withdrawRequest->note = 'Yêu cầu rút tiền đã bị hủy';
                $transaction->withdrawRequest->save();
            }

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

    // render mã qr
    public function generateQrPreview(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100000|max:10000000',
            'bank_account_id' => 'required|exists:bank_accounts,id',
        ], [
            'amount.min' => 'Số tiền rút tối thiểu là 100.000 VNĐ',
            'amount.max' => 'Số tiền rút tối đa là 10.000.000 VNĐ',
        ]);

        $userId = Auth::id();
        $bankAccount = BankAccount::find($request->bank_account_id);

        try {
            $qrCodePath = createVietQrCode($bankAccount, $request->amount, $userId);
            return response()->json([
                'qr_code' => $qrCodePath,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Lỗi khi tạo mã QR',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // yêu cầu rút tiền

    public function storeWithdrawRequest(Request $request)
    {
        // Kiểm tra dữ liệu đầu vào
        $request->validate([
            'amount' => 'required|numeric|min:100000|max:10000000',
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'qr_code' => 'nullable|string',
        ], [
            'amount.min' => 'Số tiền rút tối thiểu là 100.000 VNĐ',
            'amount.max' => 'Số tiền rút tối đa là 10.000.000 VNĐ',
        ]);

        $userId = Auth::id();
        $user = User::find($userId);
        // Kiểm tra nếu người dùng chưa có mật khẩu cấp 2
        if (!$user->has_level2_password) {
            return response()->json(['message' => 'Bạn cần thiết lập mật khẩu cấp 2 để thực hiện yêu cầu rút tiền'], 422);
        }

        // Kiểm tra mật khẩu cấp 2 nhập vào có đúng không
        if (!Hash::check($request->level2_password, $user->level2_password)) {
            return response()->json(['message' => 'Mật khẩu cấp 2 không chính xác'], 422);
        }

        $wallet = Wallet::where('user_id', $userId)->first();

        if (!$wallet) {
            return response()->json(['message' => 'Không tìm thấy ví người dùng'], 404);
        }

        // Kiểm tra số dư ví
        if ($wallet->balance < $request->amount) {
            return response()->json(['message' => 'Số dư ví không đủ để rút tiền'], 400);
        }

        // Kiểm tra số tiền đã rút trong ngày
        $totalWithdrawToday = WithdrawRequest::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->whereIn('status', ['da_duyet', 'dang_xu_ly'])
            ->sum('amount');

        if ($totalWithdrawToday + $request->amount > 50000000) {
            return response()->json(['message' => 'Bạn không thể rút quá 50 triệu VNĐ trong ngày'], 400);
        }

        // Kiểm tra số lần rút tiền thành công trong ngày
        $totalWithdrawCountToday = WithdrawRequest::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->whereIn('status', ['da_duyet', 'dang_xu_ly'])
            ->count();

        if ($totalWithdrawCountToday >= 5) {
            return response()->json(['message' => 'Bạn chỉ được rút tối đa 5 lần trong ngày'], 400);
        }

        $bankAccount = BankAccount::find($request->bank_account_id);

        if (!$bankAccount) {
            return response()->json(['message' => 'Không tìm thấy tài khoản ngân hàng'], 404);
        }

        // Tạo yêu cầu rút tiền
        DB::beginTransaction();
        try {
            // Tạo yêu cầu rút tiền với thông tin ngân hàng snapshot
            $withdrawRequest = WithdrawRequest::create([
                'user_id' => $userId,
                'amount' => $request->amount,
                'status' => 'dang_xu_ly',
                'qr_code' => $request->qr_code,
                'bank_name' => $bankAccount->bank_name,
                'bank_code' => $bankAccount->bank_code,
                'bank_account_number' => $bankAccount->bank_account_number,
                'account_holder_name' => $bankAccount->account_holder_name,
            ]);

            // Cập nhật số dư ví, tạm giữ số tiền rút
            $wallet->balance -= $request->amount;
            $wallet->save();

            // Tạo giao dịch ví (tạm giữ số tiền)
            $walletTransaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $request->amount,
                'type' => 'rut_tien',
                'status' => 'cho_thanh_toan',
                'description' => 'Yêu cầu rút tiền',
                'balance_before' => $wallet->balance + $request->amount,
                'balance_after' => $wallet->balance,
            ]);

            // Cập nhật yêu cầu rút tiền với wallet_transaction_id
            $withdrawRequest->wallet_transaction_id = $walletTransaction->id;
            $withdrawRequest->save();

            DB::commit();

            return response()->json([
                'message' => 'Yêu cầu rút tiền đã được tạo thành công',
                'withdraw_request_id' => $withdrawRequest->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi khi tạo yêu cầu rút tiền',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
