<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{
    /**
     * @throws \Exception
     */
    public function send(Request $request)
    {
        $body = [
            'to' => $request->to,
            'notification' => [
                'title' => $request->title,
                'body' => $request->content,
                'mutable_content' => true,
                'sound' => 'Tri-tone'
            ]
        ];

        $response = Http::withOptions([
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'key=' . env('APP_FIREBASE_SERVER_KEY', 'AAAAr8m6YVY:APA91bHiQMwc_e7bsu_E5BBUT2nI0zWODT4TEs_t57eY2bTKFwXy0DDpyXzrtdqPXaM_rNOXNiQ9i-6Ek3hB5bMGrHJTCKHuru25T1qrb_0qTtpSMQs53CCij2KlUSy4uEl828nPsmdU')
            ],
            'json' => $body,
        ])->post(env('APP_FIREBASE_URL', 'https://fcm.googleapis.com/fcm/send'));

        if ($response->getStatusCode() !== 200) {
            return throw new \Exception('error');
        }

        return json_decode($response)->success;
    }
}
