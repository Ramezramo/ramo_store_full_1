<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ImageGalleryImage extends Model
{
    protected $table = 'image_gallery_images';

    protected $fillable = [
        'path',
        'original_name',
        'mime_type',
        'file_size',
        'width',
        'height',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    /**
     * The absolute public URL administrators can reuse in content fields.
     */
    public function getUrlAttribute(): string
    {
        return url(Storage::disk('public')->url($this->path));
    }
}
