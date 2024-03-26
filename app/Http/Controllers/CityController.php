<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Repositories\CityRepository;

class CityController extends Controller
{
    public function __construct(
        protected CityRepository $cityRepository
    )
    {
    }

    public function search(Request $request)
    {
        return $this->cityRepository->find($request->city);
    }
}
