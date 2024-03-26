<?php

namespace App\Http\Controllers;

use App\Helpers\FileHelper;
use Illuminate\Http\Request;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Models\VehicleBodyType;

class VehiclesController extends Controller
{
    const IMAGE_WIDTHS = [
        'photo' => [
            'height' => 320,
            'width'  => 300,
        ]
    ];

    public function all(Request $request)
    {
        return $request->has('partner_id') ? Vehicle::where('user_id', $request->user_id)->get() : Vehicle::all();
    }

    public function get(Request $request)
    {
        return Vehicle::find($request->id);
    }

    public function add(Request $request)
    {
        $result = [
            'status' => false,
            'vehicle' => [],
            'vehiclePhotos' => []
        ];

        $vehicle = json_decode($request->vehicle);

        $vehicleData = Vehicle::create([
            'user_id' => $request->user_id,
            'mark' => $vehicle->mark,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'licence' => $vehicle->licence,
            'color' => $vehicle->color,
            'class' => $vehicle->class,
            'state' => 'Pending time',
            'registration' => '',
            'inspection' => '',
            'green_card' => '',
        ]);

        $vehicleID = $vehicleData->id;

        if ($vehicleID) {
            if (!empty($request->file('registration'))) {
                $registrationFile = FileHelper::upload($request->file('registration'),
                    $vehicleID,
                    'vehicles',
                    null,
                    null,
                    null,
                    'registration'
                );

                Vehicle::find($vehicleData->id)->update([
                    'registration' => $registrationFile ? $registrationFile['filename'] : ''
                ]);
            }

            if (!empty($request->file('inspection'))) {
                $inspectionFile = FileHelper::upload(
                    $request->file('inspection'),
                    $vehicleID,
                    'vehicles',
                    null,
                    null,
                    null,
                    'inspection'
                );

                Vehicle::find($vehicleData->id)->update([
                    'inspection' => $inspectionFile ? $inspectionFile['filename'] : '',
                ]);
            }

            if (!empty($request->file('greenCard'))) {
                $greenCardFile = FileHelper::upload(
                    $request->file('greenCard'),
                    $vehicleID,
                    'vehicles',
                    null,
                    null,
                    null,
                    'greenCard'
                );

                Vehicle::find($vehicleData->id)->update([
                    'green_card' => $greenCardFile ? $greenCardFile['filename'] : '',
                ]);
            }
        }

        $vehiclePhotos = [];

        if ($request->file('photos')) {
            foreach ($request->file('photos') as $key => $photo) {
                $photoData = FileHelper::upload($photo,
                    $vehicleID,
                    'vehicles',
                    self::IMAGE_WIDTHS['photo']['width'],
                    self::IMAGE_WIDTHS['photo']['height'],
                    null,
                    'photo' . $key
                );

                if ($photoData['status']) {
                    VehiclePhoto::create([
                        'vehicle_id' => $vehicleData->id,
                        'photo' => $photoData['filename'],
                    ]);
                    $vehiclePhotos[] = $photoData['filename'];
                }
            }
        }

        if ($vehicle) {
            $result['status'] = true;
            $result['vehicleID'] = $vehicleID;
            $result['vehicle'] = $vehicleData;
            $result['vehiclePhotos'] = $vehiclePhotos;
            $result['message'] = 'Vehicle added successfully';
        } else {
            $result['message'] = 'Error when adding a vehicle';
        }

        return $result;
    }

    public function update(Request $request)
    {
        $result = [
            'status' => false,
            'vehicle' => [],
            'vehiclePhotos' => []
        ];

        $vehicle = json_decode($request->vehicle);

        $vehicleData = Vehicle::find($request->id)->update([
            'mark' => $vehicle->mark,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'licence' => $vehicle->licence,
            'color' => $vehicle->color,
            'class' => $vehicle->class,
            'state' => 'Pending time',
        ]);

        if (!empty($request->file('registration'))) {
            $registrationFile = FileHelper::upload(
                $request->file('registration'),
                $request->id,
                'vehicles',
                null,
                null,
                null,
                'registration'
            );

            Vehicle::find($request->id)->update([
                'registration' => $registrationFile ? $registrationFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('inspection'))) {
            $inspectionFile = FileHelper::upload(
                $request->file('inspection'),
                $request->id,
                'vehicles',
                null,
                null,
                null,
                'inspection'
            );

            Vehicle::find($request->id)->update([
                'inspection' => $inspectionFile ? $inspectionFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('greenCard'))) {
            $greenCardFile = FileHelper::upload(
                $request->file('greenCard'),
                $request->id,
                'vehicles',
                null,
                null,
                null,
                'greenCard'
            );

            Vehicle::find($request->id)->update([
                'green_card' => $greenCardFile ? $greenCardFile['filename'] : '',
            ]);
        }

        $vehiclePhotos = [];

        if ($request->file('photos')) {
            $photosExist = VehiclePhoto::where('vehicle_id', $request->id)->get();
            if ($photosExist->count()) {
                if (!array_key_exists('oldFiles', $request->photos)) {
                    VehiclePhoto::where('vehicle_id', $request->id)->delete();
                } else {
                    foreach ($photosExist as $photoExist) {
                        if (!in_array($photoExist->photo, $request->photos['oldFiles'])) {
                            VehiclePhoto::find($photoExist->id)->delete();
                        }
                    }
                }

                foreach ($request->photos['newFiles'] as $newFile) {
                    $photoData = FileHelper::upload($newFile, $request->id, 'vehicles');

                    if ($photoData['status']) {
                        VehiclePhoto::create([
                            'vehicle_id' => $request->id,
                            'photo' => $photoData['filename'],
                        ]);
                        $vehiclePhotos[] = $photoData['filename'];
                    }
                }

            }
        }

        if ($vehicle) {
            $result['status'] = true;
            $result['vehicle'] = $vehicleData;
            $result['vehiclePhotos'] = $vehiclePhotos;
        }

        return $result;
    }

    // TODO добавить удаление картинок VehiclePhoto

    public function remove(Request $request)
    {
        $result = [
            'status' => false,
            'vehicle' => [],
            'vehiclePhotos' => []
        ];

        $vehicle = Vehicle::find($request->id);

        if ($vehicle) {
            $result['vehicle'] = $vehicle;
            $vehicle->delete();
            $vehiclePhotos = VehiclePhoto::where('vehicle_id', $request->id);

            if ($vehiclePhotos) {
                $result['vehiclePhotos'] = $vehiclePhotos;

                foreach ($vehiclePhotos->get() as $vehiclePhoto) {
                    FileHelper::remove('/vehicles/' . $request->id . '/' . $vehiclePhoto);
                }

                $vehiclePhotos->delete();
            }

            $result['status'] = true;
        }

        return $result;
    }

    public function getBodyTypes()
    {
        return VehicleBodyType::all();
    }
}
