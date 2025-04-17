<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        // Lấy tất cả thông báo và thông tin người dùng liên quan
        $notifications = Notification::with('user')->orderBy('created_at', 'desc')->get();

        if ($notifications->isEmpty()) {
            return response()->json(['message' => 'Không có thông báo nào'], 200);
        }

        return response()->json($notifications, 200); // Trả về mảng thông báo với thông tin người dùng
    }
}
