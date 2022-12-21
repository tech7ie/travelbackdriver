<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\Pivot;

class RouteOrder extends Model
{
    protected $fillable = [
        'user_id',
        'route_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'comment',
        'pickup_address',
        'drop_off_address',
        'adults',
        'childrens',
        'luggage',
        'payment_type',
        'amount',
        'currency',
        'route_date',
        'status',
        'vehicle_id',
        'driver_id'
    ];

    protected $with = [
        'driver',
        'vehicle',
        'route',
        'getCars',
        'places'
    ];

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    public function driver() : HasOne
    {
        return $this->hasOne(Driver::class, 'id', 'driver_id');
    }

    public function vehicle() : HasOne
    {
        return $this->hasOne(Vehicle::class, 'id', 'vehicle_id');
    }

    public function route() : HasOne
    {
        return $this->hasOne(Route::class, 'id', 'route_id');
    }

    public function getCars()
    {
        return $this->morphToMany(Car::class, 'cars_route_order')->withPivot(['car_id','count']);
    }

    public function places()
    {
        return $this->morphToMany(Place::class, 'places_route_order')->withPivot(['price','durations']);
    }
}
