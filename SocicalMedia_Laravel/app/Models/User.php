<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'phone',
        'profilepicture',
        'two_factor_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_enabled' => 'boolean',
    ];

    /**
     * Kiểm tra email đã tồn tại chưa
     *
     * @param string $email
     * @return bool
     */
    public static function checkEmail(string $email): bool
    {
        return !self::where('email', $email)->exists();
    }

    /**
     * Kiểm tra số điện thoại đã tồn tại chưa
     *
     * @param string $phone
     * @return bool
     */
    public static function checkPhone(string $phone): bool
    {
        return !self::where('phone', $phone)->exists();
    }

    /**
     * Những người follow user này (người được follow)
     */
    public function followers()
    {
        return $this->hasMany(Follow::class, 'followed_id');
    }

    /**
     * Những người user này đang follow (người follow)
     */
    public function followings()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }

    /**
     * Kiểm tra user hiện tại có đang follow user $userId không
     *
     * @param int $userId
     * @return bool
     */
    public function isFollowing(int $userId): bool
    {
        return $this->followings()->where('followed_id', $userId)->exists();
    }

    /**
     * Các bài đăng của user
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
