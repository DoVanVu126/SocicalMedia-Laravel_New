<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    // Lấy danh sách bài viết
    public function index()
    {
        $posts = Post::with('user')->orderBy('created_at', 'desc')->get();

        if ($posts->isEmpty()) {
            return response()->json(['message' => 'Không có bài viết nào'], 200);
        }

        // Thêm đường dẫn đầy đủ cho ảnh và video
        $posts->transform(function ($post) {
            // Đảm bảo rằng đường dẫn là đúng, ảnh nằm trong thư mục 'images', video trong thư mục 'videos'
            $post->imageurl = $post->imageurl ? asset($post->imageurl) : null;
            $post->videourl = $post->videourl ? asset($post->videourl) : null;
            return $post;
        });

        return response()->json($posts, 200);
    }

    // Tạo bài viết mới
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'video' => 'nullable|mimes:mp4,avi,mkv',
        ]);

        // Xử lý ảnh
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('images', 'public'); // Lưu vào thư mục 'storage/app/public/images'
        }

        // Xử lý video
        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('videos', 'public'); // Lưu vào thư mục 'storage/app/public/videos'
        }

        // Tạo bài viết
        $post = new Post();
        $post->content = $request->content;
        $post->user_id = $request->user_id;
        $post->imageurl = $imagePath;  // Lưu đường dẫn ảnh
        $post->videourl = $videoPath;  // Lưu đường dẫn video
        $post->status = 'active';
        $post->save();

        return response()->json(['message' => 'Bài viết đã được tạo'], 201);
    }
}
