<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;



class NotificationController extends Controller
{
    public function index($userId)
    {
        $notifications = Notification::where('user_id', $userId)->latest()->get();
        return response()->json($notifications);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json(['message' => 'Đã đánh dấu đã đọc']);
    }

    public function markAllAsRead(Request $request)
    {
        $userId = $request->input('user_id');

        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Tất cả thông báo đã được đánh dấu đã đọc']);
    }

    public function toggleSettings(Request $request)
    {
        $user = User::findOrFail($request->input('user_id'));
        $user->update(['notifications_enabled' => $request->boolean('enabled')]);

        return response()->json(['message' => 'Cập nhật cài đặt thành công']);
    }

    public function destroy($id)
    {
        Notification::findOrFail($id)->delete();
        return response()->json(['message' => 'Thông báo đã được xóa']);
    }
}

