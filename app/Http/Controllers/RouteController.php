<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Repositories\RouteRepository;

class RouteController extends Controller
{
    public function __construct(
        protected RouteRepository $routeRepository
    )
    {
    }

    public function search(Request $request)
    {
        $fromCity = $request->fromCity ?? '';
        $toCity = $request->toCity ?? '';

        return $this->routeRepository->find($fromCity, $toCity);
    }
}
