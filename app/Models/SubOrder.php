<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubOrder extends Model
{
    protected $table = 'order_sub_orders';

    protected $guarded = ['*'];

    protected $casts = [
        'id' => 'integer',
        'parent_order_id' => 'integer',
        'vendor_id' => 'integer',
        'customer_id' => 'integer',
        'line_items' => 'array',
        'timeline' => 'array',
        'subtotal' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];
}
