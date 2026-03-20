@php
    $mainMedia = $post->feedCover ?? null;
    $bgUrl = $post->galleryBackgroundUrl ?? ($mainMedia?->url ?? null);
    $postUrl = route('post.show', ['slug' => $post->slug]);
@endphp

@if ($mainMedia)
    <div class="mb-3">
        <a
            href="{{ $postUrl }}"
            class="group block rounded-xl overflow-hidden border border-white/25 dark:border-white/20 relative"
            @if ($bgUrl)
                style="background-image:url('{{ $bgUrl }}'); background-size:cover; background-position:center;"
            @else
                style="background-color:#111;"
            @endif
        >
            <div style="height: 224px;"></div>
            <div class="absolute inset-0 transition-colors group-hover:opacity-90" style="background: rgba(0, 0, 0, 0.55);"></div>

            <div class="absolute inset-0 z-10 flex items-center justify-center">
                <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold text-white tracking-wide shadow-md" style="border:1px solid rgba(255,255,255,.55); background: rgba(0,0,0,.75);">
                    Открыть галерею
                </span>
            </div>

        </a>
    </div>
@endif

