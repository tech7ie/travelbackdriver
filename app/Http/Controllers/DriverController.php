<?php

namespace App\Http\Controllers;

use App\Helpers\FileHelper;
use Illuminate\Http\Request;
use App\Models\Driver;

class DriverController extends Controller
{
    public function all(Request $request)
    {
        return Driver::where('user_id', $request->user_id)->get();
    }

    public function get(Request $request)
    {
        return Driver::find($request->id);
    }

    public function add(Request $request)
    {
        $result = [
            'status' => false,
            'driver' => []
        ];

        $driver = json_decode($request->driver);

        $driverData = Driver::create([
            'user_id' => (int) $request->user_id,
            'first_name' => $driver->first_name,
            'last_name' => $driver->last_name,
            'phone' => $driver->phone,
            'email' => $driver->email,
            'country_id' => $driver->country_id,
            'city_id' => $driver->city_id,
            'personal' => '',
            'licence' => '',
            'criminal_check' => '',
            'photo' => '',
        ]);

        $driverID = $driverData->id;

        if ($driverID) {
            $personalFile = FileHelper::upload($request->file('personal'), $driverID, 'drivers');
            $licenceFile = FileHelper::upload($request->file('licence'), $driverID, 'drivers');
            $criminalCheckFile = FileHelper::upload($request->file('criminal_check'), $driverID, 'drivers');
            $photoFile = FileHelper::upload($request->file('photo'), $driverID, 'drivers');

            Driver::find($driverData->id)->update([
                'personal' => $personalFile ? $personalFile['filename'] : '',
                'licence' => $licenceFile ? $licenceFile['filename'] : '',
                'criminal_check' => $criminalCheckFile ? $criminalCheckFile['filename'] : '',
                'photo' => $photoFile ? $photoFile['filename'] : '',
            ]);
        }

        if ($driver) {
            $result['status'] = true;
            $result['driverID'] = $driverID;
            $result['driver'] = $driverData;
            $result['message'] = 'Driver added successfully';
        } else {
            $result['message'] = 'Error when adding a driver';
        }

        return $result;
    }

    public function update(Request $request)
    {
        $result = [
            'status' => false,
            'driver' => []
        ];

        $driver = json_decode($request->driver);

        $driverData = Driver::find($request->id)->update([
            'first_name' => $driver->first_name,
            'last_name' => $driver->last_name,
            'phone' => $driver->phone,
            'email' => $driver->email,
            'country_id' => $driver->country_id,
            'city_id' => $driver->city_id,
        ]);

        if (!empty($request->file('personal'))) {
            $personalFile = FileHelper::upload($request->file('personal'), $request->id, 'drivers');

            Driver::find($request->id)->update([
                'personal' => $personalFile ? $personalFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('licence'))) {
            $licenceFile = FileHelper::upload($request->file('licence'), $request->id, 'drivers');

            Driver::find($request->id)->update([
                'licence' => $licenceFile ? $licenceFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('criminalCheck'))) {
            $criminalCheckFile = FileHelper::upload($request->file('criminalCheck'), $request->id, 'drivers');

            Driver::find($request->id)->update([
                'criminal_check' => $criminalCheckFile ? $criminalCheckFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('photo'))) {
            $photoFile = FileHelper::upload($request->file('photo'), $request->id, 'drivers');

            Driver::find($request->id)->update([
                'photo' => $photoFile ? $photoFile['filename'] : '',
            ]);
        }

        if ($driver) {
            $result['status'] = true;
            $result['driver'] = $driverData;
        }

        return $result;
    }

    // TODO добавить удаление картинок VehiclePhoto

    public function remove(Request $request)
    {
        $result = [
            'status' => false,
            'driver' => [],
        ];

        $driver = Driver::find($request->id);

        if ($driver) {
            $result['driver'] = $driver;
            $driver->delete();

            $result['status'] = true;
        }

        return $result;
    }
}
