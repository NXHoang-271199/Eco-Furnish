<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $message = Message::create([
            'text' => $request->text,
            'userId' => $request->userId,
            'userName' => $request->userName,
            'adminId' => $request->adminId,
            'adminName' => $request->adminName,
            'type' => $request->type,
            'sent_at' => now(),
        ]);

        return response()->json($message, 201);
    }

    public function getUserMessages($userId)
    {
        $messages = Message::where('userId', $userId)
            ->orderBy('sent_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function getUnreadMessages()
    {
        $messages = Message::where('is_read', false)
            ->where('type', 'client')
            ->get();

        return response()->json($messages);
    }

    public function markAsRead($messageId)
    {
        $message = Message::findOrFail($messageId);
        $message->update(['is_read' => true]);

        return response()->json(['message' => 'Message marked as read']);
    }

    public function sendByAdmin(Request $request)
    {
        $message = Message::create([
            'text' => $request->text,
            'userId' => $request->userId,
            'userName' => $request->userName,
            'adminId' => $request->adminId,
            'adminName' => $request->adminName,
            'type' => 'admin',
            'sent_at' => now(),
        ]);

        return response()->json($message, 201);
    }
}
