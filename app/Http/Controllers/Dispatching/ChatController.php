<?php

namespace App\Http\Controllers\Dispatching;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Message;

class ChatController extends Controller
{
    public function contacts(Request $request)
    {
        $userMessages = Message::where('user_id', '!=', 1)->get()->groupBy('user_id');
        $result = [];

        foreach ($userMessages as $userKey => $userMessage) {
            $user          = User::with(['profile' => function ($query) {
                $query->without('cities');
            }])->without('company')->find($userKey);
            $lastMessage   = Message::where('chat_id', $userMessage[0]->chat_id)->get()->pop(); //$userMessage->pop();
            $unReadMessage = 0;

            if (!$lastMessage->status) {
                foreach ($userMessage->reverse() as $message) {
                    if (!$message->status && $message->user_id !== $userKey) {
                        $unReadMessage++;
                    } else {
                        break;
                    }
                }
            }
            
            $result[] = [
                'user'           => $user,
                'lastMessage'    => $lastMessage,
                'unReadMessages' => $unReadMessage
            ];
        }

        usort($result, function($a, $b)
        {
            return $b['lastMessage']['created_at']->toArray()['timestamp'] - $a['lastMessage']['created_at']->toArray()['timestamp'];
        });
        
        return $result;
    }

    public function chat($chat_id, Request $request)
    {
        $messages = Message::where('chat_id', $chat_id)->get();

        $messages->map(function(Message $message) use ($request) {
            if ($message->status == 0 && $message->user_id !== $request->user_id) {
                $message->status = 1;
                $message->update($message->toArray());
            }
        });

        return $messages;
    }

    public function sendMessage($chat_id, Request $request)
    {
        try {
            Message::create([
                'chat_id' => $chat_id,
                'user_id' => $request->user_id,
                'subject' => '',
                'message' => $request->message,
                'status'  => 0,
            ]);

            return true;
        } catch (\Exception $exception) {
            dump($exception);
            return false;
        }
    }
}
