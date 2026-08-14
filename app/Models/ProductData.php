<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductData extends Model
{
    use HasFactory;
        // Specify the table name if it doesn't follow Laravel's naming convention
        protected $table = 'products_data';

        /**
         * This legacy data model has no approved mass-assignment write path.
         * Keep all attributes protected unless an explicit, reviewed path is added.
         *
         * @var array<int, string>
         */
        protected $guarded = ['*'];

}
