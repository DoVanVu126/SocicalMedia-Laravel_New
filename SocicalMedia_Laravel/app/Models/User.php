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
        'username', 'email', 'password', 'phone', 'profilepicture', 'two_factor_enabled',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Check if email is valid or not
     * @param string $email
     * @return bool
     */
    public static function checkEmail($email)
    {
        $is_valid = true;
        $user = User::where('email', $email)->first();
        if ($user) {
            $is_valid = false;
        }
        return $is_valid;
    }

    /**
     * Check if phone number is valid or not
     * @param string $phone
     * @return bool
     */
    public static function checkPhone($phone)
    {
        $is_valid = true;
        $user = User::where('phone', $phone)->first();
        if ($user) {
            $is_valid = false;
        }
        return $is_valid;
    }

}
