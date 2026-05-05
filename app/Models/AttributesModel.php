<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributesModel extends Model
{
    use HasFactory;
    protected $table = 'attributes';
    protected $fillable = [
        'id',
        'name',
        'slug',
        'type',
        'order_by',
        'has_archives',
        'is_visible',
        '_links'
    ];
}
