<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\Post;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('images:optimize {--dry-run : Show stats without changing files} {--force : Recompress even if within size limits}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $force = (bool) $this->option('force');

    $disk = Storage::disk('public');

    /** @var \Illuminate\Database\Eloquent\Collection<int, MediaFile> $mediaFiles */
    $mediaFiles = MediaFile::query()
        ->where('media_type', MediaFile::TYPE_IMAGE)
        ->get();

    $totalBefore = 0;
    $totalAfter = 0;
    $optimized = 0;
    $skipped = 0;
    $errors = 0;

    $this->info('Optimizing ' . $mediaFiles->count() . ' images...');
    $bar = $this->output->createProgressBar($mediaFiles->count());
    $bar->start();

    foreach ($mediaFiles as $mediaFile) {
        $fullPath = $disk->path($mediaFile->path);

        if (! is_file($fullPath)) {
            $skipped++;
            $bar->advance();
            continue;
        }

        $originalSize = filesize($fullPath);
        $totalBefore += $originalSize;

        if ($dryRun) {
            $totalAfter += $originalSize;
            $bar->advance();
            continue;
        }

        $needsOptimize = $force;
        if (! $needsOptimize) {
            try {
                $mime = @mime_content_type($fullPath);
                $needsOptimize = $mime && str_starts_with((string) $mime, 'image/');
            } catch (\Throwable) {
                $needsOptimize = false;
            }
        }

        if ($needsOptimize) {
            $saved = \App\Helpers\ImageHelper::resizeToMaxWidth($fullPath);

            if ($saved !== false) {
                clearstatcache(true, $fullPath);
                $newSize = filesize($fullPath);
                $totalAfter += $newSize;

                if ($newSize !== $originalSize) {
                    $mediaFile->update(['size_bytes' => $newSize]);
                    $optimized++;
                } else {
                    $skipped++;
                }
            } else {
                $totalAfter += $originalSize;
                $errors++;
            }
        } else {
            $totalAfter += $originalSize;
            $skipped++;
        }

        $bar->advance();
    }

    $bar->finish();
    $this->newLine(2);

    // Site settings images
    $siteSetting = \App\Models\SiteSetting::first();
    $siteSaved = 0;

    if ($siteSetting) {
        $bgFields = [
            'header_background_path',
            'home_hero_background_path',
            'site_background_path',
        ];

        foreach ($bgFields as $field) {
            $relPath = $siteSetting->{$field};
            if (empty($relPath)) continue;

            $fullPath = $disk->path($relPath);
            if (! is_file($fullPath)) continue;

            $before = filesize($fullPath);
            $totalBefore += $before;

            if (! $dryRun) {
                $saved = \App\Helpers\ImageHelper::resizeToMaxWidth($fullPath);
                clearstatcache(true, $fullPath);
                $after = filesize($fullPath);
                $totalAfter += $after;
                if ($after < $before) $siteSaved++;
            } else {
                $totalAfter += $before;
            }
        }
    }

    $savedBytes = $totalBefore - $totalAfter;
    $savedPercent = $totalBefore > 0 ? round(($savedBytes / $totalBefore) * 100, 1) : 0;

    $fmtFn = function (int $bytes): string {
        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    };

    $label = $dryRun ? '[DRY RUN] ' : '';
    $this->info("{$label}Done. Images: {$mediaFiles->count()} total, {$optimized} optimized, {$skipped} skipped, {$errors} errors");
    $this->info("{$label}Site backgrounds: {$siteSaved} optimized");
    $this->info("{$label}Size: " . $fmtFn($totalBefore) . " → " . $fmtFn($totalAfter) . " (saved {$savedPercent}%)");

    return self::SUCCESS;
})->purpose('Optimize all uploaded images with compression and size limits');

Artisan::command('demo:import {--force}', function () {
    $force = (bool) $this->option('force');

    $projectRoot = dirname(dirname(base_path()));
    $demoDir = $projectRoot . DIRECTORY_SEPARATOR . 'demo';

    if (! is_dir($demoDir)) {
        $this->error("Demo directory not found: {$demoDir}");
        return self::FAILURE;
    }

    $folderSlug = 'demo';

    /** @var Folder|null $folder */
    $folder = Folder::query()->where('slug', $folderSlug)->first();

    if ($folder && ! $force) {
        $this->line("Demo already imported (folder `{$folderSlug}` exists). Use --force to re-import.");
        return self::SUCCESS;
    }

    $counts = [
        'images' => 0,
        'videos' => 0,
        'audios' => 0,
    ];

    DB::transaction(function () use ($force, $demoDir, $folderSlug, &$folder, &$counts) {
        if ($folder && $force) {
            $posts = Post::query()->where('folder_id', $folder->id)->get();
            $postIds = $posts->pluck('id')->all();

            if ($postIds !== []) {
                MediaFile::query()->whereIn('post_id', $postIds)->delete();
            }

            Post::query()->where('folder_id', $folder->id)->delete();
            $folder->delete();
        }

        $folder = Folder::query()->create([
            'title' => 'Demo',
            'slug' => $folderSlug,
            'background_media_id' => null,
            'sort' => 0,
        ]);

        $files = File::allFiles($demoDir);

        $images = [];
        $videos = [];
        $audios = [];

        foreach ($files as $file) {
            $ext = strtolower($file->getExtension());
            $name = $file->getFilename();
            $fullPath = $file->getPathname();

            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true);
            $isVideo = in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'mkv'], true);
            $isAudio = in_array($ext, ['mp3', 'wav', 'm4a', 'ogg', 'flac', 'aac'], true);

            if ($isImage) {
                $images[] = ['name' => $name, 'path' => $fullPath];
            } elseif ($isVideo) {
                $videos[] = ['name' => $name, 'path' => $fullPath];
            } elseif ($isAudio) {
                $audios[] = ['name' => $name, 'path' => $fullPath];
            }
        }

        // Deterministic ordering for cover selection.
        usort($images, fn ($a, $b) => strcmp($a['name'], $b['name']));
        usort($videos, fn ($a, $b) => strcmp($a['name'], $b['name']));
        usort($audios, fn ($a, $b) => strcmp($a['name'], $b['name']));

        $counts['images'] = count($images);
        $counts['videos'] = count($videos);
        $counts['audios'] = count($audios);

        $post = Post::query()->create([
            'folder_id' => $folder->id,
            'title' => 'Рыжая собака — демо',
            'slug' => 'demo-post',
            'body_markdown' => "## Демо-контент\n\nЭто импортированные файлы из папки `demo`.\n\n* Видео отображается в конце поста\n* Аудио отображается в конце поста\n* Обложка и фон берутся только из фото\n",
            'cover_media_id' => null,
            'sort' => 0,
        ]);

        $disk = Storage::disk('public');

        $photoMediaIds = [];
        foreach ($images as $idx => $img) {
            $destPath = 'demo/import/photos/' . $idx . '_' . $img['name'];

            // Copy only if not exists yet.
            if (! $disk->exists($destPath)) {
                $disk->put($destPath, File::get($img['path']));
            }

            $mime = null;
            $sizeBytes = null;
            $width = null;
            $height = null;

            $tmpLocalPath = $img['path'];
            $mime = @mime_content_type($tmpLocalPath) ?: null;
            $sizeBytes = @filesize($tmpLocalPath) ?: null;
            $dims = @getimagesize($tmpLocalPath);
            if (is_array($dims)) {
                $width = $dims[0] ?? null;
                $height = $dims[1] ?? null;
            }

            $media = MediaFile::query()->create([
                'post_id' => $post->id,
                'media_type' => MediaFile::TYPE_IMAGE,
                'path' => $destPath,
                'original_name' => $img['name'],
                'mime' => $mime,
                'size_bytes' => $sizeBytes,
                'width' => $width,
                'height' => $height,
                'sort' => (int) $idx,
            ]);

            $photoMediaIds[] = $media->id;
        }

        // If there are no photos, we can't set cover/fallback background.
        if ($photoMediaIds !== []) {
            $post->cover_media_id = $photoMediaIds[0];
            $post->save();

            $folder->background_media_id = $photoMediaIds[0];
            $folder->save();
        }

        foreach ($videos as $idx => $vid) {
            $destPath = 'demo/import/videos/' . $idx . '_' . $vid['name'];
            if (! $disk->exists($destPath)) {
                $disk->put($destPath, File::get($vid['path']));
            }

            MediaFile::query()->create([
                'post_id' => $post->id,
                'media_type' => MediaFile::TYPE_VIDEO,
                'path' => $destPath,
                'original_name' => $vid['name'],
                'mime' => @mime_content_type($vid['path']) ?: null,
                'size_bytes' => @filesize($vid['path']) ?: null,
                'width' => null,
                'height' => null,
                'sort' => (int) $idx,
            ]);
        }

        foreach ($audios as $idx => $aud) {
            $destPath = 'demo/import/audios/' . $idx . '_' . $aud['name'];
            if (! $disk->exists($destPath)) {
                $disk->put($destPath, File::get($aud['path']));
            }

            MediaFile::query()->create([
                'post_id' => $post->id,
                'media_type' => MediaFile::TYPE_AUDIO,
                'path' => $destPath,
                'original_name' => $aud['name'],
                'mime' => @mime_content_type($aud['path']) ?: null,
                'size_bytes' => @filesize($aud['path']) ?: null,
                'width' => null,
                'height' => null,
                'sort' => (int) $idx,
            ]);
        }

        return $post->slug;
    });

    $this->info("Demo imported: folder=`{$folder->slug}`, post=`demo-post`");
    $this->info('Photos: ' . $counts['images'] . ', Videos: ' . $counts['videos'] . ', Audios: ' . $counts['audios']);
})->purpose('Import demo files from /demo into database');

