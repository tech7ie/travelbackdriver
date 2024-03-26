<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;

class NotificationHelper
{
    public static function send($to, $title, $content)
    {
        $response = Http::withOptions([
            'json' => [
                'to' => $to,
                'title' => $title,
                'content' => $content
            ]
        ])->post(env('APP_NOTIFICATION_API_URL', 'https://api.drivermytripline.com/api/notification/send'));

        return $response->body();
    }
}
