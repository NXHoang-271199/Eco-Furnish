<?php

namespace App\Http\Controllers\Api;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
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
    public function getBanks()
    {
        try {
            $response = Http::get('https://api.vietqr.io/v2/banks');

            if ($response->successful()) {
                return response()->json($response->json()['data']);
            } else {
                return response()->json(['message' => 'Không thể lấy danh sách ngân hàng'], 500);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi gọi API', 'error' => $e->getMessage()], 500);
        }
    }
    public function lookupBankAccount(Request $request)
    {
        $request->validate([
            'bank_account_number' => 'required|string',
            'bank_code' => 'required|string',  // Thay 'acq_id' bằng 'bank_code'
        ]);

        try {
            // URL và thông tin API Key, Secret
            $apiUrl = 'https://api.banklookup.net/api/bank/id-lookup-prod';
            $apiKey = 'b35792a4-b2aa-4773-9e59-132572ffcacfkey';  // API Key
            $apiSecret = 'a8c0fb6d-9e3b-4894-abe8-85a4e6004ae2secret';  // API Secret

            // Gửi yêu cầu tới API
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'x-api-secret' => $apiSecret,
                'Content-Type' => 'application/json',
            ])->post($apiUrl, [
                'bank' => $request->bank_code,  // Mã ngân hàng
                'account' => $request->bank_account_number,  // Số tài khoản cần tra cứu
            ]);

            // Kiểm tra nếu API trả về thành công
            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'accountName' => $data['data']['ownerName'],  // Lấy tên chủ tài khoản từ dữ liệu trả về
                ]);
            } else {
                return response()->json(['message' => 'Không tìm thấy thông tin tài khoản'], 404);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Lỗi khi tra cứu', 'error' => $e->getMessage()], 500);
        }
    }
}
