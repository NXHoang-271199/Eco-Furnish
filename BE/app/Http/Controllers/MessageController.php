<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index()
    {
        $messages = Message::orderBy('sent_at', 'asc')->get();
        return view('admins.messages.index', compact('messages'));
    }
}
