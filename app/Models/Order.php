<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'set_paid',
        'parent_id',
        'status',
        'currency',
        'version',
        'prices_include_tax',
        'parent_vendors_data',
        'parent_vendors_ids',
        'date_created',
        'date_modified',
        'date_completed',
        'date_paid',
        'discount_total',
        'discount_tax',
        'shipping_total',
        'shipping_tax',
        'cart_tax',
        'coupon_code',
        'coupon_applied',
        // 'discount_total',
        'original_total',
        'final_total',
        'total_tax',
        'customer_id',
        'order_key',
        'billing',
        'shipping',
        'payment_method',
        'payment_method_title',
        'transaction_id',
        'customer_ip_address',
        'customer_user_agent',
        'created_via',
        'customer_note',
        'cart_hash',
        'number',
        'meta_data',
        'line_items',
        'tax_lines',
        'shipping_lines',
        'fee_lines',
        'coupon_lines',
        'refunds',
        'payment_url',
        'is_editable',
        'needs_payment',
        'needs_processing',
        'date_created_gmt',
        'date_modified_gmt',
        'date_completed_gmt',
        'date_paid_gmt',
        'bacs_info',
        'currency_symbol',
        'timeline',
        '_links',
    ];

    protected $hidden = ['bacs_info', 'payment_url'];

    // ✅ THIS IS THE KEY FIX - CAST ALL JSON FIELDS TO ARRAYS
    protected $casts = [
        'set_paid' => 'boolean',
        'prices_include_tax' => 'boolean',
        'is_editable' => 'boolean',
        'needs_payment' => 'boolean',
        'needs_processing' => 'boolean',

        // ✅ JSON ARRAY FIELDS - THIS WAS MISSING!
        'billing' => 'array',
        'shipping' => 'array',
        'coupon_lines' => 'array',
        'coupon_data' => 'array',
        'meta_data' => 'array',
        'line_items' => 'array',
        'tax_lines' => 'array',
        'shipping_lines' => 'array',
        'fee_lines' => 'array',
        'coupon_lines' => 'array',
        'refunds' => 'array',
        'bacs_info' => 'array',
        'parent_vendors_data' => 'array',
        'timeline' => 'array',
        'parent_vendors_ids' => 'array',
        '_links' => 'array',

        // ✅ DATETIME FIELDS
        'date_created' => 'datetime',
        'date_modified' => 'datetime',
        'date_completed' => 'datetime',
        'date_paid' => 'datetime',
        'date_created_gmt' => 'datetime',
        'date_modified_gmt' => 'datetime',
        'date_completed_gmt' => 'datetime',
        'date_paid_gmt' => 'datetime',
    ];
}
