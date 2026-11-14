<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Immutable financial events. The database also rejects UPDATE and DELETE. */
class ReferralCommissionAudit extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'commission_id', 'referral_id', 'order_id', 'actor_id', 'action',
        'old_status', 'new_status', 'old_amount', 'new_amount', 'reason',
        'metadata', 'created_at',
    ];

    protected $casts = [
        'old_amount' => 'decimal:2',
        'new_amount' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(static fn () => throw new \LogicException('Referral financial audit events cannot be changed.'));
        static::deleting(static fn () => throw new \LogicException('Referral financial audit events cannot be deleted.'));
    }

    public static function record(
        ReferralCommission $commission,
        string $action,
        array $before = [],
        ?int $actorId = null,
        ?string $reason = null,
        array $metadata = [],
    ): self {
        return static::create([
            'commission_id' => $commission->id,
            'referral_id' => $commission->referral_id,
            'order_id' => $commission->order_id,
            'actor_id' => $actorId,
            'action' => $action,
            'old_status' => $before['status'] ?? null,
            'new_status' => $commission->status,
            'old_amount' => $before['amount'] ?? null,
            'new_amount' => $commission->amount,
            'reason' => $reason,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
