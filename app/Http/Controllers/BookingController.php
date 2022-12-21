<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RouteOrder;

class BookingController extends Controller
{
    public function all(Request $request)
    {
        $result = [
            'offers' => [],
            'planned' => [],
            'finished' => []
        ];

        $routes = RouteOrder::all()->sortBy('route_date');

        foreach ($routes as $route) {
            if ($route->status == 'pending') {
                $result['offers'][] = $route;
            } elseif($route->status == 'planned' && $route->user_id == $request->user_id) {
                $result['planned'][] = $route;
            } elseif($route->status == 'complete' && $route->user_id == $request->user_id) {
                $result['finished'][] = $route;
            }
        }

        return $result;
    }

    public function get(Request $request)
    {
        return RouteOrder::find($request->id);
    }

    public function updateStatus(Request $request)
    {
        $result = [
            'status' => false,
            'orderRoute' => []
        ];

        $orderRoute = RouteOrder::find($request->id);

        $orderRoute->update([
            'user_id' => $request->user_id,
            'status' => $request->status
        ]);

        if ($request->status == 'pending') {
            $orderRoute->update([
                'driver_id' => null,
                'vehicle_id' => null
            ]);
        }

        if ($orderRoute) {
            $result['status'] = true;
            $result['orderRoute'] = $orderRoute;
        }

        return $result;
    }

    public function updateRouteOrder(Request $request)
    {
        $result = [
            'status' => false,
            'orderRoute' => []
        ];

        $orderRoute = RouteOrder::find($request->id);

        if (!empty($request->vehicle_id)) {
            $orderRoute->update([
                'vehicle_id' => $request->vehicle_id,
            ]);
        }

        if (!empty($request->driver_id)) {
            $orderRoute->update([
                'driver_id' => $request->driver_id,
            ]);
        }

        if ($orderRoute) {
            $result['status'] = true;
            $result['orderRoute'] = $orderRoute;
        }

        return $result;
    }
}
