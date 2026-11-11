<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorApplication extends Model
{
    protected $guarded = ['*'];

    protected $hidden = ['personal_data', 'marketplace_draft', 'identity_document', 'rejection_reason'];

    protected $casts = [
        'personal_data' => 'encrypted:array',
        'marketplace_draft' => 'encrypted:array',
        'identity_document' => 'encrypted',
        'rejection_reason' => 'encrypted',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];
}
