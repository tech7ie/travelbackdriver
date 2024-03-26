<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\VehiclesController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\Dispatching\FleetsController;
use App\Http\Controllers\Dispatching\ModeratorController;
use App\Http\Controllers\Dispatching\ChatController;
use App\Http\Controllers\Dispatching\MetricController;
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
Route::post('/user/forgot-password', [UserController::class, 'forgotPassword']);
Route::post('/user/update-status', [UserController::class, 'updateStatus']);
Route::post('/user/update', [UserController::class, 'update']);
Route::post('/user/check-code', [UserController::class, 'checkCode']);
Route::post('/user/messages', [UserController::class, 'getMessages']);
Route::post('/user/messages/add', [UserController::class, 'addMessage' ] );
Route::post('/user/device/add', [UserController::class, 'addDevice' ] );
Route::post('/user/device/update', [UserController::class, 'updateDevice' ] );

Route::post('/profile/cities', [ProfileController::class, 'getCitiesByCountry']);
Route::post('/profile/countries', [ProfileController::class, 'getCountries']);

Route::post('/profile/update', [ProfileController::class, 'update']);
Route::post('/profile/update-photo', [ProfileController::class, 'updatePhoto']);

Route::post('/company/{id}', [CompanyController::class, 'update']);

Route::post('/bookings/all', [BookingController::class, 'all']);
Route::post('/bookings/get', [BookingController::class, 'get']);
Route::post('/bookings/update-status', [BookingController::class, 'updateStatus']);
Route::post('/bookings/update-route-order', [BookingController::class, 'updateRouteOrder']);
Route::middleware([\App\Http\Middleware\TrustDomain::class])->post('/bookings/add', [BookingController::class, 'add']);


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
Route::post('/invoice/{id}/info', [InvoiceController::class, 'info']);

Route::get('/check-redis', [InvoiceController::class, 'checkRedis']);

Route::post('/notification/send', [NotificationController::class, 'send']);

Route::get('/routes/search', [RouteController::class, 'search']);
Route::get('/city/search', [CityController::class, 'search']);

Route::middleware([\App\Http\Middleware\TrustDomain::class])->get('/customers', [UserController::class, 'getCustomers']);
Route::middleware([\App\Http\Middleware\TrustDomain::class])->get('/customers/{id}', [UserController::class, 'getOneCustomer']);
Route::middleware([\App\Http\Middleware\TrustDomain::class])->post('/customers/add', [UserController::class, 'addCustomer']);
Route::middleware([\App\Http\Middleware\TrustDomain::class])->post('/customers/update', [UserController::class, 'updateCustomer']);


// DISPATCHING

Route::middleware([\App\Http\Middleware\TrustDomain::class])->prefix('dispatching')->group(function () {
    Route::prefix('fleets')->group(function () {
        Route::get('/', [FleetsController::class, 'all']);
        Route::get('/{id}', [FleetsController::class, 'one']);
        Route::post('/update', [FleetsController::class, 'update']);
        Route::post('/add', [FleetsController::class, 'add']);
    });
    Route::prefix('moderators')->group(function () {
        Route::get('/', [ModeratorController::class, 'all']);
        Route::get('/{id}', [ModeratorController::class, 'one']);
        Route::post('/update', [ModeratorController::class, 'update']);
        Route::post('/add', [ModeratorController::class, 'add']);
    });
    Route::prefix('chat')->group(function () {
        Route::get('/contacts', [ChatController::class, 'contacts']);
        Route::get('/{chat_id}', [ChatController::class, 'chat']);
        Route::post('/{chat_id}/send-message', [ChatController::class, 'sendMessage']);
    });
    Route::prefix('metric')->group(function () {
        Route::get('/info', [MetricController::class, 'info']);
        Route::get('/revenue', [MetricController::class, 'revenue']);
        Route::get('/traffic', [MetricController::class, 'traffic']);
    });
    Route::prefix('partners')->group(function () {
        Route::get('/', [UserController::class, 'getPartners']);
    });
});
