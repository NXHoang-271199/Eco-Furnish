<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view-messages');
        $this->middleware('permission:send-messages', ['only' => ['send', 'sendByAdmin']]);
    }

    public function index()
    {
        $messages = Message::orderBy('sent_at', 'asc')->get();
        return view('admins.messages.index', compact('messages'));
    }
}
