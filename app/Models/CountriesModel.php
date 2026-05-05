<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CountriesModel extends Model
{
    use HasFactory;
    protected $table = 'countries';
    protected $fillable = [
        'code',
        'name'
    ];
}
