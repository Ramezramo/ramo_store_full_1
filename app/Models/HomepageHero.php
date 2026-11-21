<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomepageHero extends Model
{
    public const SETTINGS_ID = 1;

    public $incrementing = false;
    protected $fillable = [
        'enabled', 'media_type',
        'headline', 'subtext', 'button_label', 'button_link',
        'headline_ar', 'subtext_ar', 'button_label_ar',
    ];
    protected $casts = ['enabled' => 'boolean'];

    public static function active(): ?self
    {
        $hero = static::where('enabled', true)->find(self::SETTINGS_ID);

        return $hero?->media_path && Storage::disk('public')->exists($hero->media_path) ? $hero : null;
    }

    public function mediaUrl(): string
    {
        return Storage::disk('public')->url($this->media_path);
    }

    public function posterUrl(): ?string
    {
        return $this->poster_path ? Storage::disk('public')->url($this->poster_path) : null;
    }
}
