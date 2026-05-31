<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $post = Post::query()
            ->with(['folder', 'cover', 'media'])
            ->where('slug', $slug)
            ->firstOrFail();

        $folder = $post->folder;

        return view('public.post', [
            'post' => $post,
            'folder' => $folder,
            'images' => $post->media->where('media_type', \App\Models\MediaFile::TYPE_IMAGE)->values(),
            'videos' => $post->media->where('media_type', \App\Models\MediaFile::TYPE_VIDEO)->values(),
            'audios' => $post->media->where('media_type', \App\Models\MediaFile::TYPE_AUDIO)->values(),
        ]);
    }
}

