<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        // Validate request
        $request->validate([
            'text' => 'nullable|string',
            'image' => 'nullable|string',
            'receiver_id' => 'nullable|exists:users,id',
        ]);

        // Kiểm tra xác thực người dùng
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Kiểm tra dữ liệu đầu vào
        if (empty($request->text) && empty($request->image)) {
            return response()->json(['error' => 'Tin nhắn phải có nội dung văn bản hoặc hình ảnh'], 400);
        }

        $message = Message::create([
            'text' => $request->text,
            'image' => $request->image,
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'sent_at' => now(),
            'is_read' => false,
        ]);

        // Load relationships
        $message->load('sender');

        // Thêm trường is_read vào phản hồi JSON
        $response = $message->toArray();
        $response['is_read'] = false;

        return response()->json($response, 201);
    }

    public function getUserMessages($userId)
    {
        // Kiểm tra xác thực người dùng
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

<<<<<<< Updated upstream
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

            // Log các trường dữ liệu của messages để debug
            Log::info('Cấu trúc tin nhắn đầu tiên', [
                'sample' => $messages->first() ? $messages->first()->toArray() : 'Không có tin nhắn',
                'has_image_field' => $messages->first() ? array_key_exists('image', $messages->first()->toArray()) : false
            ]);

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
            ], 500);
=======
        // Admin có thể xem tin nhắn của bất kỳ người dùng nào
        // Người dùng thường chỉ có thể xem tin nhắn của họ
        if ($user->role !== 'admin' && $user->id != $userId) {
            return response()->json(['error' => 'Forbidden'], 403);
>>>>>>> Stashed changes
        }

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

        return response()->json($messages);
    }

    public function getUnreadMessages()
    {
        // Kiểm tra xác thực admin
        $user = Auth::user();
        if (!$user || $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $messages = Message::where('is_read', false)
            ->whereNotNull('receiver_id')
            ->with(['sender', 'receiver'])
            ->get();

        return response()->json($messages);
    }

    public function markAsRead($messageId)
    {
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
    }

    public function sendByAdmin(Request $request)
    {
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
    }

    public function markAllAsRead($userId)
    {
        // Kiểm tra xác thực người dùng
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Chỉ người dùng được đánh dấu là admin mới có thể đánh dấu tất cả là đã đọc
        if ($user->id != $userId && $user->role !== 'admin') {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        try {
            // Đánh dấu tất cả tin nhắn gửi đến người dùng này là đã đọc
            Message::where('receiver_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Tất cả tin nhắn đã được đánh dấu là đã đọc'
            ]);
        } catch (\Exception $e) {
            Log::error('Lỗi khi đánh dấu tin nhắn đã đọc', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Server Error',
                'message' => 'Lỗi khi đánh dấu tin nhắn đã đọc: ' . $e->getMessage()
            ], 500);
        }
    }
}
