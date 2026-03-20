<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use App\Models\Post;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $recentPosts = Post::query()
            ->with(['folder', 'media', 'images', 'videos', 'audios'])
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $recentPosts = $recentPosts->map(function (Post $post): Post {
            $cover = $post->cover;

            if ($cover) {
                // Defensive check: cover must belong to the same post and must be an image.
                if ((int) $cover->post_id !== (int) $post->id || $cover->media_type !== MediaFile::TYPE_IMAGE) {
                    $cover = null;
                }
            }

            if (! $cover) {
                $cover = $post->images()->orderBy('sort')->first();
                $post->setRelation('cover', $cover);
            }

            return $post;
        });

        // Store URLs ready for templates.
        $recentPosts->each(function (Post $post) {
            $post->media->each(function (MediaFile $m): void {
                $m->url = Storage::disk('public')->url($m->path);
            });

            /** @var MediaFile|null $cover */
            $cover = $post->cover;
            if ($cover) {
                if ((int) $cover->post_id !== (int) $post->id || $cover->media_type !== MediaFile::TYPE_IMAGE) {
                    $cover = null;
                }
            }

            $post->cover_url = $cover ? Storage::disk('public')->url($cover->path) : null;

            $post->images->each(function (MediaFile $m): void {
                $m->url = Storage::disk('public')->url($m->path);
            });
            $post->videos->each(function (MediaFile $m): void {
                $m->url = Storage::disk('public')->url($m->path);
            });
            $post->audios->each(function (MediaFile $m): void {
                $m->url = Storage::disk('public')->url($m->path);
            });

            $galleryMedia = $post->media
                ->filter(fn (MediaFile $m): bool => in_array($m->media_type, [MediaFile::TYPE_IMAGE, MediaFile::TYPE_VIDEO], true))
                ->sortBy([
                    ['sort', 'asc'],
                    ['created_at', 'asc'],
                    ['id', 'asc'],
                ])
                ->values();

            $post->feedCover = $galleryMedia->first();
            $post->feedThumbs = $galleryMedia->slice(1, 3)->values();
            $post->isGalleryPost = filled($post->folder_id);
            $post->hasGallery = $post->isGalleryPost && $galleryMedia->isNotEmpty();
            $post->hasAudio = $post->audios->isNotEmpty();
            $post->galleryMedia = $galleryMedia;

            $backgroundImage = $post->images
                ->sortBy([
                    ['sort', 'asc'],
                    ['created_at', 'asc'],
                    ['id', 'asc'],
                ])
                ->first();
            $post->galleryBackgroundUrl = $backgroundImage?->url;

            $rawMarkdown = trim((string) ($post->body_markdown ?? ''));
            if ($rawMarkdown === '') {
                $post->feedCaption = '';
                return;
            }

            $asHtml = Str::markdown($rawMarkdown);
            $normalizedHtml = str_ireplace(
                ['</p>', '</li>', '<br>', '<br/>', '<br />'],
                "\n",
                $asHtml
            );
            $plain = trim(strip_tags($normalizedHtml));
            $plain = preg_replace('/[ \t]+/u', ' ', $plain) ?? '';
            $plain = preg_replace('/\n{2,}/u', "\n", $plain) ?? '';
            $post->feedCaption = trim($plain);
        });

        $siteSetting = SiteSetting::query()->first();
        $heroBackgroundUrl = $siteSetting?->home_hero_background_path
            ? Storage::disk('public')->url($siteSetting->home_hero_background_path)
            : 'https://picsum.photos/seed/dima-hero/1200/600';

        return view('public.home', [
            'recentPosts' => $recentPosts,
            'heroTitle' => $siteSetting?->home_hero_title ?: 'Фото-видео альбом жизни',
            'heroText' => $siteSetting?->home_hero_text ?: 'Посты внутри папок. Обложки и фон берутся только из фото.',
            'heroBackgroundUrl' => $heroBackgroundUrl,
        ]);
    }
}

