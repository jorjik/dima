<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Folder extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'background_media_id',
        'sort',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'folder_id')->orderByDesc('created_at')->orderBy('sort');
    }

    public function backgroundMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'background_media_id');
    }
}

