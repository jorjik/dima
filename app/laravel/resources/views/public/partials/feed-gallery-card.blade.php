@php
    $mainMedia = $post->feedCover ?? null;
    $bgUrl = $post->galleryBackgroundUrl ?? ($mainMedia?->url ?? null);
    $postUrl = route('post.show', ['slug' => $post->slug]);
@endphp

@if ($mainMedia)
    <div class="mb-3">
        <a
            href="{{ $postUrl }}"
            class="group relative block h-56 md:h-64 overflow-hidden rounded-2xl border border-white/30 shadow-xl"
            @if ($bgUrl)
                style="background-image:url('{{ $bgUrl }}'); background-size:cover; background-position:center;"
            @else
                style="background-color:#111;"
            @endif
        >
            <div class="absolute inset-0 transition-colors group-hover:opacity-95" style="background: rgba(0, 0, 0, 0.11);"></div>

            <div class="absolute inset-0 z-10 flex items-center justify-center">
                <span class="inline-flex min-h-12 items-center rounded-2xl px-5 text-sm font-semibold text-white tracking-wide shadow-md" style="border:1px solid rgba(255,255,255,.55); background: rgba(0,0,0,.75);">
                    Открыть галерею
                </span>
            </div>

        </a>
    </div>
@endif

