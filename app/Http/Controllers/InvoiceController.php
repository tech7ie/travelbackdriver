<?php

namespace App\Http\Controllers;

use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use App\Models\RouteOrder;
use Illuminate\Support\Facades\Redis;
//use Redis;

class InvoiceController extends Controller
{
    public function get(Request $request)
    {
        $result = [
            'calendar' => [],
            'prices' => [],
            'orders' => [],
        ];

        $userID = $request->user_id;

        $routeOrders = RouteOrder::orderBy('route_date')->where('user_id', $userID)->get();

        if (!$routeOrders->count()) {
            return $result;
        }

        $firstDate = $routeOrders->first()->route_date;
        $lastDate = $routeOrders->last()->route_date;

        if ($request->has('calendar') && !$request->calendar) {
            $dateFrom = $firstDate;
            $dateTo   = $lastDate;
        } else {
            $result['calendar'] = $this->getCalendar($firstDate, $lastDate);

            $dateFrom = $result['calendar'][0]['from'];
            $dateTo   = $result['calendar'][0]['to'];

            if ($request->date_from && $request->date_to) {
                $dateFrom = $request->date_from;
                $dateTo   = $request->date_to;
            }
        }

        $result['orders'] = $this->getOrders($dateFrom, $dateTo, $userID);

        if (count($result['orders'])) {
            $result['prices'] = $this->getPrices($result['orders']);
        }

        return $result;
    }

    private function getOrders($dateFrom, $dateTo, $userID)
    {
        return RouteOrder::where([['user_id', $userID], ['route_date', '>=', $dateFrom . ' 00:00:00'], ['route_date', '<=', $dateTo . ' 23:59:59'], ['status', 'complete']])->get();
    }

    private function getPrices($orders): array
    {
        $prices = [
            'paid_online' => 0,
            'collected_in_cash' => 0,
            'total_earned' => 0,
            'to_be_paid' => 0,
        ];

        foreach ($orders as $order) {
            if ($order->payment_type == 1) {
                $prices['paid_online'] += $order->amount;
            }

            if ($order->payment_type == 2) {
                $prices['collected_in_cash'] += $order->amount;
            }

            if ($order->status == 'planned') {
                $prices['to_be_paid'] += $order->amount;
            }

            if ($order->status == 'complete') {
                $prices['total_earned'] += $order->amount;
            }
        }

        return $prices;
    }

    private function getCalendar($firstDate, $lastDate): array
    {
        $calendar = [];

        $period = new DatePeriod(
            new DateTime($firstDate),
            new DateInterval('P1M'),
            new DateTime($lastDate)
        );

        foreach ($period as $value) {
            $month = $value->format('m');
            $year = $value->format('Y');
            $daysImMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);

            $firstDay = 1;
            $middleDayDown = floor($daysImMonth / 2);
            $middleDayUp = ceil($daysImMonth / 2);
            $lastDay = $daysImMonth;

            $calendar[] = [
                'from' => $year . '-' . $month . '-' . $firstDay,
                'to' => $year . '-' . $month . '-' . $middleDayDown,
            ];

            $calendar[] = [
                'from' => $year . '-' . $month . '-' . $middleDayUp,
                'to' => $year . '-' . $month . '-' . $lastDay,
            ];
        }

        return array_reverse($calendar);
    }

    public function info($id)
    {
        return RouteOrder::find($id);
    }

    public function checkRedis()
    {
        $socket = stream_socket_server("https://127.0.0.1:6379", $errno, $errstr);

        return $socket;
        //dd(Redis::get('user:profile:1'));
    }
}
