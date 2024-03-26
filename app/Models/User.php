<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'first_name',
        'last_name',
        'password',
        'email_verified_at',
        'status',
        'phone',
        'day_of_birth',
        'role_id',
        'gender',
    ];

    protected $with = [
        'profile',
        'company',
        'device'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    //    'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function profile()
    {
//        if ($this->role_id === 5) { // Если водитель, берем данные с drivers а не с profiles
//            return $this->hasOne(Driver::class, 'user_id', 'id');
//        }

        return $this->hasOne(Profile::class);
    }

//    public function driver()
//    {
//        return $this->hasOne(Driver::class, 'user_id', 'id');
//    }

    public function company() : HasOne
    {
        return $this->hasOne(Company::class);
    }

    public function device(): HasOne
    {
        return $this->hasOne(UserDevice::class);
    }

    public function getOrdersCount()
    {
        //dd(get_class_methods($this->hasMany(RouteOrder::class, 'user_id', 'id')));
        return $this->hasMany(RouteOrder::class, 'user_id', 'id')->count();
    }
}
