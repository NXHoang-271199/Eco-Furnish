<?php

namespace App\Http\Controllers\Api;

use Log;
use App\Models\User;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
<<<<<<< HEAD
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
=======
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7

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
<<<<<<< HEAD
        // Validate request
        $request->validate([
            'text' => 'required|string',
            'receiver_id' => 'nullable|exists:users,id',
        ]);

        // Kiểm tra xác thực người dùng
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $message = Message::create([
            'text' => $request->text,
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'sent_at' => now(),
            'is_read' => false,
        ]);

        // Load relationships
        $message->load('sender');
        return response()->json($message, 201);
=======
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
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
    }

    /**
     * Lấy danh sách tin nhắn của một người dùng
     */
    public function getUserMessages(Request $request, $userId)
    {
<<<<<<< HEAD
        // Kiểm tra xác thực người dùng
        $user = Auth::user();
        if (!$user) {
            Log::error('Lỗi xác thực: Không có người dùng');
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Debug thông tin user
        Log::info('User info in getUserMessages', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_id' => $user->role_id ?? null,
            'user_token' => request()->bearerToken(),
            'role type' => gettype($user->role),
            'requested userId' => $userId
        ]);

        // Luôn cho phép xem tin nhắn (tạm thời để debug)
        $isAdmin = true;

        Log::info('Is user admin?', ['isAdmin' => $isAdmin]);

        // Bỏ qua kiểm tra quyền - mọi người dùng đều có thể xem tin nhắn (tạm thời để debug)
        // if (!$isAdmin && $user->id != $userId) {
        //     return response()->json([
        //         'error' => 'Forbidden',
        //         'message' => 'Bạn không có quyền xem tin nhắn của người dùng khác',
        //         'user_id' => $user->id,
        //         'requested_user_id' => $userId,
        //         'is_admin' => $isAdmin
        //     ], 403);
        // }

        try {
            $messages = Message::where(function ($query) use ($userId) {
                    $query->where('sender_id', $userId)
                        ->orWhere('receiver_id', $userId);
                })
                ->with(['sender', 'receiver'])
                ->orderBy('sent_at', 'asc')
                ->get();

            // Đánh dấu tất cả tin nhắn đến người dùng này là đã đọc
            // nếu người đọc là người nhận
            if ($user->id == $userId) {
                Message::where('receiver_id', $userId)
                    ->where('is_read', false)
                    ->update(['is_read' => true]);
            }

            Log::info('Tin nhắn đã tải', ['count' => $messages->count()]);
            return response()->json($messages);
        } catch (\Exception $e) {
            Log::error('Lỗi khi tải tin nhắn', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Server Error',
                'message' => 'Lỗi khi tải tin nhắn: ' . $e->getMessage()
=======
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
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
            ], 500);
        }
    }

    /**
     * Lấy danh sách tin nhắn chưa đọc (chỉ dành cho admin)
     */
    public function getUnreadMessages(Request $request)
    {
<<<<<<< HEAD
        // Kiểm tra xác thực admin
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $messages = Message::where('is_read', false)
            ->whereNotNull('receiver_id')
            ->with(['sender', 'receiver'])
            ->get();
=======
        // Kiểm tra quyền admin
        if (Auth::user()->role->slug !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập chức năng này'
            ], 403);
        }
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7

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
<<<<<<< HEAD
        // Kiểm tra xác thực người dùng
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $message = Message::findOrFail($messageId);

        // Chỉ người nhận tin nhắn mới có thể đánh dấu là đã đọc
        if ($user->id != $message->receiver_id && $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $message->update(['is_read' => true]);

        return response()->json(['message' => 'Tin nhắn đã được đánh dấu là đã đọc']);
=======
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
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
    }

    /**
     * Admin gửi tin nhắn cho người dùng
     */
    public function sendByAdmin(Request $request)
    {
<<<<<<< HEAD
        // Kiểm tra xác thực admin
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate request
        $request->validate([
            'text' => 'required|string',
            'receiver_id' => 'required|exists:users,id'
        ]);

        $message = Message::create([
            'text' => $request->text,
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'sent_at' => now(),
            'is_read' => false,
        ]);

        // Load relationships
        $message->load(['sender', 'receiver']);

        return response()->json($message, 201);
=======
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
>>>>>>> 2e14406b3e596a8478022de3f9eb13f92e5eddb7
    }
}
