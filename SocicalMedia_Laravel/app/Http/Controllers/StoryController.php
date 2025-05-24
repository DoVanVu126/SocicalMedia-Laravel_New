<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Story;
use App\Models\Follow;

class StoryController extends Controller
{
    public function index(Request $request)
{
    try {
        $userId = $request->query('user_id');

        $query = Story::with(['user' => function ($query) {
            $query->select('id', 'username', 'profilepicture');
        }])
        ->where('expires_at', '>', now())
        ->latest();

        if ($userId) {
            // Get IDs of users the logged-in user follows
            $followedUserIds = Follow::where('follower_id', $userId)
                ->pluck('followed_id')
                ->toArray();

            // Include the user's own stories
            $followedUserIds[] = $userId;

            // Filter stories by followed users and own stories
            $query->where(function ($q) use ($userId, $followedUserIds) {
                $q->whereIn('user_id', $followedUserIds)
                  ->where('visibility', 'public') // Only public stories for followed users
                  ->orWhere(function ($q) use ($userId) {
                      $q->where('user_id', $userId)
                        ->where('visibility', 'private'); // Private stories only for the creator
                  });
            });
        } else {
            // For non-logged-in users, return only public stories
            $query->where('visibility', 'public');
        }

        $stories = $query->get();

        return response()->json($stories, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Unable to fetch stories'], 500);
    }
}

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'content' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'video' => 'nullable|mimes:mp4,avi,mkv|max:10240',
            'visibility' => 'in:public,private',
        ]);

        $imagePath = $request->hasFile('image')
            ? basename($request->file('image')->store('story_images', 'public'))
            : null;

        $videoPath = $request->hasFile('video')
            ? basename($request->file('video')->store('story_videos', 'public'))
            : null;

        $story = Story::create([
            'user_id' => $request->user_id,
            'content' => $request->content,
            'imageurl' => $imagePath,
            'videourl' => $videoPath,
            'visibility' => $request->visibility ?? 'public',
            'expires_at' => Carbon::now()->addHours(24),
        ]);

        return response()->json(['message' => 'Story đã được tạo', 'story' => $story]);
    }

    public function update(Request $request, $id)
    {
        $story = Story::find($id);
        if (!$story) return response()->json(['message' => 'Story không tồn tại'], 404);

        $request->validate([
            'content' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'video' => 'nullable|mimes:mp4,avi,mkv|max:10240',
            'visibility' => 'in:public,private',
        ]);

        if ($request->hasFile('image')) {
            if ($story->imageurl) {
                Storage::disk('public')->delete('story_images/' . $story->imageurl);
            }
            $story->imageurl = basename($request->file('image')->store('story_images', 'public'));
        }

        if ($request->hasFile('video')) {
            if ($story->videourl) {
                Storage::disk('public')->delete('story_videos/' . $story->videourl);
            }
            $story->videourl = basename($request->file('video')->store('story_videos', 'public'));
        }

        $story->content = $request->input('content', $story->content);
        $story->visibility = $request->input('visibility', $story->visibility);
        $story->save();

        return response()->json(['message' => 'Đã cập nhật story', 'story' => $story]);
    }

    public function destroy($id)
    {
        $story = Story::find($id);
        if (!$story) return response()->json(['message' => 'Không tìm thấy story'], 404);

        if ($story->imageurl) Storage::disk('public')->delete('story_images/' . $story->imageurl);
        if ($story->videourl) Storage::disk('public')->delete('story_videos/' . $story->videourl);

        $story->delete();
        return response()->json(['message' => 'Đã xóa story']);
    }
}
