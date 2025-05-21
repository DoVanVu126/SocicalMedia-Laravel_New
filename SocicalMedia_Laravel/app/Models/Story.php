<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Story extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'imageurl', 'videourl', 'content', 'visibility', 'expires_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
