<?php

namespace App\Http\Controllers\Dispatching;

use App\Helpers\FileHelper;
use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use DB;

class FleetsController extends Controller
{
    public function all(Request $request)
    {
        if ($request->has('role_id')) {
            return $request->has('count_items') && $request->has('current_page') ?
                User::with(['profile' => function ($query) {
                    $query->without('cities');
                }])->without('company')->where('role_id', $request->role_id)->paginate($request->count_items, ['*'], 'page', $request->current_page)
                : User::with(['profile' => function ($query) {
                    $query->without('cities');
                }])->without('company')->where('role_id', $request->role_id)->get();
        }

        return $request->has('count_items') && $request->has('current_page') ?
            User::with(['profile' => function ($query) {
                $query->without('cities');
            }])->without('company')->whereIn('role_id', [4, 5, 6])->paginate($request->count_items, ['*'], 'page', $request->current_page)
            : User::with(['profile' => function ($query) {
                $query->without('cities');
            }])->without('company')->whereIn('role_id', [4, 5, 6])->get();
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
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function add(Request $request)
    {
        try {
            $user    = json_decode($request->user);
            $profile = json_decode($request->profile);
            $company = json_decode($request->company);

            $dayOfBirthUserDate          = new DateTime($user->day_of_birth);
            $dayOfBirthCompanyDate       = new DateTime($company->represent_day_of_birth);
            $representCountryCompanyDate = new DateTime($company->represent_country);

            DB::beginTransaction();

            $userData = [
                'first_name'   => $user->first_name,
                'last_name'    => $user->last_name,
                'email'        => $user->email,
                'phone'        => $user->phone,
                'day_of_birth' => $dayOfBirthUserDate->format('Y-m-d H:i:s'),
            ];
            $userId               = User::max('id') + 1;
            $userData['password'] = Hash::make('Pass!'. rand(100, 900));
            $userData['role_id']  = 3;
            $userData['id']       = $userId;

            $profileData = [
                'first_name'  => $profile->first_name,
                'last_name'   => $profile->last_name,
                'country'     => $profile->country,
                'city'        => $profile->city,
                'address'     => $profile->address,
                'whatsapp'    => $profile->whatsapp,
                'postal_code' => $profile->postal_code
                //    'gender'     => 'required',
            ];
            $profileData['user_id'] = $userId;

            $companyData = [
                'user_id'                => $userId,
                'name'                   => $company->name,
                'email'                  => $company->email,
                'represent_first_name'   => $company->represent_first_name,
                'represent_last_name'    => $company->represent_last_name,
                'represent_day_of_birth' => $dayOfBirthCompanyDate->format('Y-m-d H:i:s'),
                'represent_country'      => $representCountryCompanyDate->format('Y-m-d H:i:s'),
                'address_country'        => $company->address_country,
                'address_city'           => $company->address_city,
                'address_address'        => $company->address_address,
                'address_postal_code'    => $company->address_postal_code,
                'head_country'           => $company->head_country,
                'head_city'              => $company->head_city,
                'head_address'           => $company->head_address,
                'head_postal_code'       => $company->head_postal_code,
                'billing_company'        => $company->billing_company,
                'billing_country'        => $company->billing_country,
                'vat'                    => $company->vat,
                'iban'                   => $company->iban,
            ];

            if (!empty($request->file('driver_licence'))) {
                $driverLicenceFile = FileHelper::upload(
                    $request->file('driver_licence'),
                    $userId,
                    'profiles',
                    null,
                    null,
                    null,
                    'driver-licence'
                );

                $profileData['driver_licence'] = $driverLicenceFile['filename'];
            }

            if (!empty($request->file('criminal_check'))) {
                $criminalCheckFile = FileHelper::upload(
                    $request->file('criminal_check'),
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

            if (!empty($request->file('licence'))) {
                $companyLicenceFile = FileHelper::upload(
                    $request->file('licence'),
                    $userId,
                    'companies',
                    null,
                    null,
                    null,
                    'licence'
                );

                $companyData['licence'] = $companyLicenceFile['filename'];
            }

            if (!empty($request->file('certified'))) {
                $companyCertifiedFile = FileHelper::upload(
                    $request->file('certified'),
                    $userId,
                    'companies',
                    null,
                    null,
                    null,
                    'certified'
                );

                $companyData['certified'] = $companyCertifiedFile['filename'];
            }

            User::create($userData);
            Profile::create($profileData);
            Company::create($companyData);

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            return false;
        }
    }
}
