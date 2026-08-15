<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Coupon extends Model
{
    use HasFactory;

    // Table name (matches your migration)
    protected $table = 'coupons';

    // Disable Laravel's default timestamps (using manual ones)
    public $timestamps = false;

    // Fillable fields
    protected $fillable = [
        'code',
        'vendor_id',
        'amount',
        'status',
        'discount_type',
        'date_created',
        'date_created_gmt',
        'date_modified',
        'date_modified_gmt',
        'date_expires',
        'date_expires_gmt',
        'usage_count',
        'individual_use',
        'usage_limit',
        'usage_limit_per_user',
        'limit_usage_to_x_items',
        'product_ids',
        'excluded_product_ids',
        'product_categories',
        'excluded_product_categories',
        'free_shipping',
        'exclude_sale_items',
        'minimum_amount',
        'maximum_amount',
        'email_restrictions',
        'used_by',
        'description',
        'meta_data',
    ];

    // Casts for proper data types
    protected $casts = [
        'vendor_id' => 'integer',
        'amount' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'date_created' => 'datetime',
        'date_created_gmt' => 'datetime',
        'date_modified' => 'datetime',
        'date_modified_gmt' => 'datetime',
        'date_expires' => 'datetime',
        'date_expires_gmt' => 'datetime',
        'usage_count' => 'integer',
        'usage_limit' => 'integer',
        'usage_limit_per_user' => 'integer',
        'limit_usage_to_x_items' => 'integer',
        'individual_use' => 'boolean',
        'free_shipping' => 'boolean',
        'exclude_sale_items' => 'boolean',
        'product_ids' => 'array',
        'excluded_product_ids' => 'array',
        'product_categories' => 'array',
        'excluded_product_categories' => 'array',
        'email_restrictions' => 'array',
        'used_by' => 'array',
        'meta_data' => 'array',
    ];

    // Accessors for GMT timestamps
    protected function dateExpires(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value)->setTimezone(config('app.timezone')) : null,
        );
    }

    protected function dateExpiresGmt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value) : null,
        );
    }

    // Scope for active coupons
    public function scopeActive($query)
    {
        return $query->where('status', 'publish')
                    ->where(function ($q) {
                        $q->whereNull('date_expires')
                          ->orWhere('date_expires', '>', now());
                    });
    }

    // Scope for expired coupons
    public function scopeExpired($query)
    {
        return $query->whereNotNull('date_expires')
                    ->where('date_expires', '<=', now());
    }

    // Check if coupon is valid
    public function isValid(): bool
    {
        return $this->status === 'publish' &&
               (!$this->date_expires || $this->date_expires->isFuture()) &&
               $this->usage_count < ($this->usage_limit ?? PHP_INT_MAX);
    }

    // Check if coupon can be used by specific user
    public function canBeUsedByUser($userId): bool
    {
        if (!$this->individual_use) {
            return true;
        }

        $userUsage = collect($this->used_by)->filter(fn($id) => $id == $userId)->count();
        $limit = $this->usage_limit_per_user ?? PHP_INT_MAX;

        return $userUsage < $limit;
    }

    // Increment usage count
    public function incrementUsage($userId = null): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        if ($userId && !$this->canBeUsedByUser($userId)) {
            return false;
        }

        $this->increment('usage_count');

        // Add user to used_by if individual use
        if ($userId && $this->individual_use) {
            $usedBy = collect($this->used_by);
            if (!$usedBy->contains($userId)) {
                $usedBy->push($userId);
                $this->used_by = $usedBy->toArray();
            }
        }

        return $this->save();
    }

    // Get discount amount for cart
    public function getDiscountAmount($cartTotal): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        switch ($this->discount_type) {
            case 'percent':
                return $cartTotal * ($this->amount / 100);
            case 'fixed_cart':
            case 'fixed_product':
                return $this->amount;
            default:
                return 0;
        }
    }
}