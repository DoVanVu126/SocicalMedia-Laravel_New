<?php

namespace App\Http\Controllers;

use App\Models\FriendRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FriendRequestController extends Controller
{
    public function getListFriend(Request $request)
    {
        $userId = $request->input('user_id');
        $friendIds = DB::table('friend_requests')
            ->where('status', 'accepted')
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->get()
            ->map(function ($item) use ($userId) {
                return $item->sender_id == $userId ? $item->receiver_id : $item->sender_id;
            });

        $friends = User::whereIn('id', $friendIds)->get();
        $data = returnMessage(1, $friends, 'Success');
        return response($data, 200);
    }

    public function getListRequest(Request $request)
    {
        $userId = $request->input('user_id');
        $pendingRequests = FriendRequest::where('friend_requests.sender_id', $userId)
            ->join('users', 'users.id', '=', 'friend_requests.receiver_id')
            ->where('friend_requests.status', 'pending')
            ->select('users.username', 'users.profilepicture', 'friend_requests.*')
            ->orderByDesc('friend_requests.created_at')
            ->get();

        $data = returnMessage(1, $pendingRequests, 'Success');
        return response($data, 200);
    }

    public function getListPending(Request $request)
    {
        $userId = $request->input('user_id');
        $pendingRequests = FriendRequest::where('friend_requests.receiver_id', $userId)
            ->join('users', 'users.id', '=', 'friend_requests.sender_id')
            ->where('friend_requests.status', 'pending')
            ->select('users.username', 'users.profilepicture', 'friend_requests.*')
            ->orderByDesc('friend_requests.created_at')
            ->get();

        $data = returnMessage(1, $pendingRequests, 'Success');
        return response($data, 200);
    }

    public function getAllNoFriend(Request $request)
    {
        $userId = $request->input('user_id');
        $relatedUserIds = FriendRequest::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->get()
            ->flatMap(function ($item) use ($userId) {
                return [$item->sender_id, $item->receiver_id];
            })
            ->unique()
            ->filter(fn($id) => $id != $userId)
            ->values()
            ->all();

        $users = User::where('id', '!=', $userId)
            ->whereNotIn('id', $relatedUserIds)
            ->get();

        $data = returnMessage(1, $users, 'Success');
        return response($data, 200);
    }

    public function store(Request $request)
    {
        try {
            $sender_id = $request->input('sender_id');
            $receiver_id = $request->input('receiver_id');

            $status = 'pending';

            $friendRequest = new FriendRequest();
            $friendRequest->sender_id = $sender_id;
            $friendRequest->receiver_id = $receiver_id;
            $friendRequest->status = $status;
            $friendRequest->save();

            $data = returnMessage(1, $friendRequest, 'Success');
            return response($data, 200);
        } catch (\Exception $ex) {
            $data = returnMessage(-1, '', $ex->getMessage());
            return response($data, 400);
        }
    }

    public function accept(Request $request)
    {
        try {
            $id = $request->input('id');
            $status = 'accepted';
            $friendRequest = FriendRequest::find($id);
            if ($friendRequest) {
                $friendRequest->status = $status;
                $friendRequest->save();
            }
            $data = returnMessage(1, $friendRequest, 'Success');
            return response($data, 200);
        } catch (\Exception $ex) {
            $data = returnMessage(-1, '', $ex->getMessage());
            return response($data, 400);
        }
    }

    public function reject(Request $request)
    {
        try {
            $id = $request->input('id');
            $friendRequest = FriendRequest::find($id);
            $friendRequest?->delete();
            $data = returnMessage(1, '', 'Success');
            return response($data, 200);
        } catch (\Exception $ex) {
            $data = returnMessage(-1, '', $ex->getMessage());
            return response($data, 400);
        }
    }
}
