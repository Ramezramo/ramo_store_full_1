<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    use HasFactory;

    protected $table = 'app_config';

    protected $fillable = [
        'config_json',
    ];

    protected $casts = [
        'config_json' => 'array',
    ];
}
