<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FolderController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $folder = Folder::query()
            ->with('posts')
            ->where('slug', $slug)
            ->firstOrFail();

        // Fallback: фон из обложки самого нового поста (только фото).
        $bg = $folder->backgroundMedia;

        if (! $bg) {
            $newestPost = $folder->posts()
                ->with('cover')
                ->orderByDesc('created_at')
                ->first();

            $bg = $newestPost?->cover;

            if ($bg) {
                if ((int) $bg->post_id !== (int) $newestPost->id || $bg->media_type !== MediaFile::TYPE_IMAGE) {
                    $bg = null;
                }
            }

            if (! $bg) {
                $bg = $newestPost?->images()->where('media_type', MediaFile::TYPE_IMAGE)->orderBy('sort')->first();
            }
        }

        if ($bg && $bg->media_type !== MediaFile::TYPE_IMAGE) {
            $bg = null;
        }

        $folder->background_url = $bg ? Storage::disk('public')->url($bg->path) : null;

        $posts = $folder->posts()
            ->with(['cover', 'images'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Post $post): Post {
                $cover = $post->cover;
                if ($cover) {
                    if ((int) $cover->post_id !== (int) $post->id || $cover->media_type !== MediaFile::TYPE_IMAGE) {
                        $cover = null;
                    }
                }

                if (! $cover) {
                    $cover = $post->images()->orderBy('sort')->first();
                    $post->setRelation('cover', $cover);
                }

                $post->cover_url = $cover ? Storage::disk('public')->url($cover->path) : null;

                $post->images->each(function (MediaFile $m): void {
                    $m->url = Storage::disk('public')->url($m->path);
                });

                return $post;
            });

        return view('public.folder', [
            'folder' => $folder,
            'posts' => $posts,
        ]);
    }
}

