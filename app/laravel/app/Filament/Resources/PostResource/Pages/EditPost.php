<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Models\MediaFile;
use App\Models\Post;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    /**
     * Automatically regenerate unique slug based on the current title.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $currentSlug = (string) ($this->record?->slug ?? '');
        if (filled($currentSlug)) {
            // Keep existing URLs stable when editing title/content.
            $data['slug'] = $currentSlug;
            return $data;
        }

        $title = trim((string) Arr::get($data, 'title', ''));
        $baseSlug = Str::slug($title);
        $ignoreId = $this->record?->id;
        $data['slug'] = $this->ensureUniqueSlug($baseSlug, $ignoreId);

        return $data;
    }

    private function ensureUniqueSlug(?string $baseSlug, ?int $ignoreId = null): string
    {
        $base = filled($baseSlug) ? $baseSlug : ('post-' . Str::random(8));
        $slug = $base;

        $i = 1;
        while (
            Post::query()
                ->where('slug', $slug)
                ->when(filled($ignoreId), fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $i++;
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    /**
     * Runs after the Post is updated.
     * Here we persist newly uploaded media.
     */
    protected function afterSave(): void
    {
        /** @var Post $post */
        $post = $this->record;

        $rawState = $this->form->getRawState();

        $photos = Arr::wrap($rawState['photos'] ?? []);
        $videos = Arr::wrap($rawState['videos'] ?? []);
        $audios = Arr::wrap($rawState['audios'] ?? []);

        $this->syncUploads($post, $photos, MediaFile::TYPE_IMAGE);
        $this->syncUploads($post, $videos, MediaFile::TYPE_VIDEO);
        $this->syncUploads($post, $audios, MediaFile::TYPE_AUDIO);

        // Ensure cover is set and always points to an image.
        if (filled($post->cover_media_id)) {
            $cover = $post->cover;
            if (! $cover || $cover->media_type !== MediaFile::TYPE_IMAGE || (int) $cover->post_id !== (int) $post->id) {
                $post->cover_media_id = null;
            }
        }

        if (blank($post->cover_media_id)) {
            $firstImage = $post->images()
                ->orderBy('created_at')
                ->orderBy('id')
                ->first();
            if ($firstImage) {
                $post->cover_media_id = $firstImage->id;
            }
        }

        $post->save();
    }

    /**
     * @param  array<int, string>  $paths
     */
    private function syncUploads(Post $post, array $paths, string $mediaType): void
    {
        if ($paths === []) {
            return;
        }

        $disk = Storage::disk('public');
        $baseSort = ((int) ($post->media()->max('sort') ?? -1)) + 1;

        foreach ($paths as $sort => $path) {
            $path = (string) $path;

            $fullPath = $disk->path($path);
            $mime = is_file($fullPath) ? (@mime_content_type($fullPath) ?: null) : null;
            $sizeBytes = is_file($fullPath) ? (@filesize($fullPath) ?: null) : null;

            $width = null;
            $height = null;

            if ($mediaType === MediaFile::TYPE_IMAGE && is_file($fullPath)) {
                $dims = @getimagesize($fullPath);
                if (is_array($dims)) {
                    $width = $dims[0] ?? null;
                    $height = $dims[1] ?? null;
                }
            }

            MediaFile::updateOrCreate(
                [
                    'post_id' => $post->id,
                    'path' => $path,
                ],
                [
                    'media_type' => $mediaType,
                    'original_name' => basename($path),
                    'mime' => $mime,
                    'size_bytes' => $sizeBytes,
                    'width' => $width,
                    'height' => $height,
                    'sort' => $baseSort + (int) $sort,
                ],
            );
        }
    }
}

