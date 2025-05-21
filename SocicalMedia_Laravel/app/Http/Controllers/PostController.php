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

            $post->imageurl = $post->imageurl
                ? array_map(fn($img) => asset('storage/images/' . $img), explode(',', $post->imageurl))
                : [];

            $post->videourl = $post->videourl ? asset('storage/videos/' . $post->videourl) : null;


            $reactionCounts = $post->reactions->groupBy('type')->map->count();
            $post->reaction_summary = $reactionCounts;
            $post->user_reaction = $userId ? $post->reactions->firstWhere('user_id', $userId) : null;

            return $post;
        });

        return response()->json($posts, 200);
    }

    public function show($id)
    {
        $post = Post::with('reactions')->find($id);
        if (!$post) return response()->json(['message' => 'Không tìm thấy bài viết'], 404);

        return response()->json($post);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'visibility' => 'in:public,private',

            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:50120',
            'video' => 'nullable|mimes:mp4,avi,mkv|max:100240',
        ]);

        try {
            // Lưu ảnh (nhiều ảnh)
            $imagePaths = [];

            if ($request->hasFile('image')) {
                foreach ($request->file('image') as $img) {

                    $filename = Str::random(20) . '.' . $img->getClientOriginalExtension();
                    $img->storeAs('images', $filename, 'public');
                    $imagePaths[] = $filename;
                }
            }


            $imageString = implode(',', $imagePaths);

            // Lưu video
            $videoPath = null;
            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $videoName = Str::random(20) . '.' . $video->getClientOriginalExtension();
                $video->storeAs('videos', $videoName, 'public');
                $videoPath = $videoName;
            }
            $post = Post::create([
                'user_id' => $request->user_id,
                'content' => $request->content,
                'imageurl' => $imageString,
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
        $post = Post::findOrFail($id);
        $post->content = $request->input('content');

        $existingImages = $post->imageurl ? explode(',', $post->imageurl) : [];

        $newImages = [];
        if ($request->hasFile('image')) {
            $imageFiles = is_array($request->file('image')) ? $request->file('image') : [$request->file('image')];
            foreach ($imageFiles as $imgFile) {
                $filename = Str::random(20) . '.' . $imgFile->getClientOriginalExtension();
                $imgFile->storeAs('images', $filename, 'public');
                $newImages[] = $filename;
            }
        }

        $post->imageurl = implode(',', array_merge($existingImages, $newImages));
        if ($request->has('remove_video') && $request->remove_video == '1') {
            if ($post->videourl) {
                Storage::disk('public')->delete('videos/' . $post->videourl);
                $post->videourl = null;
            }
        }

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


        return response()->json(['message' => 'Bài viết đã được cập nhật']);

    }

    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Bài viết không tồn tại'], 404);
        }

        // Xóa ảnh
        if ($post->imageurl) {
            foreach (explode(',', $post->imageurl) as $img) {

                Storage::disk('public')->delete('images/' . $img);
            }
        }
        // Xóa các comment liên quan (nếu có)
        if (method_exists($post, 'comments')) {
            $post->comments()->delete();
        }


        // Xóa video
        if ($post->videourl) {
            Storage::disk('public')->delete('videos/' . $post->videourl);
        }

        // Xóa bài viết
        $post->delete();

        //xóa reaction
        Reaction::where('post_id', $id)->delete();


        return response()->json(['message' => 'Bài viết đã được xóa']);
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
}
