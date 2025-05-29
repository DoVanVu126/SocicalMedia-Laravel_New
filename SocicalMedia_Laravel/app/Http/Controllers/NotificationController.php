<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function index(Request $request, $userId)
    {
        if (!User::where('id', $userId)->exists()) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($notifications);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('id', $id)->first();
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $userId = $request->input('user_id');
        if ($userId && $notification->user_id != $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->is_read = 1;
        $notification->save();
        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        $userId = $request->input('user_id');
        if (!User::where('id', $userId)->exists()) {
            return response()->json(['message' => 'User not found'], 404);
        }

        Notification::where('user_id', $userId)
            ->where('is_read', 0)
            ->update(['is_read' => 1]);
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function destroy(Request $request, $id)
    {
        $notification = Notification::where('id', $id)->first();
        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $userId = $request->input('user_id');
        if ($userId && $notification->user_id != $userId) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $notification->delete();
        return response()->json(['message' => 'Notification deleted']);
    }

    public function toggleSettings(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'enabled' => 'required|boolean',
        ]);

        $user = User::find($request->user_id);
        $user->notifications_enabled = $request->enabled;
        $user->save();
        return response()->json(['message' => 'Notification settings updated']);
    }

    public static function createNotification($userId, $content, $notifiableId, $notifiableType = 'post')
    {
        Log::info("Creating notification for user_id: {$userId}, content: {$content}, notifiable_id: {$notifiableId}, notifiable_type: {$notifiableType}");
        return Notification::create([
            'user_id' => $userId,
            'notification_content' => $content,
            'notifiable_id' => $notifiableId,
            'notifiable_type' => $notifiableType,
            'is_read' => 0,
        ]);
    }

    public function getUnread(Request $request, $userId)
    {
        if (!User::where('id', $userId)->exists()) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $unreadCount = Notification::where('user_id', $userId)
            ->where('is_read', 0)
            ->count();
        return response()->json(['unread_count' => $unreadCount]);
    }
}
