<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = [
        'header_title',
        'header_tagline',
        'header_background_path',
        'site_background_path',
        'home_hero_title',
        'home_hero_text',
        'home_hero_background_path',
    ];
}

