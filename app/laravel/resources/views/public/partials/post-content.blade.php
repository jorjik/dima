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
    <article
        class="text-base leading-8 break-words text-base-content/85 mb-3
            [&_h1]:text-3xl [&_h1]:font-bold [&_h1]:leading-tight [&_h1]:mt-8 [&_h1]:mb-5 [&_h1]:text-base-content/95
            [&_h2]:text-2xl [&_h2]:font-semibold [&_h2]:leading-tight [&_h2]:mt-7 [&_h2]:mb-4 [&_h2]:text-base-content/95
            [&_h3]:text-xl [&_h3]:font-semibold [&_h3]:leading-tight [&_h3]:mt-6 [&_h3]:mb-3 [&_h3]:text-base-content/95
            [&_h4]:text-lg [&_h4]:font-medium [&_h4]:mt-5 [&_h4]:mb-3 [&_h4]:text-base-content/95
            [&_p]:my-4
            [&_ul]:my-4 [&_ul]:pl-7 [&_ul]:list-disc
            [&_ol]:my-4 [&_ol]:pl-7 [&_ol]:list-decimal
            [&_li]:my-1.5 [&_li]:pl-1
            [&_strong]:font-bold [&_strong]:text-base-content/95
            [&_em]:italic
            [&_blockquote]:my-5 [&_blockquote]:border-l-4 [&_blockquote]:border-primary/35 [&_blockquote]:pl-4 [&_blockquote]:italic [&_blockquote]:text-base-content/80
            [&_a]:text-primary/80 [&_a]:underline [&_a]:underline-offset-4
            [&_hr]:my-6 [&_hr]:border-base-300
            [&_code]:px-2 [&_code]:py-1 [&_code]:rounded-md [&_code]:bg-base-200 [&_code]:text-base-content/90 [&_code]:text-sm
            [&_pre]:my-5 [&_pre]:p-4 [&_pre]:rounded-xl [&_pre]:overflow-x-auto [&_pre]:bg-base-200 [&_pre]:text-base-content/90 [&_pre]:text-sm
            [&_pre_code]:bg-transparent [&_pre_code]:p-0 [&_pre_code]:text-inherit
        "
    >
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
                    class="relative block h-44 md:h-48 overflow-hidden rounded-2xl border border-[#e3e3e0] dark:border-[#3E3E3A] bg-[#111]"
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
                </a>
            @else
                <a
                    href="{{ $targetUrl }}"
                    @if (!$galleryLinkUrl) target="_blank" rel="noopener" @endif
                    class="relative block h-44 md:h-48 overflow-hidden rounded-2xl border border-[#e3e3e0] dark:border-[#3E3E3A] bg-[#111]"
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

