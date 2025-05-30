<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Story;
use Carbon\Carbon;

class StoryController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Vui lòng cung cấp ID người dùng'], 401);
        }

        $stories = Story::with('user')
            ->where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($stories, 200);
    }

    public function show(Request $request, $id)
    {
        $userId = $request->query('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Vui lòng cung cấp ID người dùng'], 401);
        }

        $story = Story::with('user')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->where('created_at', '>=', Carbon::now()->subHours(24))
            ->first();

        if (!$story) {
            return response()->json(['error' => 'Story không tồn tại hoặc đã bị xóa'], 404);
        }

        return response()->json($story, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000',
            'visibility' => 'required|in:public,private',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video' => 'nullable|mimes:mp4,avi,mkv|max:10240',
        ]);

        $story = new Story();
        $story->user_id = $validated['user_id'];
        $story->content = $validated['content'];
        $story->visibility = $validated['visibility'];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('story_images', 'public');
            $story->imageurl = basename($imagePath);
        }

        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('story_videos', 'public');
            $story->videourl = basename($videoPath);
        }

        $story->save();
        return response()->json(['message' => 'Story đã được tạo', 'story' => $story], 201);
    }

    public function update(Request $request, $id)
    {
        $story = Story::find($id);

        if (!$story) {
            return response()->json(['error' => 'Story không tồn tại hoặc đã bị xóa'], 404);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'content' => 'required|string|max:1000|not_regex:/^\s*$/',
            'visibility' => 'required|in:public,private',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'video' => 'nullable|mimes:mp4,avi,mkv|max:10240',
            'remove_video' => 'nullable|in:1',
        ]);

        if ($story->user_id != $validated['user_id']) {
            return response()->json(['error' => 'Bạn không có quyền chỉnh sửa story này'], 403);
        }

        // ❌ Không kiểm tra updated_at nữa

        $story->content = trim($validated['content']);
        $story->visibility = $validated['visibility'];

        // Cập nhật ảnh mới
        if ($request->hasFile('image')) {
            if ($story->imageurl) {
                Storage::disk('public')->delete('story_images/' . $story->imageurl);
            }
            $imagePath = $request->file('image')->store('story_images', 'public');
            $story->imageurl = basename($imagePath);
        }

        // Cập nhật hoặc xóa video
        if ($request->filled('remove_video') && $request->input('remove_video') === '1') {
            if ($story->videourl) {
                Storage::disk('public')->delete('story_videos/' . $story->videourl);
                $story->videourl = null;
            }
        } elseif ($request->hasFile('video')) {
            if ($story->videourl) {
                Storage::disk('public')->delete('story_videos/' . $story->videourl);
            }
            $videoPath = $request->file('video')->store('story_videos', 'public');
            $story->videourl = basename($videoPath);
        }

        $story->save();

        return response()->json([
            'message' => 'Story đã được cập nhật',
            'story' => $story,
        ], 200);
    }


    public function destroy(Request $request, $id)
    {
        $userId = $request->input('user_id');
        if (!$userId) {
            return response()->json(['error' => 'Vui lòng cung cấp ID người dùng'], 401);
        }

        $story = Story::find($id);
        if (!$story) {
            return response()->json(['error' => 'Story không tồn tại hoặc đã bị xóa'], 404);
        }

        if ($story->user_id != $userId) {
            return response()->json(['error' => 'Bạn không có quyền xóa story này'], 403);
        }

        if ($story->imageurl) {
            Storage::disk('public')->delete('story_images/' . $story->imageurl);
        }
        if ($story->videourl) {
            Storage::disk('public')->delete('story_videos/' . $story->videourl);
        }

        $story->delete();
        return response()->json(['message' => 'Story đã được xóa thành công'], 200);
    }
}
