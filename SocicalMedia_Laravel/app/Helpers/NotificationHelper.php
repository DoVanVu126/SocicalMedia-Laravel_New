<?php

namespace App\Helpers;

use App\Models\Notification;

class NotificationHelper
{
    public static function sendNotification($userId, $content, $type = null, $notifiableId = null, $notifiableType = null)
    {
        Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'notification_content' => $content,
            'notifiable_id' => $notifiableId,
            'notifiable_type' => $notifiableType,
        ]);
    }
}
