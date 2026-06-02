@extends('public.layouts.app')

@if (!empty($heroBackgroundUrl))
    @push('styles')
        <link rel="preload" as="image" href="{{ $heroBackgroundUrl }}" fetchpriority="high">
    @endpush
@endif

@if (isset($recentPosts) && $recentPosts->isNotEmpty() && $firstCoverUrl = $recentPosts->first()->cover_url)
    @push('styles')
        <link rel="preload" as="image" href="{{ $firstCoverUrl }}" fetchpriority="high">
    @endpush
@endif

@section('content')
    <section class="mb-10">
        <div class="relative overflow-hidden rounded-2xl shadow-xl flex items-center"
             style="background-color:#000; @if(!empty($heroBackgroundUrl)) background-image: url('{{ $heroBackgroundUrl }}'); @endif background-size: cover; background-position: center; aspect-ratio: 16 / 9; width: 100%;">
            {{-- Overlay --}}
            <div class="absolute inset-0" style="background-color: rgba(0, 0, 0, {{ $heroBackgroundOpacity / 100 }});"></div>
            {{-- Subtle border shine --}}
            <div class="pointer-events-none absolute inset-0 rounded-2xl shadow-[inset_0_0_0_1px_rgba(255,255,255,0.28)]"></div>
            {{-- Content --}}
            <div class="relative p-6 md:p-12 lg:p-16 w-full flex flex-col justify-center h-full">
                <h1 class="hero-title mb-2 text-2xl md:text-3xl lg:text-5xl font-bold tracking-tight text-white" style="text-shadow: 4px 4px 8px rgba(54,40,39,0.48);">{{ $heroTitle }}</h1>
                @if (filled($heroText))
                    <p class="hero-text max-w-3xl text-sm md:text-xl text-white/90 leading-relaxed" style="text-shadow: 4px 4px 8px rgba(54,40,39,0.48);">{{ $heroText }}</p>
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

                    @if ($post->isGalleryPost)
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
                        <div class="mt-2 text-sm leading-6 text-white/90 whitespace-pre-line break-words">
                            {{ $post->feedCaption }}
                        </div>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    <script>
        (function () {
            function evaluateExpandable(item, previewSelector, gradientSelector, toggleSelector, defaultHeight) {
                const preview = item.querySelector(previewSelector);
                const gradient = item.querySelector(gradientSelector);
                const toggle = item.querySelector(toggleSelector);
                if (!preview || !gradient || !toggle) return;

                const collapsedHeight = preview.dataset.collapsedHeight || defaultHeight;
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
            }

            function initExpandableItems(selector, previewSelector, gradientSelector, toggleSelector, defaultHeight) {
                const items = document.querySelectorAll(selector);
                items.forEach((item) => {
                    const preview = item.querySelector(previewSelector);
                    const toggle = item.querySelector(toggleSelector);
                    if (!preview || !toggle) return;

                    item.dataset.expanded = '0';
                    evaluateExpandable(item, previewSelector, gradientSelector, toggleSelector, defaultHeight);

                    toggle.addEventListener('click', function () {
                        item.dataset.expanded = item.dataset.expanded === '1' ? '0' : '1';
                        evaluateExpandable(item, previewSelector, gradientSelector, toggleSelector, defaultHeight);
                    });

                    preview.querySelectorAll('img').forEach((img) => {
                        if (!img.complete) {
                            img.addEventListener('load', () => evaluateExpandable(item, previewSelector, gradientSelector, toggleSelector, defaultHeight), { once: true });
                            img.addEventListener('error', () => evaluateExpandable(item, previewSelector, gradientSelector, toggleSelector, defaultHeight), { once: true });
                        }
                    });

                    preview.querySelectorAll('video').forEach((video) => {
                        video.addEventListener('loadedmetadata', () => evaluateExpandable(item, previewSelector, gradientSelector, toggleSelector, defaultHeight), { once: true });
                    });
                });
                return items;
            }

            const feedItems = initExpandableItems('[data-feed-item]', '[data-feed-preview]', '[data-feed-gradient]', '[data-feed-toggle]', '18rem');
            const captionItems = initExpandableItems('[data-caption-item]', '[data-caption-preview]', '[data-caption-gradient]', '[data-caption-toggle]', '8.5rem');

            let resizeTimer;
            window.addEventListener('load', () => {
                feedItems.forEach((item) => evaluateExpandable(item, '[data-feed-preview]', '[data-feed-gradient]', '[data-feed-toggle]', '18rem'));
                captionItems.forEach((item) => evaluateExpandable(item, '[data-caption-preview]', '[data-caption-gradient]', '[data-caption-toggle]', '8.5rem'));
            });

            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    feedItems.forEach((item) => evaluateExpandable(item, '[data-feed-preview]', '[data-feed-gradient]', '[data-feed-toggle]', '18rem'));
                    captionItems.forEach((item) => evaluateExpandable(item, '[data-caption-preview]', '[data-caption-gradient]', '[data-caption-toggle]', '8.5rem'));
                }, 150);
            });
        })();
    </script>
@endsection

@push('scripts')
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


