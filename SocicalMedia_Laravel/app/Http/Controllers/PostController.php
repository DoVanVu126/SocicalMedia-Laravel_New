<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        // Lấy bài viết kèm user và reaction (gồm cả user phản ứng)
        $posts = Post::with(['user', 'reactions.user'])->orderBy('created_at', 'desc')->get();
        return response()->json($posts, 200);
    }

    public function addReaction(Request $request)
    {
        // Validate input
        $request->validate([
            'post_id' => 'required|exists:posts,id',
            'type' => 'required|string',
            'user_id' => 'required|integer', // user_id được gửi từ frontend
        ]);

        // Kiểm tra đã từng phản ứng chưa
        $existing = Reaction::where('post_id', $request->post_id)
                            ->where('user_id', $request->user_id)
                            ->first();

        if ($existing) {
            // Nếu đã có phản ứng thì thay đổi loại cảm xúc
            $existing->update(['type' => $request->type]);
            return response()->json($existing, 200);
        }

        // Tạo mới reaction
        $reaction = Reaction::create([
            'user_id' => $request->user_id,
            'post_id' => $request->post_id,
            'type' => $request->type,
        ]);

        return response()->json($reaction, 201);
    }

    public function removeReaction($postId, $reactionId)
    {
        $reaction = Reaction::where('post_id', $postId)
                            ->where('id', $reactionId)
                            ->firstOrFail();

        $reaction->delete();

        return response()->json(['message' => 'Reaction deleted successfully'], 200);
    }

    public function updateReaction(Request $request, $postId)
    {
        // Validate input
        $request->validate([
            'user_id' => 'required|integer', // user_id được gửi từ frontend
            'type' => 'required|string', // Loại phản ứng mới
        ]);

        // Kiểm tra xem người dùng đã có phản ứng nào cho bài viết chưa
        $existing = Reaction::where('post_id', $postId)
                            ->where('user_id', $request->user_id)
                            ->first();

        if ($existing) {
            // Nếu có phản ứng, thay đổi loại phản ứng
            $existing->update(['type' => $request->type]);
            return response()->json($existing, 200);
        }

        // Nếu chưa có phản ứng, tạo mới một phản ứng
        $reaction = Reaction::create([
            'user_id' => $request->user_id,
            'post_id' => $postId,
            'type' => $request->type,
        ]);

        return response()->json($reaction, 201);
    }

}
