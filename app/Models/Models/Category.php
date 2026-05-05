<?php

namespace App\Models\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories2';
    protected $fillable = [

        'name',
        'slug',
        'parent',
        'description',
        'display',
        'image',
        'menu_order',
        'count',
        'has_children',
        '_links'
    ];
}
