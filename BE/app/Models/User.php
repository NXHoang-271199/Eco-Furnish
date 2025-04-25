<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Post;
use App\Models\Role;
use App\Models\Comment;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, CanResetPassword;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'age',
        'email',
        'password',
        'level2_password',
        'has_level2_password',
        'address',
        'phone',
        'role_id',
        'is_active',
        'avatar',
        'email_verified_at',
        'email_verification_token',
        'access_token',
        'refresh_token',
        'remember_me',
        'remember_me_expires_at'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Kiểm tra xem người dùng có quyền cụ thể không
     *
     * @param string|array $permissions
     * @return bool
     */
    public function hasPermission($permissions)
    {
        if (!$this->role) {
            return false;
        }

        return $this->role->hasPermission($permissions);
    }

    /**
     * Kiểm tra xem người dùng có phải là admin không
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role && $this->role->slug === 'admin';
    }

    /**
     * Kiểm tra xem người dùng có phải là chủ sở hữu của một model không
     *
     * @param mixed $model
     * @return bool
     */
    public function owns($model)
    {
        return $model && $model->user_id === $this->id;
    }

    public function scopeSearch($query, $fillers)
    {
        if (!empty($fillers['name'])) {
            $query->where('name', 'like', '%' . $fillers['name'] . '%');
        }

        if (!empty($fillers['email'])) {
            $query->where('email', 'like', '%' . $fillers['email'] . '%');
        }

        if (!empty($fillers['role'])) {
            $query->whereHas('role', function($q) use ($fillers) {
                $q->where('slug', $fillers['role']);
            });
        }

        return $query;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'level2_password',
        'access_token',
        'refresh_token'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'level2_password' => 'hashed',
        'has_level2_password' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function getDisplayNameAttribute()
    {
        if (is_string($this->name) && is_array(json_decode($this->name, true))) {
            $userData = json_decode($this->name, true);
            return $userData['name'] ?? 'Admin';
        }
        return $this->name ?? 'Admin';
    }

    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the comments for the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function voucherUsages()
    {
        return $this->hasMany(VoucherUsage::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    public function refundRequest()
    {
        return $this->hasMany(RefundRequest::class);
    }

    /**
     * Get the addresses for the user.
     */
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }
    // Mối quan hệ: Mỗi người dùng có thể có nhiều yêu cầu rút tiền
    public function withdrawRequests()
    {
        return $this->hasMany(WithdrawRequest::class);
    }
    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }
}
