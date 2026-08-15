<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RefundRequest extends Model
{
    use HasFactory;

    protected $table = 'refund_requests';

    protected $fillable = [
        'order_id',
        'customer_id',
        'vendor_id',
        'type',
        'reason',
        'description',
        'status',
        'admin_note',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'customer_id' => 'integer',
        'vendor_id' => 'integer',
    ];
}
