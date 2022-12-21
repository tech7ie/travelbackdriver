<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'represent_first_name',
        'represent_last_name',
        'represent_country',
        'represent_date_of',
        'address_country',
        'address_city',
        'address_address',
        'address_postal_code',
        'head_country',
        'head_city',
        'head_address',
        'head_postal_code',
        'billing_country',
        'billing_company',
        'vat',
        'iban',
        'licence',
        'certified',
    ];

    protected $with = [
        'citiesAddress',
        'citiesHead'
    ];

    public function citiesAddress() : HasManyThrough
    {
        return $this->hasManyThrough(City::class, Country::class, 'name', 'country_id', 'address_country', 'id');
    }

    public function citiesHead() : HasManyThrough
    {
        return $this->hasManyThrough(City::class, Country::class, 'name', 'country_id', 'head_country', 'id');
    }
}
