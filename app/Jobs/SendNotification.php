<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Helpers\EmailHelper;
use App\Helpers\NotificationHelper;
use App\Models\UserDevice;

class SendNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $routeOrder;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($routeOrder)
    {
        $this->routeOrder = $routeOrder;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $device = UserDevice::where('user_id', $this->routeOrder->user_id)->first();
        if (!empty($device)) {
            NotificationHelper::send($device->token, 'Mytripline Driver', 'Start job. Click here for details.');
        }
        //return $this->routeOrder;
    }
}
