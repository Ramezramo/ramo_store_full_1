<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopMo extends Model
{
    use HasFactory;
    protected $table = 'shops';
    protected $fillable = [
        'user_id',
        'shop_name',
        'shop_address',
        'shop_logo',
        'status',
        'shop_banner',
        'secondary_banner',
    ];

    // علاقة belongsTo مع المستخدم
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
