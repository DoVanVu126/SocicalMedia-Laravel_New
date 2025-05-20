<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        $posts = Post::with(['user', 'reactions'])
            ->where(function ($query) use ($userId) {
                $query->where('visibility', 'public');

                if ($userId) {
                    $query->orWhere(function ($q) use ($userId) {
                        $q->where('visibility', 'private')->where('user_id', $userId);
                    });
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        if ($posts->isEmpty()) {
            return response()->json(['message' => 'Không có bài viết nào'], 200);
        }

        $posts->transform(function ($post) use ($userId) {
            if ($post->imageurl) {
                $post->imageurl = explode(',', $post->imageurl);
                $post->imageurl = array_map(fn($img) => asset($img), $post->imageurl);
            } else {
                $post->imageurl = [];
            }

            $post->videourl = $post->videourl ? asset(basename($post->videourl)) : null;

            $reactionCounts = $post->reactions->groupBy('type')->map->count();
            $post->reaction_summary = $reactionCounts;
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
            'visibility' => 'in:public,private',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'video' => 'nullable|mimes:mp4,avi,mkv|max:10240',
        ]);

        try {
            $imagePath = $request->hasFile('image')
                ? basename($request->file('image')->store('images', 'public'))
                : null;

            $videoPath = $request->hasFile('video')
                ? basename($request->file('video')->store('videos', 'public'))
                : null;

            $post = Post::create([
                'user_id' => $request->user_id,
                'content' => $request->content,
                'imageurl' => $imagePath,
                'videourl' => $videoPath,
                'status' => 'draft',
                'visibility' => $request->visibility ?? 'public',
            ]);

            return response()->json(['message' => 'Bài viết đã được tạo', 'post' => $post], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra khi tạo bài viết', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
            'visibility' => 'in:public,private',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'video' => 'nullable|mimes:mp4,avi,mkv|max:10240',
        ]);

        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        try {
            if ($request->hasFile('image')) {
                if ($post->imageurl) {
                    Storage::disk('public')->delete('images/' . $post->imageurl);
                }
                $post->imageurl = basename($request->file('image')->store('images', 'public'));
            }

            if ($request->hasFile('video')) {
                if ($post->videourl) {
                    Storage::disk('public')->delete('videos/' . $post->videourl);
                }
                $post->videourl = basename($request->file('video')->store('videos', 'public'));
            }

            $post->content = $request->content;
            if ($request->filled('visibility')) {
                $post->visibility = $request->visibility;
            }

            $post->save();

            return response()->json(['message' => 'Cập nhật bài viết thành công', 'post' => $post], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }
public function destroy($id)
{
    $post = Post::find($id);

            // Gộp thành chuỗi nếu cần (cho TEXT)
            $imageString = implode(',', $imagePaths); // lưu chuỗi: img1.jpg,img2.png,...

            // Lưu video (nếu có)
            $videoPath = $request->hasFile('video')
                ? basename($request->file('video')->store('videos', 'public'))
                : null;

            // Tạo bài viết
            $post = Post::create([
                'user_id' => $request->user_id,
                'content' => $request->content,
                'imageurl' => $imageString, // chỉ là TEXT
                'videourl' => $videoPath,
                'status' => 'publish',
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
        // Tìm bài post
        $post = Post::find($id);

        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }


        // Xóa tất cả bình luận của bài post này
        $post->comments()->delete();

        // Xóa ảnh và video nếu có
        if ($post->imageurl) {
            Storage::disk('public')->delete('images/' . $post->imageurl);
        }
        if ($post->videourl) {
            Storage::disk('public')->delete('videos/' . $post->videourl);
        }

        // Xóa bài post
        $post->delete();

        return response()->json(['message' => 'Bài viết và bình luận đã được xóa'], 200);
    }

    // PostController.php
    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) return response()->json(['message' => 'Not found'], 404);

        return response()->json($post);
    }

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $post->content = $request->input('content');

        // Lấy danh sách ảnh cũ (nếu có)
        $existingImages = $post->imageurl ? explode(',', $post->imageurl) : [];

        // Lưu ảnh mới nếu có
        $newImages = [];
        if ($request->hasFile('image')) {
            $imageFiles = $request->file('image');
            $imageFiles = is_array($imageFiles) ? $imageFiles : [$imageFiles];

            foreach ($imageFiles as $imgFile) {
                $filename = Str::random(20) . '.' . $imgFile->getClientOriginalExtension();
                $imgFile->storeAs('images', $filename, 'public');
                $newImages[] = $filename;
            }
        }

        // Gộp ảnh cũ + ảnh mới
        $post->imageurl = implode(',', array_merge($existingImages, $newImages));

        // Xử lý xóa video cũ nếu được yêu cầu
        if ($request->has('remove_video') && $request->remove_video == '1') {
            if ($post->videourl) {
                Storage::disk('public')->delete('videos/' . $post->videourl);
                $post->videourl = null;
            }
        }

        // Lưu video mới nếu có
        if ($request->hasFile('video')) {
            if ($post->videourl) {
                Storage::disk('public')->delete('videos/' . $post->videourl);
            }
            $video = $request->file('video');
            $videoName = Str::random(20) . '.' . $video->getClientOriginalExtension();
            $video->storeAs('videos', $videoName, 'public');
            $post->videourl = $videoName;
        }

        $post->save();
  
        return response()->json(['message' => 'Post updated successfully']);
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

        // Thêm hoặc cập nhật phản ứng
        $reaction = Reaction::updateOrCreate(
            ['post_id' => $id, 'user_id' => $request->user_id],
            ['type' => $request->type]
        );

        // Tóm tắt tổng số phản ứng theo loại
        $summary = Reaction::where('post_id', $id)
            ->select('type', \DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Đảm bảo tất cả các loại reaction có trong summary
        $defaultSummary = ['like' => 0, 'love' => 0, 'haha' => 0, 'wow' => 0, 'sad' => 0, 'angry' => 0];
        $summary = array_merge($defaultSummary, $summary);

        // Cập nhật reaction_summary trong bảng posts (tùy chọn, nếu muốn lưu trực tiếp)
        $post->reaction_summary = json_encode($summary);
        $post->save();

        return response()->json([
            'message' => 'Đã phản ứng bài viết',
            'user_reaction' => $reaction,
            'reaction_summary' => $summary,
        ]);
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

        // Lấy lại tổng kết sau khi xóa
        $summary = Reaction::where('post_id', $id)
            ->select('type', \DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        // Đảm bảo tất cả các loại reaction có trong summary
        $defaultSummary = ['like' => 0, 'love' => 0, 'haha' => 0, 'wow' => 0, 'sad' => 0, 'angry' => 0];
        $summary = array_merge($defaultSummary, $summary);

        // Cập nhật reaction_summary trong bảng posts (tùy chọn)
        $post = Post::find($id);
        $post->reaction_summary = json_encode($summary);
        $post->save();

        return response()->json([
            'message' => 'Đã xóa reaction',
            'user_reaction' => null,
            'reaction_summary' => $summary,
        ]);
    }
}