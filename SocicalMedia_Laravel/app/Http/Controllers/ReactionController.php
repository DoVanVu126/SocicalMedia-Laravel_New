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
            'user_id' => 'required|exists:users,id',
        ]);

        $reaction = Reaction::create([
            'post_id' => $request->post_id,
            'user_id' => $request->user_id,
            'type'    => $request->type,
        ]);

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
