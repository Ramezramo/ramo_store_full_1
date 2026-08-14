<?php

namespace App\Models;

use App\Constants\AppConstants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class VendorUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'branch',
        'minimum_order_amount',
        'free_delivery_over_amount',
        'free_delivery_status',
        'minimum_order_amount_by_seller',
        'free_delivery_responsibility',
        'free_delivery_features_status',
        'temporary_close',
        'vacation_end_date',
        'vacation_start_date',
        'vacation_status',
        'offer_banner',
        'first_name',
        'last_name',
        'password',
        'phone',
        'email',
        'profile_image',
        'shop_name',
        'shop_address',
        'shop_logo',
        'shop_banner',
        'bottom_banner',
        'secondary_banner',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Add these to hide the raw paths
        'profile_image',
        'shop_logo',
        'shop_banner',
        'secondary_banner',
        'bottom_banner',
        'offer_banner',
        'password',
        'status',
        'bank_name',
        'auth_token',
        'account_no',
        'id',
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

    protected $appends = [
        'profile_image_url',
        'shop_logo_url',
        'shop_banner_url',
        'secondary_banner_url',
        'bottom_banner_url',
        'offer_banner_url',
    ];

    // Reusable helper using your AppConstants
    private function getFullImageUrl($path)
    {
        if (! $path || $path === 'empty' || $path === null || trim($path) === '') {
            return null;
        }

        return AppConstants::imageUrl($path);
    }

    // Profile Image Full URL
    public function getProfileImageUrlAttribute()
    {
        return $this->getFullImageUrl($this->profile_image);
    }

    public function getShopLogoUrlAttribute()
    {
        return $this->getFullImageUrl($this->shop_logo);
    }

    public function getShopBannerUrlAttribute()
    {
        return $this->getFullImageUrl($this->shop_banner);
    }

    public function getSecondaryBannerUrlAttribute()
    {
        return $this->getFullImageUrl($this->secondary_banner);
    }

    public function getBottomBannerUrlAttribute()
    {
        return $this->getFullImageUrl($this->bottom_banner);
    }

    public function getOfferBannerUrlAttribute()
    {
        return $this->getFullImageUrl($this->offer_banner);
    }
}
