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

        // Số bản ghi trên 1 trang, bạn có thể chỉnh lại theo nhu cầu
        $perPage = 5;

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
            ->paginate($perPage);  // <-- Thay get() bằng paginate()

        if ($posts->isEmpty()) {
            return response()->json(['message' => 'Không có bài viết nào'], 200);
        }

        // transform() với collection phân trang là items()
        $posts->getCollection()->transform(function ($post) use ($userId) {
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

        return response()->json([
            'posts' => $posts->items(),            // danh sách bài viết trên trang hiện tại
            'totalPosts' => $posts->total(),       // tổng số tất cả bài viết
            'currentPage' => $posts->currentPage(), // trang hiện tại (nếu muốn dùng)
            'perPage' => $posts->perPage(),         // số bài viết mỗi trang (nếu muốn dùng)
        ]);
    }


    public function show($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Không tìm thấy bài viết'], 404);
        }
        return response()->json($post);
    }
    public function getUserPosts(Request $request) // Đổi tên hàm
    {
        $userId = $request->query('user_id'); // Hoặc lấy từ route parameter nếu bạn muốn /api/users/{userId}/posts
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
            ->get(); // Không dùng paginate()

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
            'content' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
            'visibility' => 'in:public,private',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:50120',
            'video' => 'nullable|mimes:mp4,avi,mkv|max:100240',
        ]);
        try {
            // Lưu nhiều ảnh
            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePaths[] = basename($image->store('images', 'public'));
                }
            }
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
                'visibility' => $request->visibility,
                'status' => 'published',
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

    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);
        $post->content = $request->input('content');
        // Lấy danh sách ảnh cũ (nếu có)
        $existingImages = $post->imageurl ? explode(',', $post->imageurl) : [];
        $newImages = [];
        // ✅ Nếu có ảnh mới được upload => xóa toàn bộ ảnh cũ
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ khỏi thư mục
            foreach ($existingImages as $img) {
                Storage::disk('public')->delete('images/' . $img);
            }
            // Lưu ảnh mới
            $imageFiles = $request->file('image');
            $imageFiles = is_array($imageFiles) ? $imageFiles : [$imageFiles];
            foreach ($imageFiles as $imgFile) {
                $filename = Str::random(20) . '.' . $imgFile->getClientOriginalExtension();
                $imgFile->storeAs('images', $filename, 'public');
                $newImages[] = $filename;
            }
            // Chỉ lưu ảnh mới, không giữ lại ảnh cũ
            $post->imageurl = implode(',', $newImages);
        }
        // Nếu không chọn ảnh mới thì giữ nguyên ảnh cũ
        if (!$request->hasFile('image')) {
            $post->imageurl = implode(',', $existingImages);
        }
        // Xử lý video
        if ($request->hasFile('video')) {
            if ($post->videourl) {
                Storage::disk('public')->delete('videos/' . $post->videourl);
            }
            $video = $request->file('video');
            $videoName = Str::random(20) . '.' . $video->getClientOriginalExtension();
            $video->storeAs('videos', $videoName, 'public');
            $post->videourl = $videoName;
        }
        // Xóa video nếu được yêu cầu
        if ($request->has('remove_video') && $request->input('remove_video') == '1') {
            if ($post->videourl) {
                Storage::disk('public')->delete('videos/' . $post->videourl);
            }
            $post->videourl = null;
        }
        $post->save();
        return response()->json(['message' => 'Post updated successfully']);
    }

    public function destroy($id)
    {
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

        $summary = Reaction::where('post_id', $id)
            ->select('type', \DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $defaultSummary = ['like' => 0, 'love' => 0, 'haha' => 0, 'wow' => 0, 'sad' => 0, 'angry' => 0];
        $summary = array_merge($defaultSummary, $summary);

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

        $summary = Reaction::where('post_id', $id)
            ->select('type', \DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        $defaultSummary = ['like' => 0, 'love' => 0, 'haha' => 0, 'wow' => 0, 'sad' => 0, 'angry' => 0];
        $summary = array_merge($defaultSummary, $summary);

        $post = Post::find($id);
        $post->reaction_summary = json_encode($summary);
        $post->save();

        return response()->json([
            'message' => 'Đã xóa reaction',
            'user_reaction' => null,
            'reaction_summary' => $summary,
        ]);
    }

    public function sharePost(Request $request)
    {
        $post_id = $request->input('post_id');
        $user_id = $request->input('user_id');

        $post = Post::find($post_id);
        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

       $clone_post = $post->replicate();
       $clone_post->user_id = $user_id;
       $clone_post->parent_id = $post_id;
       $clone_post->save();

       return response()->json(['message' => 'Bài viết được chia sử dụng', 'post' => $clone_post], 200);
    }
}
