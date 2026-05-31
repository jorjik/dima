<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = [
        'header_title',
        'header_tagline',
        'header_background_path',
        'header_background_opacity',
        'site_background_path',
        'site_background_overlay_percent',
        'home_hero_title',
        'home_hero_text',
        'home_hero_background_path',
        'hero_background_opacity',
        'social_telegram',
        'social_vk',
        'social_instagram',
        'social_youtube',
        'social_whatsapp',
        'social_tiktok',
        'home_meta_title',
        'home_meta_description',
    ];

    protected function casts(): array
    {
        return [
            'site_background_overlay_percent' => 'integer',
        ];
    }

    public static function firstCached(): ?self
    {
        return Cache::remember('site_setting', 3600, function () {
            return static::query()->first();
        });
    }

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('site_setting');
        });

        static::deleted(function () {
            Cache::forget('site_setting');
        });
    }
}

