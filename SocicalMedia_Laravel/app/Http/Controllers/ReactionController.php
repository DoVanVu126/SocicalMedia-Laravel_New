<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReactionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'type' => 'required|in:like,love,haha,wow,sad,angry',
            'user_id' => 'required|exists:users,id',
        ]);

        $reaction = Reaction::updateOrCreate(
            [
                'post_id' => $request->post_id,
                'user_id' => $request->user_id,
            ],
            [
                'type' => $request->type,
            ]
        );

        // Create notification for post owner
        $post = Post::find($request->post_id);
        if ($post && $post->user_id != $request->user_id) {
            $user = User::find($request->user_id);
            Log::info("Reaction stored for post_id: {$request->post_id}, user_id: {$request->user_id}");
            NotificationController::createNotification(
                $post->user_id,
                "{$user->username} đã thả cảm xúc {$request->type} trên bài viết của bạn.",
                $request->post_id,
                'post'
            );
        }

        return response()->json($reaction, 201);
    }

    public function index($postId)
    {
        $reactions = Reaction::where('post_id', $postId)
            ->with('user:id,username,profilepicture')
            ->latest()
            ->get();

        return response()->json($reactions);
    }
}
