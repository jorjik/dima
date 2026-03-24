@extends('public.layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
@endpush

@section('content')
    <section class="mb-10" data-animate>
        <div class="relative overflow-hidden rounded-2xl shadow-xl"
             style="background-color:#111; background-image: url('{{ $heroBackgroundUrl }}'); background-size: cover; background-position: center;">
            <div class="absolute inset-0 bg-black/55"></div>
            <div class="pointer-events-none absolute inset-0 rounded-2xl shadow-[inset_0_0_0_1px_rgba(255,255,255,0.28)]"></div>
            <div class="relative p-6 md:p-8">
                <h1 class="mb-2 text-3xl md:text-4xl font-bold tracking-tight text-white">{{ $heroTitle }}</h1>
                @if (filled($heroText))
                    <p class="max-w-2xl text-sm md:text-base text-white/90">{{ $heroText }}</p>
                @endif
            </div>
        </div>
    </section>

    <section data-animate data-animate-delay="80">
        <h2 class="mb-4 text-xl md:text-2xl font-semibold text-white">Лента</h2>
        <div class="flex flex-col gap-4">
            @foreach ($recentPosts as $post)
                <article class="feed-card interactive-surface" data-animate data-animate-delay="{{ ($loop->index % 6) * 40 }}">
                    <div class="feed-date">{{ $post->created_at?->format('d.m.Y') }}</div>

                    <div class="feed-title">{{ $post->title }}</div>

                    @if ($post->isGalleryPost && $post->hasGallery)
                        @include('public.partials.feed-gallery-card', [
                            'post' => $post,
                        ])
                    @else
                        <div class="relative" data-feed-item>
                            <div class="relative">
                                <div
                                    class="overflow-hidden transition-all duration-300
                                        text-white/95
                                        [&_article]:max-w-prose [&_article]:!text-white/95
                                        [&_article_h1]:!text-white [&_article_h2]:!text-white [&_article_h3]:!text-white [&_article_h4]:!text-white
                                        [&_article_p]:my-1
                                        [&_article_h1]:mt-0 [&_article_h1]:mb-1
                                        [&_article_h2]:mt-0 [&_article_h2]:mb-1
                                        [&_article_h3]:mt-0 [&_article_h3]:mb-1
                                        [&_article_h4]:mt-0 [&_article_h4]:mb-1"
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
                                    class="hidden pointer-events-none absolute left-0 right-0 bottom-0 h-20 bg-gradient-to-t from-black via-black/70 to-transparent"
                                    data-feed-gradient
                                ></div>
                            </div>
                            <button
                                type="button"
                                class="feed-toggle-btn"
                                data-feed-toggle
                            >Раскрыть</button>
                        </div>
                    @endif

                    @if ($post->isGalleryPost && filled($post->feedCaption))
                        <div class="relative mt-2" data-caption-item>
                            <div class="relative">
                                <div
                                    class="overflow-hidden transition-all duration-300 text-sm leading-6 text-white/90 whitespace-pre-line break-words"
                                    style="max-height: 8.5rem;"
                                    data-caption-preview
                                    data-collapsed-height="8.5rem"
                                >{{ $post->feedCaption }}</div>

                                <div
                                    class="hidden pointer-events-none absolute left-0 right-0 bottom-0 h-16 bg-gradient-to-t from-black via-black/70 to-transparent"
                                    data-caption-gradient
                                ></div>
                            </div>
                            <button
                                type="button"
                                class="feed-toggle-btn"
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


