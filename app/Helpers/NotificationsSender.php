<?php

namespace App\Helpers;

use App\Models\NotificationToken;
use Illuminate\Support\Facades\Log;

class NotificationsSender
{
    public static function send($userId, $title, $body, $click_action = null, $icon = null) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $SERVER_KEY = env('NOTIFICATION_SERVER_KEY');

        $notificationTokens = NotificationToken::where('user_id', $userId)->select('token')->get();
        $userClientTokens = [];

        foreach ($notificationTokens as $notificationToken) {
            $userClientTokens[] = $notificationToken->token;
        }

        $request_body = [
            'notification' => [
                'title' => $title,
                'body' => $body,
                'icon' => $icon ?? env('NOTIFICATION_ICON'),
                'click_action' => $click_action ?? null,
            ],
            'registration_ids' => $userClientTokens
        ];
        $fields = json_encode($request_body);

        $request_headers = [
            'Content-Type: application/json',
            'Authorization: key=' . $SERVER_KEY,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        curl_close($ch);
    }
}
