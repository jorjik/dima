<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $post = Post::query()
            ->with(['folder'])
            ->where('slug', $slug)
            ->firstOrFail();

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

        $post->cover_url = $cover ? Storage::disk('public')->url($cover->path) : null;

        $images = $post->images()->orderBy('sort')->get();
        $videos = $post->videos()->orderBy('sort')->get();
        $audios = $post->audios()->orderBy('sort')->get();

        $images->each(fn (MediaFile $m) => $m->url = Storage::disk('public')->url($m->path));
        $videos->each(fn (MediaFile $m) => $m->url = Storage::disk('public')->url($m->path));
        $audios->each(fn (MediaFile $m) => $m->url = Storage::disk('public')->url($m->path));

        $folder = $post->folder;

        return view('public.post', [
            'post' => $post,
            'folder' => $folder,
            'images' => $images,
            'videos' => $videos,
            'audios' => $audios,
        ]);
    }
}

