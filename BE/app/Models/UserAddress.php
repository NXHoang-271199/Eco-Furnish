<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'full_name',
        'phone',
        'email',
        'address_name',
        'country',
        'province',
        'district',
        'ward',
        'street_address',
        'is_default'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($address) {
            if ($address->isDirty('first_name') || $address->isDirty('last_name')) {
                $address->full_name = trim(($address->first_name ?? '') . ' ' . ($address->last_name ?? ''));
            }
        });
    }

    /**
     * Get the user that owns the address.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
