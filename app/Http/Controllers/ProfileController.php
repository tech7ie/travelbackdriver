<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Country;
use App\Models\Profile;
use App\Models\Company;
use App\Models\User;
use App\Helpers\FileHelper;

class ProfileController extends Controller
{
    const IMAGE_WIDTHS = [
        'photo' => [
            'height' => 320,
            'width'  => 300,
        ]
    ];

    public function update(Request $request)
    {
        $result = [
            'status' => false,
            'user' => [],
        ];

        $profile = json_decode($request->profile);

        if (!empty($request->phone)) {
            User::find($request->user_id)->update([
                'phone' => $request->phone
            ]);
        }
        

        $profile = Profile::where('user_id', $request->user_id)->update((array)$profile);

        if (!empty($request->file('passport'))) {
            $passportFile = FileHelper::upload(
                $request->file('passport'),
                $request->user_id,
                'profiles',
                null,
                null,
                null,
                'passport'
            );

            Profile::where('user_id', $request->user_id)->update([
                'passport' => $passportFile ? $passportFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('driverLicence'))) {
            $driverLicenceFile = FileHelper::upload(
                $request->file('driverLicence'),
                $request->user_id,
                'profiles',
                null,
                null,
                null,
                'driver-licence'
            );

            Profile::where('user_id', $request->user_id)->update([
                'driver_licence' => $driverLicenceFile ? $driverLicenceFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('criminalCheck'))) {
            $criminalCheckFile = FileHelper::upload(
                $request->file('criminalCheck'),
                $request->user_id,
                'profiles',
                null,
                null,
                null,
                'criminal-check'
            );

            Profile::where('user_id', $request->user_id)->update([
                'criminal_check' => $criminalCheckFile ? $criminalCheckFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('photo'))) {
            $photoFile = FileHelper::upload(
                $request->file('photo'),
                $request->user_id,
                'profiles',
                self::IMAGE_WIDTHS['photo']['width'],
                self::IMAGE_WIDTHS['photo']['height'],
                'webp',
                'avatar'
            );

            Profile::where('user_id', $request->user_id)->update([
                'photo' => $photoFile ? $photoFile['filename'] : '',
            ]);
        }

        if ($profile) {
            $user = User::where('id', $request->user_id)->first();
//            if($user->status == 'new') {
//                $user->update([
//                    'status' => 'checking'
//                ]);
//            }

            $result['status'] = true;
            $result['user'] = $user;
        }

        return $result;
    }

    public function getCitiesByCountry(Request $request)
    {
        ini_set('memory_limit', '-1');
        return City::where('country_id', $request->countryId)->get();
    }

    public function getCountries()
    {
        return Country::all();
    }

    public function updatePhoto(Request $request)
    {
        if (!empty($request->file('photo'))) {
            $filename = pathinfo($request->file('photo')->getClientOriginalName(), PATHINFO_FILENAME);
            $path = $request->user_id . '/' . $filename;
            $photoFile = FileHelper::upload($request->file('photo'), $path, 'profiles', 100, 100, 'webp');

            Profile::where('user_id', $request->user_id)->update([
                'photo' => $photoFile ? $photoFile['filename'] : '',
            ]);
        }
    }
}
