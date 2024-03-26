<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'english_lvl',
        'whatsapp',
        'country',
        'city',
        'passport',
        'driver_licence',
        'criminal_check',
        'photo',
        'address',
        'postal_code'
    ];

    protected $with = [
        'cities'
    ];

    public function cities() : HasManyThrough
    {
        //return $this->hasMany(City::class, 'country_id', 'country');
        return $this->hasManyThrough(City::class, Country::class, 'name', 'country_id', 'country', 'id');
    }
}
