<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\CustomResetPassword;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
// use ;

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPassword($token, 'en'));
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // 'billing',//{first_name: , last_name: , company: , 
            //address_1: , address_2: , city: , state: , postcode: , country: , email: , 
            //phone: phoneNumber}
        'shipping',
        'description',
        'lastname',
        'firstname',
        'registered',
        'nicename',
        'name',
        'email',
        'password',
        'user_login',
        'avatar',
        'url',
        'user_nicename',
        'display_name',
        'phone',
        'first_name',
        'last_name',
        'address',
        'city',
        'state',
        'address_note',
        'latitude',
        'longitude',
        'registration_method', // Indicator: 'phone_otp' or 'email_password'
        'is_phone_verified',
        'provider',
        'provider_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
