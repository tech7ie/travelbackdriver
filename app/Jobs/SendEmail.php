<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\EmailHelper;
use App\Models\User;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bodyData;
    protected $emailType;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($bodyData, string $emailType)
    {
        $this->bodyData = $bodyData;
        $this->emailType = $emailType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $type = $this->emailType;
        $user = User::find($this->bodyData->user_id);
        $this->bodyData->user = $user;
        $toEmail = $user->email;

        EmailHelper::$type($this->bodyData, $toEmail);
    }
}
