@extends('public.layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
@endpush

@section('content')
    <section class="mb-10">
        <div class="rounded-xl overflow-hidden border border-[#e3e3e0] dark:border-[#3E3E3A] relative"
             style="background-color:#111; background-image: url('{{ $heroBackgroundUrl }}'); background-size: cover; background-position: center;">
            <div class="absolute inset-0 bg-black/40"></div>
            <div class="relative p-8">
                <h1 class="text-3xl font-bold tracking-tight mb-2">{{ $heroTitle }}</h1>
                @if (filled($heroText))
                    <p class="text-sm opacity-90">{{ $heroText }}</p>
                @endif
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-xl font-semibold mb-4">Лента</h2>
        <div class="flex flex-col gap-4">
            @foreach ($recentPosts as $post)
                <article class="rounded-xl border border-[#e3e3e0] dark:border-[#3E3E3A] p-4 bg-white/40 dark:bg-[#161615]/40 backdrop-blur">
                    <div class="text-sm opacity-80 mb-2">{{ $post->created_at?->format('d.m.Y') }}</div>

                    <div class="text-lg font-semibold mb-3">{{ $post->title }}</div>

                    @if ($post->isGalleryPost && $post->hasGallery)
                        @include('public.partials.feed-gallery-card', [
                            'post' => $post,
                        ])
                    @else
                        <div class="relative" data-feed-item>
                            <div
                                class="overflow-hidden transition-all duration-300"
                                style="max-height: 18rem;"
                                data-feed-preview
                                data-collapsed-height="18rem"
                            >
                                @include('public.partials.post-content', [
                                    'post' => $post,
                                    'images' => $post->images,
                                    'videos' => $post->videos,
                                    'audios' => $post->audios,
                                    'enableLightbox' => true,
                                ])
                            </div>

                            <div
                                class="hidden pointer-events-none absolute left-0 right-0 bottom-0 h-20 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/70 to-transparent"
                                data-feed-gradient
                            ></div>
                            <button
                                type="button"
                                class="hidden mt-3 w-fit mx-auto flex items-center justify-center rounded-full border border-white/35 bg-black/60 px-4 py-1.5 text-xs font-medium tracking-wide text-white shadow-md backdrop-blur transition-colors hover:bg-black/75"
                                data-feed-toggle
                            >Раскрыть</button>
                        </div>
                    @endif

                    @if ($post->isGalleryPost && filled($post->feedCaption))
                        <div class="relative" data-caption-item>
                            <div
                                class="overflow-hidden transition-all duration-300 text-sm leading-6 opacity-90 whitespace-pre-line break-words"
                                style="max-height: 8.5rem;"
                                data-caption-preview
                                data-collapsed-height="8.5rem"
                            >{{ $post->feedCaption }}</div>

                            <div
                                class="hidden pointer-events-none absolute left-0 right-0 bottom-0 h-16 bg-gradient-to-t from-[#0a0a0a] via-[#0a0a0a]/70 to-transparent"
                                data-caption-gradient
                            ></div>
                            <button
                                type="button"
                                class="hidden mt-3 w-fit mx-auto flex items-center justify-center rounded-full border border-white/35 bg-black/60 px-4 py-1.5 text-xs font-medium tracking-wide text-white shadow-md backdrop-blur transition-colors hover:bg-black/75"
                                data-caption-toggle
                            >Раскрыть</button>
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    <script>
        (function () {
            const feedItems = document.querySelectorAll('[data-feed-item]');
            const evaluateFeedItem = (item) => {
                const preview = item.querySelector('[data-feed-preview]');
                const gradient = item.querySelector('[data-feed-gradient]');
                const toggle = item.querySelector('[data-feed-toggle]');
                if (!preview || !gradient || !toggle) return;

                const collapsedHeight = preview.dataset.collapsedHeight || '18rem';
                const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize || '16');
                const collapsedPx = parseFloat(collapsedHeight) * rootFontSize;
                const expanded = item.dataset.expanded === '1';
                const prevMaxHeight = preview.style.maxHeight;
                preview.style.maxHeight = 'none';
                const fullHeight = preview.scrollHeight;
                const isLong = fullHeight > (collapsedPx + 2);

                if (!isLong) {
                    item.dataset.expanded = '0';
                    preview.style.maxHeight = '';
                    gradient.classList.add('hidden');
                    toggle.classList.add('hidden');
                    toggle.textContent = 'Раскрыть';
                    return;
                }

                toggle.classList.remove('hidden');
                if (expanded) {
                    preview.style.maxHeight = fullHeight + 'px';
                    gradient.classList.add('hidden');
                    toggle.textContent = 'Свернуть';
                } else {
                    preview.style.maxHeight = collapsedHeight;
                    gradient.classList.remove('hidden');
                    toggle.textContent = 'Раскрыть';
                }

                if (!expanded && prevMaxHeight && prevMaxHeight !== 'none') {
                    preview.style.maxHeight = collapsedHeight;
                }
            };

            feedItems.forEach((item) => {
                const preview = item.querySelector('[data-feed-preview]');
                const toggle = item.querySelector('[data-feed-toggle]');
                if (!preview || !toggle) return;

                item.dataset.expanded = '0';
                evaluateFeedItem(item);

                toggle.addEventListener('click', function () {
                    item.dataset.expanded = item.dataset.expanded === '1' ? '0' : '1';
                    evaluateFeedItem(item);
                });

                preview.querySelectorAll('img').forEach((img) => {
                    if (!img.complete) {
                        img.addEventListener('load', () => evaluateFeedItem(item), { once: true });
                        img.addEventListener('error', () => evaluateFeedItem(item), { once: true });
                    }
                });

                preview.querySelectorAll('video').forEach((video) => {
                    video.addEventListener('loadedmetadata', () => evaluateFeedItem(item), { once: true });
                });
            });

            const items = document.querySelectorAll('[data-caption-item]');
            const evaluateItem = (item) => {
                const preview = item.querySelector('[data-caption-preview]');
                const gradient = item.querySelector('[data-caption-gradient]');
                const toggle = item.querySelector('[data-caption-toggle]');
                if (!preview || !gradient || !toggle) return;

                const collapsedHeight = preview.dataset.collapsedHeight || '8.5rem';
                const rootFontSize = parseFloat(getComputedStyle(document.documentElement).fontSize || '16');
                const collapsedPx = parseFloat(collapsedHeight) * rootFontSize;
                const expanded = item.dataset.expanded === '1';
                const prevMaxHeight = preview.style.maxHeight;
                preview.style.maxHeight = 'none';
                const fullHeight = preview.scrollHeight;
                const isLong = fullHeight > (collapsedPx + 2);
                if (!isLong) {
                    item.dataset.expanded = '0';
                    preview.style.maxHeight = '';
                    gradient.classList.add('hidden');
                    toggle.classList.add('hidden');
                    toggle.textContent = 'Раскрыть';
                    return;
                }

                toggle.classList.remove('hidden');
                if (expanded) {
                    preview.style.maxHeight = fullHeight + 'px';
                    gradient.classList.add('hidden');
                    toggle.textContent = 'Свернуть';
                } else {
                    preview.style.maxHeight = collapsedHeight;
                    gradient.classList.remove('hidden');
                    toggle.textContent = 'Раскрыть';
                }

                if (!expanded && prevMaxHeight && prevMaxHeight !== 'none') {
                    preview.style.maxHeight = collapsedHeight;
                }
            };

            items.forEach((item) => {
                const preview = item.querySelector('[data-caption-preview]');
                const toggle = item.querySelector('[data-caption-toggle]');
                if (!preview || !toggle) return;

                item.dataset.expanded = '0';
                evaluateItem(item);

                toggle.addEventListener('click', function () {
                    item.dataset.expanded = item.dataset.expanded === '1' ? '0' : '1';
                    evaluateItem(item);
                });

            });

            window.addEventListener('load', () => {
                feedItems.forEach((item) => evaluateFeedItem(item));
                items.forEach((item) => evaluateItem(item));
            });

            window.addEventListener('resize', () => {
                feedItems.forEach((item) => evaluateFeedItem(item));
                items.forEach((item) => evaluateItem(item));
            });
        })();
    </script>
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


