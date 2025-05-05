<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // App\Models\Comment.php
    protected $fillable = ['post_id', 'user_id', 'content'];

    public function index($postId)
    {
        $comments = Comment::where('post_id', $postId)
            ->with('user:id,username') // Lấy thêm tên người dùng
            ->latest()
            ->get();

        return response()->json($comments);
    }

    public function store(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
        ]);

        $comment = Comment::create([
            'post_id' => $postId,
            'user_id' => $request->user_id,
            'content' => $request->content,
        ]);

        return response()->json($comment, 201);
    }
}
