<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RouteOrderCar extends Model
{
    protected $fillable = [
        'route_id',
        'car_id',
    ];
}
