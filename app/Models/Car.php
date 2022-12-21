<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    public function vehicle()
    {
        return $this->hasOne(VehicleBodyType::class, 'id', 'vehicle_body_type');
    }
}
