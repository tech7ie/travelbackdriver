<?php

namespace App\Http\Controllers;

use App\Helpers\FileHelper;
use Illuminate\Http\Request;
use App\Models\Driver;

class DriverController extends Controller
{
    const IMAGE_WIDTHS = [
        'photo' => [
            'height' => 320,
            'width'  => 300,
        ]
    ];

    public function all(Request $request)
    {
        return $request->has('partner_id') ? Driver::where('partner_id', $request->partner_id)->get() : Driver::all();
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
            'partner_id' => (int) $request->partner_id,
            'user_id' => 0,
            'first_name' => $driver->first_name,
            'last_name' => $driver->last_name,
            'phone' => $driver->phone,
            'email' => $driver->email,
            'country_id' => $driver->country_id,
            'city_id' => $driver->city_id,
            'state'   => $driver->state ?? 'Pending time',
            'personal' => '',
            'licence' => '',
            'criminal_check' => '',
            'photo' => '',
        ]);

        $driverID = $driverData->id;

        if ($driverID) {
            if (!empty($request->file('licence'))) {
                $driverLicenceFile = FileHelper::upload(
                    $request->file('licence'),
                    $driverID,
                    'drivers',
                    null,
                    null,
                    null,
                    'licence'
                );
            }

            if (!empty($request->file('criminal_check'))) {
                $criminalCheckFile = FileHelper::upload(
                    $request->file('criminal_check'),
                    $driverID,
                    'drivers',
                    null,
                    null,
                    null,
                    'criminal-check'
                );
            }

            if (!empty($request->file('photo'))) {
                $photoFile = FileHelper::upload(
                    $request->file('photo'),
                    $driverID,
                    'drivers',
                    self::IMAGE_WIDTHS['photo']['width'],
                    self::IMAGE_WIDTHS['photo']['height'],
                    'webp',
                    'avatar'
                );
            }

            if (!empty($request->file('personal'))) {
                $personalFile = FileHelper::upload(
                    $request->file('personal'),
                    $driverID,
                    'drivers',
                    null,
                    null,
                    null,
                    'personal'
                );
            }

            Driver::find($driverData->id)->update([
                'personal' => !empty($personalFile) ? $personalFile['filename'] : '',
                'licence' => !empty($driverLicenceFile) ? $driverLicenceFile['filename'] : '',
                'criminal_check' => !empty($criminalCheckFile) ? $criminalCheckFile['filename'] : '',
                'photo' => !empty($photoFile) ? $photoFile['filename'] : '',
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

        $driverData = Driver::find($request->id);

        $driverData->update([
            'first_name' => $driver->first_name,
            'last_name' => $driver->last_name,
            'phone' => $driver->phone,
            'email' => $driver->email,
            'country_id' => $driver->country_id,
            'city_id' => $driver->city_id,
        ]);

        if (!empty($request->file('personal'))) {
            $personalFile = FileHelper::upload(
                $request->file('personal'),
                $request->id,
                'drivers',
                null,
                null,
                null,
                'personal'
            );

            Driver::find($request->id)->update([
                'personal' => $personalFile ? $personalFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('licence'))) {
            $licenceFile = FileHelper::upload(
                $request->file('licence'),
                $request->id,
                'drivers',
                null,
                null,
                null,
                'licence'
            );

            Driver::find($request->id)->update([
                'licence' => $licenceFile ? $licenceFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('criminalCheck'))) {
            $criminalCheckFile = FileHelper::upload(
                $request->file('criminal_check'),
                $request->id,
                'drivers',
                null,
                null,
                null,
                'criminal-check'
            );

            Driver::find($request->id)->update([
                'criminal_check' => $criminalCheckFile ? $criminalCheckFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('photo'))) {
            $photoFile = FileHelper::upload(
                $request->file('photo'),
                $request->id,
                'drivers',
                self::IMAGE_WIDTHS['photo']['width'],
                self::IMAGE_WIDTHS['photo']['height'],
                'webp',
                'avatar'
            );

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
