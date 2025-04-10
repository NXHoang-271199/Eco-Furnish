<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserAddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
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

    /**
     * Lấy danh sách địa chỉ của người dùng
     */
    public function getUserAddresses($userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Kiểm tra xem người dùng hiện tại có quyền truy cập vào thông tin này không
            if (Auth::id() != $userId && !Auth::user()->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không có quyền truy cập vào thông tin này'
                ], 403);
            }
            
            $addresses = $user->addresses()->orderBy('is_default', 'desc')->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $addresses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy người dùng',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Tạo mới địa chỉ cho người dùng
     */
    public function storeUserAddress(Request $request, $userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Kiểm tra xem người dùng hiện tại có quyền truy cập vào thông tin này không
            if (Auth::id() != $userId && !Auth::user()->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không có quyền thêm địa chỉ cho người dùng này'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'phone' => 'required|string|max:20',
                'email' => 'nullable|email|max:255',
                'address_name' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'province' => 'required|string|max:100',
                'district' => 'required|string|max:100',
                'ward' => 'required|string|max:100',
                'street_address' => 'required|string',
                'is_default' => 'boolean'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422);
            }
            
            // Nếu địa chỉ này được đánh dấu là mặc định, cập nhật tất cả các địa chỉ khác thành không mặc định
            if ($request->is_default) {
                $user->addresses()->update(['is_default' => false]);
            }
            
            // Nếu đây là địa chỉ đầu tiên, đánh dấu là mặc định
            $existingAddresses = $user->addresses()->count();
            if ($existingAddresses === 0) {
                $request->merge(['is_default' => true]);
            }
            
            $address = $user->addresses()->create($request->all());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Thêm địa chỉ thành công',
                'data' => $address
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể thêm địa chỉ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật địa chỉ
     */
    public function updateUserAddress(Request $request, $userId, $addressId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Kiểm tra xem người dùng hiện tại có quyền truy cập vào thông tin này không
            if (Auth::id() != $userId && !Auth::user()->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không có quyền cập nhật địa chỉ của người dùng này'
                ], 403);
            }
            
            $address = $user->addresses()->findOrFail($addressId);
            
            $validator = Validator::make($request->all(), [
                'first_name' => 'string|max:50',
                'last_name' => 'string|max:50',
                'phone' => 'string|max:20',
                'email' => 'nullable|email|max:255',
                'address_name' => 'nullable|string|max:100',
                'country' => 'nullable|string|max:100',
                'province' => 'string|max:100',
                'district' => 'string|max:100',
                'ward' => 'string|max:100',
                'street_address' => 'string',
                'is_default' => 'boolean'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()
                ], 422);
            }
            
            // Nếu địa chỉ này được đánh dấu là mặc định, cập nhật tất cả các địa chỉ khác thành không mặc định
            if ($request->has('is_default') && $request->is_default) {
                $user->addresses()->where('id', '!=', $addressId)->update(['is_default' => false]);
            }
            
            $address->update($request->all());
            
            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật địa chỉ thành công',
                'data' => $address
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể cập nhật địa chỉ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa địa chỉ
     */
    public function deleteUserAddress($userId, $addressId)
    {
        try {
            $user = User::findOrFail($userId);
            
            // Kiểm tra xem người dùng hiện tại có quyền truy cập vào thông tin này không
            if (Auth::id() != $userId && !Auth::user()->isAdmin()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Bạn không có quyền xóa địa chỉ của người dùng này'
                ], 403);
            }
            
            $address = $user->addresses()->findOrFail($addressId);
            
            // Lưu trạng thái is_default
            $wasDefault = $address->is_default;
            
            $address->delete();
            
            // Nếu địa chỉ đã xóa là mặc định và còn địa chỉ khác, đặt địa chỉ đầu tiên làm mặc định
            if ($wasDefault) {
                $firstAddress = $user->addresses()->first();
                if ($firstAddress) {
                    $firstAddress->update(['is_default' => true]);
                }
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Xóa địa chỉ thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Không thể xóa địa chỉ',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
