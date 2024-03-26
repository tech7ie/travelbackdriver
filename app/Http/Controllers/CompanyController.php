<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\Country;
use App\Models\Profile;
use App\Models\Company;
use App\Models\User;
use App\Helpers\FileHelper;

class CompanyController extends Controller
{

//    public function update(Request $request)
//    {
//        $result = [
//            'status' => false,
//            'company' => [],
//        ];
//
//        $company = Company::where('user_id', $request->user_id);
//
//        return (array)json_decode($request->company);
//
//        $company->update((array)json_decode($request->company));
//
//        if (!empty($request->file('licence'))) {
//            $licenceFile = FileHelper::upload($request->file('licence'), $request->user_id, 'companies');
//
//            Company::where('user_id', $request->user_id)->update([
//                'licence' => $licenceFile ? $licenceFile['filename'] : '',
//            ]);
//        }
//
//        if (!empty($request->file('certified'))) {
//            $certifiedFile = FileHelper::upload($request->file('certified'), $request->user_id, 'companies');
//
//            Company::where('user_id', $request->user_id)->update([
//                'certified' => $certifiedFile ? $certifiedFile['filename'] : '',
//            ]);
//        }
//
//
//        $result['status'] = true;
//        $result['company'] = $company;
//
//
//        return $result;
//    }

    public function update(Request $request, Company $id)
    {
        $result = [
            'status' => false,
            'errors' => '',
            'company' => null,
        ];

        $company = $id->update((array)json_decode($request->company));

        if (!empty($request->file('licence'))) {
            $licenceFile = FileHelper::upload($request->file('licence'), $request->user_id, 'companies');

            $id->update([
                'licence' => $licenceFile ? $licenceFile['filename'] : '',
            ]);
        }

        if (!empty($request->file('certified'))) {
            $certifiedFile = FileHelper::upload($request->file('certified'), $request->user_id, 'companies');

            $id->update([
                'certified' => $certifiedFile ? $certifiedFile['filename'] : '',
            ]);
        }

        if ($company) {
            $result['status'] = true;
            $result['company'] = $id;
        }

        return $result;
    }
}
