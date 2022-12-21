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

    public function update(Request $request)
    {
        $result = [
            'status' => false,
            'user' => [],
        ];

        $profile = json_decode($request->profile);
        $company = json_decode($request->company);

        User::find($request->user_id)->update([
            'phone' => $profile->phone
        ]);

        $profile = Profile::where('user_id', $request->user_id)->update([
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'whatsapp' => $profile->whatsapp,
            'country' => $profile->country,
            'city' => $profile->city,
        ]);

        if (!empty($profile->english_lvl)) {
            Profile::where('user_id', $request->user_id)->update([
                'english_lvl' => $profile->english_lvl,
            ]);
        }

        if (!empty($request->file('passport'))) {
            $passportFile = FileHelper::upload($request->file('passport'), $request->user_id, 'profiles');

            Profile::where('user_id', $request->user_id)->update([
                'passport' => $passportFile ? $passportFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('driverLicence'))) {
            $driverLicenceFile = FileHelper::upload($request->file('driverLicence'), $request->user_id, 'profiles');

            Profile::where('user_id', $request->user_id)->update([
                'driver_licence' => $driverLicenceFile ? $driverLicenceFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('criminalCheck'))) {
            $criminalCheckFile = FileHelper::upload($request->file('criminalCheck'), $request->user_id, 'profiles');

            Profile::where('user_id', $request->user_id)->update([
                'criminal_check' => $criminalCheckFile ? $criminalCheckFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('photo'))) {
            $photoFile = FileHelper::upload($request->file('photo'), $request->user_id, 'profiles');

            Profile::where('user_id', $request->user_id)->update([
                'photo' => $photoFile ? $photoFile['filename'] : '',
            ]);
        }

        $company = Company::where('user_id', $request->user_id)->update([
            'name' => $company->name,
            'email' => $company->email,
            'represent_first_name' => $company->represent_first_name,
            'represent_last_name' => $company->represent_last_name,
            'represent_country' => $company->represent_country,
            'represent_date_of' => $company->represent_date_of,
            'address_country' => $company->address_country,
            'address_city' => $company->address_city,
            'address_address' => $company->address_address,
            'address_postal_code' => $company->address_postal_code,
            'head_country' => $company->head_country,
            'head_city' => $company->head_city,
            'head_address' => $company->head_address,
            'head_postal_code' => $company->head_postal_code,
            'billing_country' => $company->billing_country,
            'billing_company' => $company->billing_company,
            'vat' => $company->vat,
            'iban' => $company->iban,
        ]);

        if (!empty($request->file('licence'))) {
            $licenceFile = FileHelper::upload($request->file('licence'), $request->user_id, 'companies');

            Company::where('user_id', $request->user_id)->update([
                'licence' => $licenceFile ? $licenceFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('certified'))) {
            $certifiedFile = FileHelper::upload($request->file('certified'), $request->user_id, 'companies');

            Company::where('user_id', $request->user_id)->update([
                'certified' => $certifiedFile ? $certifiedFile['filename'] : '',
            ]);
        }

        if ($profile && $company) {
            $user = User::where('id', $request->user_id)->first();
            if($user->status == 'new') {
                $user->update([
                    'status' => 'checking'
                ]);
            }

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
}
