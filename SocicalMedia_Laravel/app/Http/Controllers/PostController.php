<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    // Lấy danh sách bài viết
    public function index()
    {
        $posts = Post::with('user')->orderBy('created_at', 'desc')->get();

        if ($posts->isEmpty()) {
            return response()->json(['message' => 'Không có bài viết nào'], 200);
        }

        $posts->transform(function ($post) {
            $post->imageurl = $post->imageurl ? asset($post->imageurl) : null;
            $post->videourl = $post->videourl ? asset($post->videourl) : null;
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
// PostController.php
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

}
