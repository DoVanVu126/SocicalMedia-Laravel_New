<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Follow;
use App\Models\User;

class FollowController extends Controller
{
    public function follow(Request $request) {
        $followerId = $request->input('follower_id');
        $followedId = $request->input('followed_id');

        if ($followerId == $followedId) {
            return response()->json(['message' => 'Không thể tự follow chính mình'], 400);
        }

        $exists = Follow::where('follower_id', $followerId)
                        ->where('followed_id', $followedId)
                        ->exists();

        if ($exists) {
            return response()->json(['message' => 'Đã follow rồi'], 400);
        }

        // Tạo mối quan hệ follow
        Follow::create([
            'follower_id' => $followerId,
            'followed_id' => $followedId
        ]);

        // Tăng followers_count của người được follow
        User::where('id', $followedId)->increment('followers_count');
        // Tăng following_count của người follow
        User::where('id', $followerId)->increment('following_count');

        return response()->json(['message' => 'Đã follow thành công']);
    }

    public function unfollow(Request $request) {
        $followerId = $request->input('follower_id');
        $followedId = $request->input('followed_id');

        // Xóa mối quan hệ follow
        $deleted = Follow::where('follower_id', $followerId)
                        ->where('followed_id', $followedId)
                        ->delete();

        if ($deleted) {
            // Giảm followers_count của người được follow
            User::where('id', $followedId)->decrement('followers_count');
            // Giảm following_count của người follow
            User::where('id', $followerId)->decrement('following_count');
        }

        return response()->json(['message' => 'Đã unfollow']);
    }

    public function check(Request $request) {
        $followerId = $request->input('follower_id');
        $followedId = $request->input('followed_id');

        $isFollowing = Follow::where('follower_id', $followerId)
                             ->where('followed_id', $followedId)
                             ->exists();

        return response()->json(['isFollowing' => $isFollowing]);
    }

    // FollowController.php
public function getFollowers($id) {
    $followers = Follow::where('followed_id', $id)
        ->with('follower') // assume bạn có quan hệ follower trong model Follow
        ->get()
        ->pluck('follower'); // chỉ lấy thông tin người theo dõi

    return response()->json($followers);
}

public function getFollowing($id) {
    $followings = Follow::where('follower_id', $id)
        ->with('followed') // assume bạn có quan hệ followed trong model Follow
        ->get()
        ->pluck('followed'); // chỉ lấy thông tin người đang theo dõi

    return response()->json($followings);
}

    public function list(Request $request, $userId, $type)
{
    $page = (int) $request->query('page', 1);
    $size = (int) $request->query('size', 6);
    $currentUserId = $request->query('currentUserId');

    if (!in_array($type, ['followers', 'following'])) {
        return response()->json(['message' => 'Loại không hợp lệ'], 400);
    }

    if ($type === 'followers') {
        // Lấy danh sách user follow $userId
        $query = User::select('users.id', 'users.username')
            ->join('follows', 'users.id', '=', 'follows.follower_id')
            ->where('follows.followed_id', $userId);
    } else {
        // Lấy danh sách user mà $userId đang follow
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
