<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'mark',
        'model',
        'year',
        'licence',
        'color',
        'class',
        'state',
        'registration',
        'inspection',
        'green_card',
    ];

    protected $with = [
        'photos'
    ];

    public function photos() : HasMany
    {
        return $this->hasMany(VehiclePhoto::class);
    }
}
