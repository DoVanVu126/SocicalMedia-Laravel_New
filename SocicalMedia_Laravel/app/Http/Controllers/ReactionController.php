<?php
namespace App\Http\Controllers;

use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'type' => 'required|in:like,love,haha,wow,sad,angry',
        ]);

        $userId = auth()->id() ?? 2; // Tạm dùng user ID = 2 nếu chưa có auth

        $reaction = Reaction::updateOrCreate(
            ['post_id' => $request->post_id, 'user_id' => $userId],
            ['type' => $request->type]
        );

        return response()->json($reaction);
    }
}
