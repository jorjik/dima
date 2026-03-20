<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaFile extends Model
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';
    public const TYPE_AUDIO = 'audio';

    protected $table = 'media_files';

    protected $fillable = [
        'post_id',
        'media_type',
        'path',
        'original_name',
        'mime',
        'size_bytes',
        'width',
        'height',
        'sort',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}

