<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['notification_content', 'is_read'];

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
