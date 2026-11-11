<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFlag extends Model
{
    protected $guarded = ['*'];

    protected $hidden = ['reason'];

    protected $casts = ['reason' => 'encrypted', 'resolved_at' => 'datetime'];
}
