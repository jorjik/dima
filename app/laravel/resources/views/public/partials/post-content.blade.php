@php
    $contentPost = $post;
    $contentImages = $images ?? $contentPost->images;
    $contentVideos = $videos ?? $contentPost->videos;
    $contentAudios = $audios ?? $contentPost->audios;
    $contentVisualMedia = collect($contentImages)
        ->merge(collect($contentVideos))
        ->sortBy(fn ($item) => [($item->sort ?? 0), ($item->id ?? 0)])
        ->values();
    $galleryLinkUrl = $galleryLinkUrl ?? null;
    $enableLightbox = $enableLightbox ?? blank($galleryLinkUrl);
    $lightboxGroup = 'post-gallery-' . ($contentPost->id ?? 'x');
    /** @var bool Загружать картинки лениво (лента, блоки ниже первого экрана). */
    $lazyImages = $lazyImages ?? true;
    /** @var bool Первая картинка в сетке — eager (страница поста, LCP). */
    $eagerFirstImage = $eagerFirstImage ?? false;
    $imageSizesGrid = '(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw';
    $firstRasterMedia = $contentVisualMedia->first(function ($m) {
        $isVid = ($m->media_type ?? null) === 'video' || str_starts_with((string) ($m->mime ?? ''), 'video/');

        return ! $isVid;
    });
    $firstRasterMediaId = $firstRasterMedia?->id;
@endphp

@if (filled(trim((string) ($contentPost->body_markdown ?? ''))))
    <style>
        .post-content {
            word-break: break-word;
            overflow-wrap: anywhere;
            white-space: normal;
        }
        .post-content h1 { font-size: 1.875rem; font-weight: 700; line-height: 1.25; margin-top: 2rem; margin-bottom: 1.25rem; color: #f8fafc; }
        .post-content h2 { font-size: 1.5rem; font-weight: 600; line-height: 1.25; margin-top: 1.75rem; margin-bottom: 1rem; color: #f8fafc; }
        .post-content h3 { font-size: 1.25rem; font-weight: 600; line-height: 1.25; margin-top: 1.5rem; margin-bottom: 0.75rem; color: #f8fafc; }
        .post-content p { margin-top: 1rem; margin-bottom: 1rem; }
        .post-content ul { margin: 1rem 0; padding-left: 1.75rem; list-style-type: disc; }
        .post-content ol { margin: 1rem 0; padding-left: 1.75rem; list-style-type: decimal; }
        .post-content li { margin: 0.375rem 0; padding-left: 0.25rem; }
        .post-content strong { font-weight: 700; color: #f8fafc; }
        .post-content blockquote { margin: 1.25rem 0; border-left: 4px solid rgba(255,255,255,0.2); padding-left: 1rem; font-style: italic; opacity: 0.8; }
        .post-content img { max-width: 100%; height: auto; border-radius: 1rem; margin: 1.5rem 0; }
    </style>
    <article class="post-content text-base leading-8 text-white/85 mb-3">
        {!! \Illuminate\Support\Str::markdown((string) ($contentPost->body_markdown ?? '')) !!}
    </article>
@endif

@if ($contentVisualMedia->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 mb-4">
        @foreach ($contentVisualMedia as $media)
            @php
                $isVideo = ($media->media_type ?? null) === 'video' || str_starts_with((string) ($media->mime ?? ''), 'video/');
                $targetUrl = $galleryLinkUrl ?: $media->url;
                $imgLoading = 'lazy';
                if (! $lazyImages) {
                    $imgLoading = 'eager';
                } elseif ($eagerFirstImage && $loop->first && ! $isVideo) {
                    $imgLoading = 'eager';
                }
                $imgClass = 'h-full w-full max-w-full object-cover';
                $fetchHigh = ! $isVideo && $imgLoading === 'eager' && $firstRasterMediaId !== null
                    && (int) $media->id === (int) $firstRasterMediaId;
                $fetchLow = ! $isVideo && $imgLoading === 'lazy';
            @endphp
            @if ($enableLightbox)
                <a
                    href="{{ $media->url }}"
                    data-fancybox="{{ $lightboxGroup }}"
                    data-caption="{{ $media->original_name }}"
                    @if ($isVideo) data-width="1280" data-height="720" @endif
                    class="relative block h-44 md:h-48 overflow-hidden rounded-2xl bg-[#111]"
                >
                    @if ($isVideo)
                        <video muted playsinline preload="metadata" class="h-full w-full object-cover">
                            <source src="{{ $media->url }}" type="{{ $media->mime ?: 'video/mp4' }}">
                        </video>
                        <span class="absolute inset-0 grid place-items-center bg-black/20">
                            <span class="rounded-full bg-black/60 px-3 py-1 text-xs">Видео</span>
                        </span>
                    @else
                        <img
                            src="{{ $media->url }}"
                            alt="{{ $media->original_name }}"
                            class="{{ $imgClass }}"
                            loading="{{ $imgLoading }}"
                            decoding="async"
                            sizes="{{ $imageSizesGrid }}"
                            @if ($lazyImages && $imgLoading === 'lazy') fetchpriority="low" @endif
                            @if ($imgLoading === 'eager') fetchpriority="high" @endif
                            @if (filled($media->width) && filled($media->height))
                                width="{{ (int) $media->width }}"
                                height="{{ (int) $media->height }}"
                            @endif
                        >
                    @endif
                    <span class="pointer-events-none absolute inset-0 rounded-2xl shadow-[inset_0_0_0_1px_rgba(255,255,255,0.22)]"></span>
                </a>
            @else
                <a
                    href="{{ $targetUrl }}"
                    @if (!$galleryLinkUrl) target="_blank" rel="noopener" @endif
                    class="relative block h-44 md:h-48 overflow-hidden rounded-2xl bg-[#111]"
                >
                    @if ($isVideo)
                        <video muted playsinline preload="metadata" class="h-full w-full object-cover">
                            <source src="{{ $media->url }}" type="{{ $media->mime ?: 'video/mp4' }}">
                        </video>
                        <span class="absolute inset-0 grid place-items-center bg-black/20">
                            <span class="rounded-full bg-black/60 px-3 py-1 text-xs">Видео</span>
                        </span>
                    @else
                        <img
                            src="{{ $media->url }}"
                            alt="{{ $media->original_name }}"
                            class="{{ $imgClass }}"
                            loading="{{ $imgLoading }}"
                            decoding="async"
                            sizes="{{ $imageSizesGrid }}"
                            @if ($fetchHigh) fetchpriority="high" @endif
                            @if ($fetchLow) fetchpriority="low" @endif
                            @if (filled($media->width) && filled($media->height))
                                width="{{ (int) $media->width }}"
                                height="{{ (int) $media->height }}"
                            @endif
                        >
                    @endif
                    <span class="pointer-events-none absolute inset-0 rounded-2xl shadow-[inset_0_0_0_1px_rgba(255,255,255,0.22)]"></span>
                </a>
            @endif
        @endforeach
    </div>
@endif

@if ($contentAudios->count())
    <div class="grid grid-cols-1 gap-3">
        @foreach ($contentAudios as $audio)
            <div class="rounded-2xl border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 bg-white/60 dark:bg-[#161615]/40">
                <div class="text-sm opacity-80 mb-2">{{ $audio->original_name }}</div>
                <audio controls class="w-full">
                    <source src="{{ $audio->url }}" type="{{ $audio->mime ?: 'audio/mpeg' }}">
                </audio>
            </div>
        @endforeach
    </div>
@endif

