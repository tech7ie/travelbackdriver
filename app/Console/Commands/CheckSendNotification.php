<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RouteOrder;
use DateTime;
use App\Jobs\SendNotification;
use App\Helpers\NotificationHelper;

class CheckSendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check-send-notification:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Проверка необходимости отправки пушей';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $routeOrders = RouteOrder::where('status', 'planned')->get();
        $now = new DateTime(); // 10:00:00
        $startDate = new DateTime(date('Y-m-d H:i:s', strtotime('now +12 hour'))); // 22:00:00 date('Y-m-d H:i:s', strtotime('now +12 hour'))
        $endDate = new DateTime(date('Y-m-d H:i:s', strtotime('now +13 hour'))); // +13h = 23:00:00 date('Y-m-d H:i:s', strtotime('now +13 hour'))

        $routeOrders = $routeOrders->filter(function ($route) use ($startDate, $endDate) {
            $routeDate = new DateTime($route->route_date);
            return $routeDate->getTimestamp() >= $startDate->getTimestamp() && $routeDate->getTimestamp() < $endDate->getTimestamp();
        });

        foreach ($routeOrders as $routeOrder) { // !!!Возможная погрешность в 5 мин
            $routeDate = new DateTime($routeOrder->route_date);
            $delay = $routeDate->getTimestamp() - $now->getTimestamp() - 43200;
            SendNotification::dispatch($routeOrder)->delay($delay);
        }
    }
}
