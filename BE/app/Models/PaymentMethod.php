<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'name',
        'config',
        'image',
        'is_connected',
    ];
    protected $casts = [
        'config' => 'array', // Tự động cast JSON thành array
        'is_connected' => 'boolean'
    ];
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
