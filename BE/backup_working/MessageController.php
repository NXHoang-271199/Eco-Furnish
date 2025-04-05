<?php

namespace App\Http\Controllers\Api;

use Log;
use App\Models\User;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    /**
     * Constructor: Áp dụng middleware xác thực cho các phương thức cần thiết
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['store']);
    }

    /**
     * Lưu tin nhắn từ Socket.io server
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'text' => 'required|string',
            'sender_type' => 'required|in:client,admin,bot'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Xác định userName and adminName dựa vào sender_type và user_id
            $userName = null;
            $adminName = null;

            if ($request->sender_type == 'client') {
                // Lấy tên người dùng từ ID
                $user = User::find($request->user_id);
                $userName = $user ? $user->name : 'Khách';
                $adminName = 'Admin'; // Tên mặc định của admin
            } else if ($request->sender_type == 'admin') {
                $userName = null;
                $adminName = 'Admin';
            }

            // Thời gian gửi tin nhắn
            $sentAt = now();

            // Lưu tin nhắn
            $message = Message::create([
                'text' => $request->text,
                'userId' => $request->user_id,
                'userName' => $userName,
                'adminId' => $request->sender_type == 'admin' ? 1 : null,
                'adminName' => $adminName,
                'type' => $request->sender_type,
                'sent_at' => $sentAt,
                'is_read' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tin nhắn đã được lưu thành công',
                'data' => $message
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lưu tin nhắn',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách tin nhắn của một người dùng
     */
    public function getUserMessages(Request $request, $userId)
    {
        // Kiểm tra người dùng đã xác thực chưa
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated. Vui lòng đăng nhập lại.',
                'error_code' => 'auth_required'
            ], 401);
        }

        // Kiểm tra quyền truy cập
        if (Auth::user()->role->slug !== 'admin' && Auth::user()->id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền xem tin nhắn của người dùng này'
            ], 403);
        }

        try {
            // Log để kiểm tra
            Log::info('Truy vấn tin nhắn cho user ID: ' . $userId);

            $messages = Message::where('userId', $userId)
                ->orderBy('sent_at', 'asc')
                ->get();

            $userData = User::find($userId);
            $userName = $userData ? $userData->name : 'Unknown User';

            \Log::info('Số lượng tin nhắn tìm thấy: ' . $messages->count());

            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $messages,
                    'user' => [
                        'id' => $userId,
                        'name' => $userName
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Lỗi khi lấy tin nhắn: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy tin nhắn',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách tin nhắn chưa đọc (chỉ dành cho admin)
     */
    public function getUnreadMessages(Request $request)
    {
        // Kiểm tra quyền admin
        if (Auth::user()->role->slug !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập chức năng này'
            ], 403);
        }

        try {
            // Lấy tất cả tin nhắn chưa đọc, gom nhóm theo user_id
            $unreadMessages = Message::where('is_read', false)
                ->where('sender_type', 'client')  // Chỉ lấy tin nhắn từ khách hàng
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('user_id');

            // Xử lý và bổ sung thông tin người dùng
            $result = [];
            foreach ($unreadMessages as $userId => $messages) {
                $user = User::find($userId);
                $result[] = [
                    'user_id' => $userId,
                    'user_name' => $user ? $user->name : 'Unknown User',
                    'unread_count' => count($messages),
                    'last_message' => $messages->first(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi lấy tin nhắn chưa đọc',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Đánh dấu tin nhắn đã đọc
     */
    public function markAsRead(Request $request, $messageId)
    {
        try {
            $message = Message::findOrFail($messageId);

            // Kiểm tra quyền: chỉ admin hoặc người nhận tin nhắn mới có thể đánh dấu đã đọc
            if (Auth::user()->role->slug !== 'admin' && Auth::user()->id != $message->recipient_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền thực hiện hành động này'
                ], 403);
            }

            $message->is_read = true;
            $message->save();

            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu tin nhắn là đã đọc'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi đánh dấu tin nhắn',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin gửi tin nhắn cho người dùng
     */
    public function sendByAdmin(Request $request)
    {
        // Kiểm tra quyền admin
        if (Auth::user()->role->slug !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền thực hiện chức năng này'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'text' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find($request->user_id);
            $userName = $user ? $user->name : 'Unknown User';

            $message = Message::create([
                'text' => $request->text,
                'userId' => $request->user_id,
                'userName' => $userName,
                'adminId' => Auth::id(),
                'adminName' => Auth::user()->name ?? 'Admin',
                'type' => 'admin',
                'sent_at' => now(),
                'is_read' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tin nhắn đã được gửi thành công',
                'data' => $message
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi khi gửi tin nhắn',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
