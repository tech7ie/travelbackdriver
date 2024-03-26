<?php

namespace App\Listeners;

use App\Events\MessageEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Helpers\EmailHelper;

class AddMessageListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\MessageEvent  $event
     * @return void
     */
    public function handle(MessageEvent $event)
    {
        $event->message->message = 'ffffff1';
        $event->message->save();
        EmailHelper::sendEmailFromForgotPassword(['rrr' => $event->message], 'razrab345@gmail.com');
    }
}
