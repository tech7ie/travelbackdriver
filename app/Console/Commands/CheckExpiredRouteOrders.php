<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RouteOrder;
use DateTime;
use App\Jobs\ChangeStatusesRouteOrders;

class CheckExpiredRouteOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-expired-route-orders:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка просроченных route orders и простановка им статусов fail';


    public function handle()
    {
        $routeOrders = RouteOrder::where([['status', '!=', 'fail'], ['status_job', null]])->get(); //status_job???
        //$this->info($routeOrders);
        $now = new DateTime(); // 10:00:00

        foreach ($routeOrders as $routeOrder) {
            $routeDate = new DateTime($routeOrder->route_date);
            $delay = 0;
            $isExpired = false;

            if ($now->getTimestamp() > $routeDate->getTimestamp()) {
                $isExpired = true;
            } elseif($now->getTimestamp() + 3600 > $routeDate->getTimestamp()) {
                $delay = $routeDate->getTimestamp() - $now->getTimestamp();
                $isExpired = true;
            }

            if ($isExpired) {
                ChangeStatusesRouteOrders::dispatch($routeOrder->id, 'fail')->delay($delay);
            }
        }
    }
}
