<?php

namespace App\Http\Controllers\Dispatching;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use DB;
use Hash;

class ModeratorController extends Controller
{
    public function all(Request $request)
    {
        return User::where('role_id', '<', 3)->get();
    }

    public function one($id)
    {
        return User::find($id);
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'first_name' => 'max:255',
                'last_name'  => 'max:255',
                'email'      => 'email',
                'phone'      => 'max:255',
                //    'day_of_birth' => 'date',
            ]);

            $profileData = $request->validate([
                'first_name' => 'max:255',
                'last_name'  => 'max:255',
                'country'    => 'max:255',
            ]);


            User::find($request->user_id)->update($request->toArray());
            if ($profile = Profile::where('user_id', $request->user_id)->first()) {
                $profile->update($profileData);
            }

            DB::commit();
            return ['status' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['status' => false, 'error' => 'Fields are not filled in correctly'];
        }
    }

    public function add(Request $request)
    {
        try {
            DB::beginTransaction();
            //dd(json_decode($request->data));
            $data = json_decode($request->data);

            $userData = [
                'first_name'   => $data->first_name,
                'last_name'    => $data->last_name,
                'email'        => $data->email,
                'phone'        => $data->phone,
                'role_id'      => $data->role_id,
                'day_of_birth' => $data->day_of_birth,
            ];
            $userId               = User::max('id') + 1;
            $userData['password'] = Hash::make('Pass!'. rand(100, 900));
            $userData['id']       = $userId;

            $profileData = [
                'first_name'   => $data->first_name,
                'last_name'    => $data->last_name,
                'country'      => $data->country,
                'whatsapp'     => $data->whatsapp,
                'address'      => $data->address,
                'postal_code'  => $data->postal_code,
                //    'gender'     => 'required',
            ];
            $profileData['user_id'] = $userId;

            if (!empty($request->file('driverLicence'))) {
                $driverLicenceFile = FileHelper::upload(
                    $request->file('driverLicence'),
                    $userId,
                    'profiles',
                    null,
                    null,
                    null,
                    'driver-licence'
                );

                $profileData['driver_licence'] = $driverLicenceFile['filename'];
            }

            if (!empty($request->file('criminalCheck'))) {
                $criminalCheckFile = FileHelper::upload(
                    $request->file('criminalCheck'),
                    $userId,
                    'profiles',
                    null,
                    null,
                    null,
                    'criminal-check'
                );

                $profileData['criminal_check'] = $criminalCheckFile['filename'];
            }

            if (!empty($request->file('photo'))) {
                $photoFile = FileHelper::upload(
                    $request->file('photo'),
                    $userId,
                    'profiles',
                    null,
                    null,
                    null,
                    'photo'
                );

                $profileData['photo'] = $photoFile['filename'];
            }

            if (!empty($request->file('passport'))) {
                $passportFile = FileHelper::upload(
                    $request->file('passport'),
                    $userId,
                    'profiles',
                    null,
                    null,
                    null,
                    'passport'
                );

                $profileData['passport'] = $passportFile['filename'];
            }

            User::create($userData);
            Profile::create($profileData);

            DB::commit();
            return ['status' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['status' => false, 'error' => 'Fields are not filled in correctly'];
        }
    }
}
