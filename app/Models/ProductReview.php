<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductReview extends Model
{
    protected $table = 'product_reviews';

    protected $guarded = ['*'];

    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'user_id' => 'integer',
        'rating' => 'integer',
        'approved' => 'boolean',
        'is_verified_purchase' => 'boolean',
        'helpful_count' => 'integer',
    ];
}
