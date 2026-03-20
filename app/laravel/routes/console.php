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

