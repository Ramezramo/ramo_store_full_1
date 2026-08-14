<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideosModel extends Model
{
    use HasFactory;

    /**
     * This legacy model has no approved mass-assignment write path.
     * Keep all attributes protected unless an explicit, reviewed path is added.
     *
     * @var array<int, string>
     */
    protected $guarded = ['*'];
}
