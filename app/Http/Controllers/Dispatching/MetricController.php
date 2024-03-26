<?php

namespace App\Http\Controllers\Dispatching;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RouteOrder;
use App\Models\User;
use Carbon\Carbon;

class MetricController extends Controller
{
    public function info()
    {
        $currentMonth = Carbon::now()->subMonth();
        $lastMonth    = Carbon::now()->subMonth(2);

        return [
            'orders'    => $this->getOrders($currentMonth, $lastMonth),
            'customers' => $this->getUsers($currentMonth, $lastMonth, 3),
            'partners'  => $this->getUsers($currentMonth, $lastMonth, 4),
            'drivers'   => $this->getUsers($currentMonth, $lastMonth, 5),
        ];
    }

    public function revenue(): array
    {
        $currentWeek = Carbon::now()->subWeek();
        $prevWeek    = Carbon::now()->subWeek(2);

        return [
            'currentWeek' => RouteOrder::where([['created_at', '>=', $currentWeek], ['status', 'complete']])->get(),
            'prevWeek'    => RouteOrder::where([['created_at', '<=', $currentWeek], ['created_at', '>=', $prevWeek], ['status', 'complete']])->get(),
        ];
    }

    public function traffic()
    {
        return [
            'country' => [
                'New York' => 90,
                'Moscow'   => 10,
                'London'   => 10
            ],
            'website' => [
                'Google' => 40,
                'Yandex' => 60
            ],
            'device'  => [
                'Linux'   => 10,
                'Mac'     => 40,
                'Windows' => 50
            ],
            'location'=> [
                'Russia' => 90,
                'Italia' => 10
            ],
        ];
    }

    private function getOrders($currentMonth, $lastMonth): array
    {
        $ordersCount            = RouteOrder::where('status', '!=', 'fail')->count();
        $orderCurrentMonthCount = RouteOrder::where([['created_at', '>=', $currentMonth], ['status', '!=', 'fail']])->count();
        $orderLastMonthCount    = RouteOrder::where([['created_at', '<=', $currentMonth], ['created_at', '>=', $lastMonth], ['status', '!=', 'fail']])->count();
        $coefficient            = $this->calculateCoefficient($orderCurrentMonthCount, $orderLastMonthCount);

        return [
            'count'       => $ordersCount,
            'coefficient' => $coefficient
        ];
    }

    private function getUsers($currentMonth, $lastMonth, $roleId): array
    {
        $usersCount             = User::where([['is_admin', 0], ['role_id', $roleId]])->count();
        $usersCurrentMonthCount = User::where([['created_at', '>=', $currentMonth], ['is_admin', 0], ['role_id', $roleId]])->count();
        $usersLastMonthCount    = User::where([['created_at', '<=', $currentMonth], ['created_at', '>=', $lastMonth], ['is_admin', 0], ['role_id', $roleId]])->count();
        $coefficient            = $this->calculateCoefficient($usersCurrentMonthCount, $usersLastMonthCount);

        return [
            'count'             => $usersCount,
            'coefficient'       => $coefficient
        ];
    }

    private function calculateCoefficient($firstNum, $lastNum): float|int
    {
        if ($firstNum == 0 && $lastNum == 0) {
            return 0;
        }

        if ($lastNum == 0) {
            return 100;
        }

        if ($firstNum == 0 && $lastNum > 0) {
            return -100;
        }

        return number_format(($firstNum / $lastNum - 1) * 100, 2,'.', '');
    }
}
