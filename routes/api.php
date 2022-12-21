<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\VehiclesController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});


Route::post('/user/login', [UserController::class, 'login']);
Route::post('/user/auth', [UserController::class, 'auth']);
Route::post('/user/verify', [UserController::class, 'verify']);
Route::post('/user/check-email', [UserController::class, 'checkEmail']);
Route::post('/user/update-password', [UserController::class, 'updatePassword']);

Route::post('/profile/cities', [ProfileController::class, 'getCitiesByCountry']);
Route::post('/profile/countries', [ProfileController::class, 'getCountries']);

Route::post('/profile/update', [ProfileController::class, 'update']);

Route::post('/bookings/all', [BookingController::class, 'all']);
Route::post('/bookings/get', [BookingController::class, 'get']);
Route::post('/bookings/update-status', [BookingController::class, 'updateStatus']);
Route::post('/bookings/update-route-order', [BookingController::class, 'updateRouteOrder']);


Route::post('/vehicles/all', [VehiclesController::class, 'all']);
Route::post('/vehicles/add', [VehiclesController::class, 'add']);
Route::post('/vehicles/get', [VehiclesController::class, 'get']);
Route::post('/vehicles/update', [VehiclesController::class, 'update']);
Route::post('/vehicles/remove', [VehiclesController::class, 'remove']);
Route::post('/vehicles/body-types', [VehiclesController::class, 'getBodyTypes']);

Route::post('/drivers/all', [DriverController::class, 'all']);
Route::post('/drivers/add', [DriverController::class, 'add']);
Route::post('/drivers/get', [DriverController::class, 'get']);
Route::post('/drivers/update', [DriverController::class, 'update']);
Route::post('/drivers/remove', [DriverController::class, 'remove']);

Route::post('/invoice/get', [InvoiceController::class, 'get']);
