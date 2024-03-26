<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\PlacesRouteOrders;
use App\Models\Route;
use App\Models\RouteOrderCar;
use DateTime;
use DB;
use Illuminate\Http\Request;
use App\Models\RouteOrder;
use App\Helpers\EmailHelper;
use App\Models\User;
use App\Jobs\SendEmail;
use App\Helpers\NotificationHelper;
use App\Models\UserDevice;

class BookingController extends Controller
{
    public function all(Request $request)
    {
        $whereCondition = [];

        if ($request->has('status')) {
            $whereCondition[] = ['status', $request->status];
        }

        if ($request->has('user_id')) {
            $whereCondition[] = ['user_id', $request->user_id];
        }

        return count($whereCondition) ? RouteOrder::where($whereCondition)->get()->sortBy('route_date') : RouteOrder::all()->sortBy('route_date')->groupBy('status');
    }

    public function get(Request $request)
    {
        return RouteOrder::with(['partner' => function ($query) {
            $query->select(['id', 'last_name', 'first_name']);
            $query->with('profile');
        }])->find($request->id);
    }

    public function updateStatus(Request $request): array
    {
        $result = [
            'status' => false,
            'orderRoute' => []
        ];

        $orderRoute = RouteOrder::find($request->id);
        $orderRouteData = [
            'status' => $request->status
        ];

        if ($request->has('user_id')) {
            $orderRouteData['user_id'] = $request->user_id;
        }

        $orderRoute->update($orderRouteData);

        if ($request->status == 'pending') {
            $orderRoute->update([
                'driver_id' => null,
                'vehicle_id' => null
            ]);
        }

        SendEmail::dispatch($orderRoute, 'sendEmailFromUpdateRoute');

        if ($orderRoute) {
            $result['status'] = true;
            $result['orderRoute'] = $orderRoute;
        }

        return $result;
    }

    public function updateRouteOrder(Request $request): array
    {
        $result = [
            'status' => false,
            'orderRoute' => []
        ];

        $orderRoute = RouteOrder::find($request->id);

        try {
            DB::beginTransaction();
            if (!empty($request->vehicle_id)) {
                $orderRoute->update([
                    'vehicle_id' => $request->vehicle_id,
                ]);
            }

            if (!empty($request->driver)) {
                $orderRoute->update([
                    'driver_id' => $request->driver['id'],
                ]);

                if ($request->driver['user_id']) {
                    $userDevice = UserDevice::where('user_id', $request->driver['user_id'])->first();

                    if ($userDevice) {
                        NotificationHelper::send($userDevice->token, 'Mytripline Driver', 'You have been chosen for the route!');
                    }
                }
            }

            if (!empty($request->status_job)) {
                $orderRoute->update([
                    'status_job' => $request->status_job,
                ]);
            }

            if (!empty($request->order)) {
                $orderRoute->update($request->order);
            }

            if ($orderRoute) {
                $result['status'] = true;
                $result['orderRoute'] = $orderRoute;
            }

            SendEmail::dispatch($orderRoute, 'sendEmailFromUpdateRoute');

            DB::commit();
            return $result;
        } catch ( \Throwable $e ) {
            DB::rollBack();
            return ['status' => 0, 'message' => $e->getMessage()];
        }
    }

    public function add(Request $request ) {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $data['user_id'] = 1;
            $userDevices = UserDevice::all();
            $routeOrder = new RouteOrder();
            $routeDate = new DateTime($data['route_date']);

            $routeOrder->user_id          = 1;
            $routeOrder->email            = $data['email'] ?? '';
            $routeOrder->first_name       = $data['first_name'] ?? '';
            $routeOrder->last_name        = $data['last_name'] ?? '';
            $routeOrder->phone            = $data['phone'] ?? '';
            $routeOrder->comment          = $data['comment'] ?? '';
            $routeOrder->pickup_address   = $data['pickup_address'] ?? '';
            $routeOrder->drop_off_address = $data['drop_off_address'] ?? '';
            $routeOrder->currency         = $data['currency'] ?? 'eur';
            $routeOrder->route_id         = $data['route_id'];
            $routeOrder->route_date       = $routeDate->format('Y-m-d H:i:s');
            $routeOrder->amount           = $data['amount'];
            $routeOrder->adults           = $data['adults'];
            $routeOrder->childrens        = 0;
            $routeOrder->luggage          = $data['luggage'];
            $routeOrder->payment_type     = $data['payment_type'];
            $routeOrder->save();

            $car = new RouteOrderCar();
            $car->route_id = $routeOrder->id;
            $car->car_id = $data['car_id'];
            $car->save();

            foreach ($userDevices as $userDevice) {
                NotificationHelper::send($userDevice->token, 'Mytripline Driver', 'New trip!');
            }
            DB::commit();

            return [ 'status' => 1 ];
        } catch ( \Throwable $e ) {
            DB::rollBack();
            return [ 'status' => 0, 'message' => $e->getMessage() ];
        }
    }
}
