<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNote extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'user_id',
        'note',
        'customer_note',
        // '_links',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
        
    }
}
