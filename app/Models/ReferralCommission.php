<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_id',
        'order_id',
        'amount',
        'status',
        'approved_at',
        'paid_at',
        'clawed_back_at',
        'clawback_reason',
    ];

    protected $casts = [
        'referral_id' => 'integer',
        'order_id' => 'integer',
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'clawed_back_at' => 'datetime',
    ];

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
