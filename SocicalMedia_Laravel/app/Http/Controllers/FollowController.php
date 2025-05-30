<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FollowController extends Controller
{
    public function follow(Request $request)
    {
        $request->validate([
            'follower_id' => 'required|exists:users,id',
            'followed_id' => 'required|exists:users,id',
        ]);

        $followerId = $request->follower_id;
        $followedId = $request->followed_id;

        if ($followerId == $followedId) {
            return response()->json(['message' => 'Không thể tự follow chính mình'], 400);
        }

        $exists = Follow::where('follower_id', $followerId)
            ->where('followed_id', $followedId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Đã follow rồi'], 400);
        }

        Follow::create([
            'follower_id' => $followerId,
            'followed_id' => $followedId,
        ]);

        User::where('id', $followedId)->increment('followers_count');
        User::where('id', $followerId)->increment('following_count');

        $follower = User::find($followerId);
        Log::info("Follow notification for followed_id: {$followedId}, follower_id: {$followerId}");
        NotificationController::createNotification(
            $followedId,
            "{$follower->username} đã bắt đầu theo dõi bạn.",
            $followerId,
            'user'
        );

        return response()->json(['message' => 'Đã follow thành công']);
    }

    public function unfollow(Request $request)
    {
        $request->validate([
            'follower_id' => 'required|exists:users,id',
            'followed_id' => 'required|exists:users,id',
        ]);

        $followerId = $request->follower_id;
        $followedId = $request->followed_id;

        $deleted = Follow::where('follower_id', $followerId)
            ->where('followed_id', $followedId)
            ->delete();

        if ($deleted) {
            User::where('id', $followedId)->decrement('followers_count');
            User::where('id', $followerId)->decrement('following_count');
        }

        return response()->json(['message' => 'Đã unfollow']);
    }

    public function check(Request $request)
    {
        $request->validate([
            'follower_id' => 'required|exists:users,id',
            'followed_id' => 'required|exists:users,id',
        ]);

        $isFollowing = Follow::where('follower_id', $request->follower_id)
            ->where('followed_id', $request->followed_id)
            ->exists();

        return response()->json(['isFollowing' => $isFollowing]);
    }

    public function getFollowers($id)
    {
        $followers = Follow::where('followed_id', $id)
            ->with('follower:id,username')
            ->get()
            ->pluck('follower');

        return response()->json($followers);
    }

    public function getFollowing($id)
    {
        $followings = Follow::where('follower_id', $id)
            ->with('followed:id,username')
            ->get()
            ->pluck('followed');

        return response()->json($followings);
    }

    public function list(Request $request, $userId, $type)
    {
        $request->validate([
            'page' => 'integer|min:1',
            'size' => 'integer|min:1',
            'currentUserId' => 'nullable|exists:users,id',
        ]);

        $page = $request->query('page', 1);
        $size = $request->query('size', 6);
        $currentUserId = $request->query('currentUserId');

        if (!in_array($type, ['followers', 'following'])) {
            return response()->json(['message' => 'Loại không hợp lệ'], 400);
        }

        if ($type === 'followers') {
            $query = User::select('users.id', 'users.username')
                ->join('follows', 'users.id', '=', 'follows.follower_id')
                ->where('follows.followed_id', $userId);
        } else {
            $query = User::select('users.id', 'users.username')
                ->join('follows', 'users.id', '=', 'follows.followed_id')
                ->where('follows.follower_id', $userId);
        }

        $totalItems = $query->count();
        $totalPages = ceil($totalItems / $size);

        $items = $query->skip(($page - 1) * $size)
            ->take($size)
            ->get();

        if ($currentUserId) {
            $followingIds = Follow::where('follower_id', $currentUserId)
                ->whereIn('followed_id', $items->pluck('id'))
                ->pluck('followed_id')
                ->toArray();

            $items = $items->map(function ($user) use ($followingIds) {
                $user->isFollowing = in_array($user->id, $followingIds);
                return $user;
            });
        }

        return response()->json([
            'items' => $items,
            'totalPages' => $totalPages,
        ]);
    }
}
