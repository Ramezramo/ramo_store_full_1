<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'phone',
        'otp_code',
        'expires_at',
        'attempts',
        'resend_count',
        'resend_window_start',
        'verified',
    ];

    protected $casts = [
        'expires_at'          => 'datetime',
        'resend_window_start' => 'datetime',
        'verified'            => 'boolean',
    ];

    public function isExpired(): bool
    {
        return now()->greaterThan($this->expires_at);
    }

    public function isExhausted(int $max): bool
    {
        return $this->attempts >= $max;
    }
}
