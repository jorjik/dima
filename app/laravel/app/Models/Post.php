<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    protected $fillable = [
        'folder_id',
        'title',
        'slug',
        'body_markdown',
        'cover_media_id',
        'sort',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(MediaFile::class, 'post_id')->orderBy('sort');
    }

    public function images(): HasMany
    {
        return $this->media()->where('media_type', MediaFile::TYPE_IMAGE)->orderBy('sort');
    }

    public function videos(): HasMany
    {
        return $this->media()->where('media_type', MediaFile::TYPE_VIDEO)->orderBy('sort');
    }

    public function audios(): HasMany
    {
        return $this->media()->where('media_type', MediaFile::TYPE_AUDIO)->orderBy('sort');
    }

    public function cover(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'cover_media_id');
    }
}

