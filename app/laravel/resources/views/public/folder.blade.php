@extends('public.layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
@endpush

@section('content')
    <section class="mb-10">
        <div
            class="relative overflow-hidden rounded-2xl border border-white/30 shadow-xl bg-[#111]"
            @if ($folder->background_url)
                style="background-image: url('{{ $folder->background_url }}'); background-size: cover; background-position: center;"
            @endif
        >
            <div class="absolute inset-0 bg-black/55"></div>
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

                    <div class="relative" data-feed-item>
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
                                'lazyImages' => true,
                            ])
                        </div>

                        <div
                            class="hidden pointer-events-none absolute left-0 right-0 bottom-0 h-20 bg-gradient-to-t from-black via-black/70 to-transparent"
                            data-feed-gradient
                        ></div>
                        <button
                            type="button"
                            class="feed-toggle-btn"
                            data-feed-toggle
                        >Раскрыть</button>
                    </div>
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

            window.addEventListener('load', () => {
                feedItems.forEach((item) => evaluateFeedItem(item));
            });

            window.addEventListener('resize', () => {
                feedItems.forEach((item) => evaluateFeedItem(item));
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
