<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'image',
        'sender_id',
        'receiver_id',
        'is_read',
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'is_read' => 'boolean'
    ];

    /**
     * Lấy thông tin người gửi
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Lấy thông tin người nhận
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
