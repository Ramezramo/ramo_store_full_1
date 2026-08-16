<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';

    protected $fillable = [
        'product_id',
        'variation_id',
        'qty',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'product_id' => 'integer',
        'variation_id' => 'integer',
        'qty' => 'integer',
    ];
}
