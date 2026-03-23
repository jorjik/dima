@extends('public.layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
@endpush

@section('content')
    <section class="mb-10">
        <div
            class="relative overflow-hidden rounded-2xl shadow-xl bg-[#111]"
            @if ($folder->background_url)
                style="background-image: url('{{ $folder->background_url }}'); background-size: cover; background-position: center;"
            @endif
        >
            <div class="absolute inset-0 bg-black/55"></div>
            <div class="pointer-events-none absolute inset-0 rounded-2xl shadow-[inset_0_0_0_1px_rgba(255,255,255,0.28)]"></div>
            <div class="relative p-6 md:p-8 text-white">
                @php
                    $pc = $posts->count();
                    $postsLabel = 'постов';
                    if ($pc % 10 === 1 && $pc % 100 !== 11) {
                        $postsLabel = 'пост';
                    } elseif (in_array($pc % 10, [2, 3, 4], true) && ! in_array($pc % 100, [12, 13, 14], true)) {
                        $postsLabel = 'поста';
                    }
                @endphp
                <div class="mb-2 text-xs font-medium uppercase tracking-wide text-white/80">
                    {{ $pc }} {{ $postsLabel }}
                </div>
                <h1 class="mb-4 text-3xl font-bold tracking-tight md:text-4xl">{{ $folder->title }}</h1>
                <a href="{{ route('home') }}" class="text-sm text-white/90 underline underline-offset-4 hover:text-white">
                    Назад на главную
                </a>
            </div>
        </div>
    </section>

    <section>
        <h2 class="mb-4 text-xl font-semibold text-white md:text-2xl">Посты</h2>
        <div class="flex flex-col gap-4">
            @foreach ($posts as $post)
                <article class="feed-card">
                    <div class="feed-date">{{ $post->created_at?->format('d.m.Y') }}</div>

                    <div class="feed-title">{{ $post->title }}</div>

                    <div
                        class="text-white/95
                            [&_article]:max-w-prose [&_article]:!text-white/95
                            [&_article_h1]:!text-white [&_article_h2]:!text-white [&_article_h3]:!text-white [&_article_h4]:!text-white
                            [&_article_p]:my-1
                            [&_article_h1]:mt-0 [&_article_h1]:mb-1
                            [&_article_h2]:mt-0 [&_article_h2]:mb-1
                            [&_article_h3]:mt-0 [&_article_h3]:mb-1
                            [&_article_h4]:mt-0 [&_article_h4]:mb-1"
                    >
                        @include('public.partials.post-content', [
                            'post' => $post,
                            'images' => $post->images,
                            'videos' => $post->videos,
                            'audios' => $post->audios,
                            'enableLightbox' => true,
                            'lazyImages' => true,
                        ])
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script>
        if (window.Fancybox) {
            window.Fancybox.bind('[data-fancybox^="post-gallery-"]', {
                Thumbs: {
                    autoStart: true,
                },
                Carousel: {
                    Video: {
                        autoplay: true,
                        muted: true,
                    },
                },
            });
        }
    </script>
@endpush
