<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        // Lấy tất cả bài viết và trả về dưới dạng JSON
        $posts = Post::with('user')->orderBy('created_at', 'desc')->get();
        
        if ($posts->isEmpty()) {
            return response()->json(['message' => 'Không có bài viết nào'], 200);
        }

        return response()->json($posts, 200); // Trả về mảng bài viết
    }
}
