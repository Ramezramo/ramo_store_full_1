<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorApplicationReview extends Model
{
    protected $guarded = ['*'];
    protected $hidden = ['reason'];
    protected $casts = ['reason' => 'encrypted'];
}
