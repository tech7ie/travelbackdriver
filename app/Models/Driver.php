<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Driver extends Model
{
    protected $fillable = [
        'user_id',
        'partner_id',
        'first_name',
        'last_name',
        'phone',
        'email',
        'country_id',
        'city_id',
        'personal',
        'licence',
        'criminal_check',
        'state',
        'photo',
    ];

    protected $with = [
        'city',
        'country'
    ];

    public function city() : HasOne
    {
        return $this->hasOne(City::class, 'id', 'city_id');
    }

    public function country() : HasOne
    {
        return $this->hasOne(Country::class, 'id', 'country_id');
    }
}
