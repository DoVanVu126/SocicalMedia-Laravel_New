<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('user_id'); // Lấy user_id từ query param
    
        $posts = Post::with(['user', 'reactions'])->orderBy('created_at', 'desc')->get();
    
        if ($posts->isEmpty()) {
            return response()->json(['message' => 'Không có bài viết nào'], 200);
        }
    
        $posts->transform(function ($post) use ($userId) {
            $post->imageurl = $post->imageurl ? asset($post->imageurl) : null;
            $post->videourl = $post->videourl ? asset($post->videourl) : null;
    
            $reactionCounts = $post->reactions->groupBy('type')->map->count();
            $post->reaction_summary = $reactionCounts;
    
            // Lấy reaction của người dùng (nếu có user_id)
            $post->user_reaction = $userId ? $post->reactions->firstWhere('user_id', $userId) : null;
    
            return $post;
        });
    
        return response()->json($posts, 200);
    }
    
    

    public function store(Request $request)
{
    $request->validate([
        'content' => 'required|string',
        'user_id' => 'required|exists:users,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        'video' => 'nullable|mimes:mp4,avi,mkv|max:10240',
    ]);

    try {
        // Lưu ảnh và lấy tên file
        $imagePath = $request->hasFile('image')
            ? basename($request->file('image')->store('images', 'public'))
            : null;

        // Lưu video và lấy tên file
        $videoPath = $request->hasFile('video')
            ? basename($request->file('video')->store('videos', 'public'))
            : null;

        $post = Post::create([
            'user_id' => $request->user_id,
            'content' => $request->content,
            'imageurl' => $imagePath,
            'videourl' => $videoPath,
            'status' => 'draft',
        ]);

        return response()->json([
            'message' => 'Bài viết đã được tạo',
            'post' => $post,
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Có lỗi xảy ra khi tạo bài viết',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function destroy($id)
{
    $post = Post::find($id);

    if (!$post) {
        return response()->json(['message' => 'Bài viết không tồn tại'], 404);
    }

    // Xóa ảnh và video nếu có
    if ($post->imageurl) {
        Storage::disk('public')->delete('images/' . $post->imageurl);
    }
    if ($post->videourl) {
        Storage::disk('public')->delete('videos/' . $post->videourl);
    }

    $post->delete();

    return response()->json(['message' => 'Bài viết đã được xóa'], 200);
}

public function update(Request $request, $id)
{
    $request->validate([
        'content' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        'video' => 'nullable|mimes:mp4,avi,mkv|max:10240',
    ]);

    $post = Post::find($id);

    if (!$post) {
        return response()->json(['message' => 'Bài viết không tồn tại'], 404);
    }

    try {
        // Xóa ảnh cũ nếu có ảnh mới
        if ($request->hasFile('image')) {
            if ($post->imageurl) {
                Storage::disk('public')->delete('images/' . $post->imageurl);
            }
            $post->imageurl = basename($request->file('image')->store('images', 'public'));
        }

        // Xóa video cũ nếu có video mới
        if ($request->hasFile('video')) {
            if ($post->videourl) {
                Storage::disk('public')->delete('videos/' . $post->videourl);
            }
            $post->videourl = basename($request->file('video')->store('videos', 'public'));
        }

        $post->content = $request->content;
        $post->save();

        return response()->json(['message' => 'Cập nhật bài viết thành công', 'post' => $post], 200);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
    }
}

    public function changeStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:draft,published,archived',
            'user_id' => 'required|exists:users,id',
        ]);

        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        // Kiểm tra quyền (tùy chọn, nếu cần)
        if ($post->user_id !== $request->user_id) {
            return response()->json(['message' => 'Bạn không có quyền thay đổi trạng thái bài viết này'], 403);
        }

        $post->status = $request->status;
        $post->save();

        return response()->json(['message' => 'Trạng thái bài viết đã được cập nhật', 'post' => $post], 200);
    }

    public function react(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:like,love,haha,wow,sad,angry',
        ]);

        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        $reaction = Reaction::updateOrCreate(
            ['post_id' => $id, 'user_id' => $request->user_id],
            ['type' => $request->type]
        );

        return response()->json(['message' => 'Đã phản ứng bài viết', 'reaction' => $reaction], 200);
    }

    public function removeReaction(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $reaction = Reaction::where('post_id', $id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$reaction) {
            return response()->json(['message' => 'Không tìm thấy reaction'], 404);
        }

        $reaction->delete();

        return response()->json(['message' => 'Đã xóa reaction'], 200);
    }
}

