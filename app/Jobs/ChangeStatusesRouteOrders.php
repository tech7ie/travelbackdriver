<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\RouteOrder;

class ChangeStatusesRouteOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $routeOrderId;
    protected $status;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $routeOrderId, string $status)
    {
        $this->routeOrderId = $routeOrderId;
        $this->status = $status;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        RouteOrder::find($this->routeOrderId)->update([
            'status' => $this->status
        ]);
    }
}
