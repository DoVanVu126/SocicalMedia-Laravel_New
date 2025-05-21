<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;  // Chỉ cần nếu bạn muốn sử dụng factories để tạo mẫu dữ liệu

    protected $fillable = ['post_id', 'user_id', 'content'];

    // Quan hệ với người dùng
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Quan hệ với bài viết
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

