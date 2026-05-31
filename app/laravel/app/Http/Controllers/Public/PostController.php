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
            ->with(['folder', 'cover', 'images', 'videos', 'audios'])
            ->where('slug', $slug)
            ->firstOrFail();

        $folder = $post->folder;

        return view('public.post', [
            'post' => $post,
            'folder' => $folder,
            'images' => $post->images,
            'videos' => $post->videos,
            'audios' => $post->audios,
        ]);
    }
}

