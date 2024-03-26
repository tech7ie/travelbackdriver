<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'price'
    ];

    protected $with = [
        'toCity',
        'toCountry',
        'fromCity',
        'fromCountry',
    ];


    public function toCity() : HasOne
    {
        return $this->hasOne(City::class,'id','route_to_city_id');
    }

    public function toCountry() : HasOne
    {
        return $this->hasOne(Country::class,'id','route_to_country_id');
    }

    public function fromCity() : HasOne
    {
        return $this->hasOne(City::class,'id','route_from_city_id');
    }

    public function fromCountry() : HasOne
    {
        return $this->hasOne(Country::class,'id','route_from_country_id');
    }
}
