<?php

namespace App\Http\Controllers\Api;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\BankAccountRequest;

class BankAccountController extends Controller
{
    /**
     * Lấy danh sách tài khoản ngân hàng của người dùng
     */
    public function getUserBankAccounts()
    {
        try {
            $user = Auth::user();

            if ($user->role_id !== 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không có quyền truy cập vào thông tin này'
                ], 403);
            }

            $bankAccounts = BankAccount::where('user_id', $user->id)
                ->orderBy('is_default', 'desc')
                ->get();

            if ($bankAccounts->isEmpty()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Người dùng chưa có tài khoản ngân hàng nào',
                    'data' => []
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $bankAccounts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Đã xảy ra lỗi khi lấy tài khoản ngân hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo mới tài khoản ngân hàng cho người dùng
     */
    public function storeUserBankAccount(BankAccountRequest $request)
    {
        try {
            $user = Auth::user();

            if ($user->role_id !== 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không có quyền thêm tài khoản ngân hàng cho người dùng này'
                ], 403);
            }

            if ($request->is_default) {
                BankAccount::where('user_id', $user->id)->update(['is_default' => false]);
            }

            $existingBankAccounts = BankAccount::where('user_id', $user->id)->count();
            if ($existingBankAccounts === 0) {
                $request->merge(['is_default' => true]);
            }

            $bankAccount = BankAccount::create(array_merge($request->all(), [
                'user_id' => $user->id
            ]));

            return response()->json([
                'status' => 'success',
                'message' => 'Thêm tài khoản ngân hàng thành công',
                'data' => $bankAccount
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể thêm tài khoản ngân hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật tài khoản ngân hàng
     */
    public function updateUserBankAccount(BankAccountRequest $request, $accountId)
    {
        try {
            $user = Auth::user();

            if ($user->role_id !== 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không có quyền cập nhật tài khoản ngân hàng của người dùng này'
                ], 403);
            }

            $bankAccount = BankAccount::where('user_id', $user->id)->findOrFail($accountId);

            if ($request->has('is_default') && $request->is_default) {
                BankAccount::where('user_id', $user->id)
                    ->where('id', '!=', $accountId)
                    ->update(['is_default' => false]);
            }

            $bankAccount->update($request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật tài khoản ngân hàng thành công',
                'data' => $bankAccount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể cập nhật tài khoản ngân hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa tài khoản ngân hàng
     */
    public function deleteUserBankAccount($accountId)
    {
        try {
            $user = Auth::user();

            if ($user->role_id !== 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không có quyền xóa tài khoản ngân hàng của người dùng này'
                ], 403);
            }

            $bankAccount = BankAccount::where('user_id', $user->id)->findOrFail($accountId);

            $wasDefault = $bankAccount->is_default;
            $bankAccount->delete();

            if ($wasDefault) {
                $firstBankAccount = BankAccount::where('user_id', $user->id)->first();
                if ($firstBankAccount) {
                    $firstBankAccount->update(['is_default' => true]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Xóa tài khoản ngân hàng thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa tài khoản ngân hàng',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
