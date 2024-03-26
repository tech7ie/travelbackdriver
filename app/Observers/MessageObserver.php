<?php

namespace App\Observers;

use App\Models\Message;
use App\Helpers\EmailHelper;
use Log;

class MessageObserver
{

    public function __construct(Message $message)
    {
        Log::debug('An informational message.');
    }
    /**
     * Обрабатывать события после фиксирования всех транзакций.
     *
     * @var bool
     */
    public $afterCommit = true;

    /**
     * Handle the Message "created" event.
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    public function created(Message $message): void
    {
//        $socket = new \App\Helpers\Websocket();
//        $socket->onOpen();
//        $socket->send($message->message);

        //EmailHelper::sendEmailFromForgotPassword(['rrr' => $message->message], 'razrab345@gmail.com');
    }

    /**
     * Handle the Message "updated" event.
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    public function updated(Message $message)
    {
        //
    }

    /**
     * Handle the Message "deleted" event.
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    public function deleted(Message $message)
    {
        //
    }

    /**
     * Handle the Message "restored" event.
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    public function restored(Message $message)
    {
        //
    }

    /**
     * Handle the Message "force deleted" event.
     *
     * @param  \App\Models\Message  $message
     * @return void
     */
    public function forceDeleted(Message $message)
    {
        //
    }
}
