<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewReply extends Model
{
    use HasFactory;
    protected $fillable = ['review_id', 'user_id', 'reply_text'];

    public function review() {
        return $this->belongsTo(Review::class);
    }
}
