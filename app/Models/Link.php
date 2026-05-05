<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    use HasFactory;
    // protected $table = 'links_logs_two';
    protected $fillable = ['link', 'data','post_data'];
}
