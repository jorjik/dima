@php
    $mainMedia = $post->feedCover ?? null;
    $bgUrl = $post->galleryBackgroundUrl ?? ($mainMedia?->url ?? null);
    $postUrl = route('post.show', ['slug' => $post->slug]);
@endphp

@if ($mainMedia)
    <div class="mb-3">
        <a
            href="{{ $postUrl }}"
            class="group relative block h-56 md:h-64 overflow-hidden rounded-2xl shadow-xl bg-[#111] interactive-surface"
        >
            @if ($bgUrl)
                <img
                    src="{{ $bgUrl }}"
                    alt="{{ $post->title }}"
                    class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                    loading="lazy"
                    decoding="async"
                    sizes="(max-width: 768px) 100vw, 896px"
                    fetchpriority="low"
                >
            @endif
            <div class="absolute inset-0 transition-colors group-hover:opacity-95" style="background: rgba(0, 0, 0, 0.11);"></div>
            <div class="pointer-events-none absolute inset-0 rounded-2xl shadow-[inset_0_0_0_1px_rgba(255,255,255,0.28)]"></div>

            <div class="absolute inset-0 z-10 flex items-center justify-center">
                <span class="inline-flex min-h-12 items-center rounded-2xl px-5 text-sm font-semibold text-white tracking-wide shadow-md" style="border:1px solid rgba(255,255,255,.55); background: rgba(0,0,0,.75);">
                    Открыть галерею
                </span>
            </div>

        </a>
    </div>
@endif

