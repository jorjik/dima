@extends('public.layouts.app')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
@endpush

@section('content')
    <section class="mb-8">
        <div class="text-sm opacity-80 mb-2">
            <a href="{{ route('home') }}" class="underline underline-offset-4">Главная</a>
            @if ($folder)
                <span class="opacity-60"> / </span>
                <a href="{{ route('folder.show', ['slug' => $folder->slug]) }}" class="underline underline-offset-4">{{ $folder->title }}</a>
            @endif
        </div>

        <h1 class="text-3xl font-bold tracking-tight mb-2">{{ $post->title }}</h1>
        <div class="text-xs opacity-70 mb-4">{{ $post->created_at?->format('d.m.Y') }}</div>
    </section>

    <section class="mb-10">
        <div class="rounded-xl border border-[#e3e3e0] dark:border-[#3E3E3A] p-5 bg-white/60 dark:bg-[#161615]/40 backdrop-blur">
            @include('public.partials.post-content', [
                'post' => $post,
                'images' => $images,
                'videos' => $videos,
                'audios' => $audios,
                'enableLightbox' => true,
            ])
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script>
        if (window.Fancybox) {
            const getSelectedVideo = (container) => {
                if (!container) return null;
                return container.querySelector('.fancybox__slide.is-selected video');
            };

            const upsertSoundToggleButton = (fancyboxInstance) => {
                const container = fancyboxInstance?.container;
                if (!container) return;

                const buttonId = 'fancybox-sound-toggle';
                let button = container.querySelector(`#${buttonId}`);

                if (!button) {
                    button = document.createElement('button');
                    button.id = buttonId;
                    button.type = 'button';
                    button.style.position = 'absolute';
                    button.style.right = '16px';
                    button.style.bottom = '16px';
                    button.style.zIndex = '40';
                    button.style.border = '1px solid rgba(255,255,255,0.25)';
                    button.style.borderRadius = '9999px';
                    button.style.padding = '8px 12px';
                    button.style.fontSize = '12px';
                    button.style.lineHeight = '1';
                    button.style.background = 'rgba(0,0,0,0.65)';
                    button.style.color = '#fff';
                    button.style.cursor = 'pointer';
                    button.style.backdropFilter = 'blur(2px)';

                    button.addEventListener('click', () => {
                        const video = getSelectedVideo(container);
                        if (!video) return;

                        video.muted = !video.muted;
                        video.defaultMuted = video.muted;
                        if (video.muted) {
                            video.setAttribute('muted', '');
                        } else {
                            video.removeAttribute('muted');
                        }

                        button.textContent = video.muted ? 'Включить звук' : 'Выключить звук';
                        if (!video.paused) return;
                        video.play().catch(() => {});
                    });

                    container.appendChild(button);
                }

                const video = getSelectedVideo(container);
                if (!video) {
                    button.style.display = 'none';
                    return;
                }

                button.style.display = 'inline-flex';
                button.textContent = video.muted ? 'Включить звук' : 'Выключить звук';
            };

            const tryAutoplayCurrentVideo = (fancyboxInstance) => {
                try {
                    const container = fancyboxInstance?.container || document;
                    // Stop any previously playing videos in other slides.
                    container.querySelectorAll('.fancybox__slide video').forEach((v) => {
                        if (!v.paused) {
                            v.pause();
                        }
                    });

                    const video = container.querySelector('.fancybox__slide.is-selected video');
                    if (!video) return;

                    // Browser autoplay policy: muted + playsinline is usually allowed.
                    video.controls = true;
                    video.autoplay = true;
                    video.muted = true;
                    video.defaultMuted = true;
                    video.playsInline = true;
                    video.setAttribute('muted', '');
                    video.setAttribute('autoplay', '');
                    video.setAttribute('playsinline', '');

                    const playVideo = () => {
                        video.play().catch(() => {
                            // If blocked, user can still press Play manually.
                        });
                    };

                    // Try immediately and once again after the slide is fully painted.
                    playVideo();
                    setTimeout(playVideo, 120);
                } catch (e) {
                    // Ignore autoplay probing errors.
                }

                upsertSoundToggleButton(fancyboxInstance);
            };

            window.Fancybox.bind('[data-fancybox]', {
                Carousel: {
                    Video: {
                        autoplay: true,
                        muted: true,
                    },
                },
                Thumbs: {
                    autoStart: true,
                },
                Toolbar: {
                    display: {
                        left: ["infobar"],
                        middle: [],
                        right: ["zoom", "slideshow", "fullscreen", "close"],
                    },
                },
                on: {
                    ready: (fancyboxInstance) => tryAutoplayCurrentVideo(fancyboxInstance),
                    reveal: (fancyboxInstance) => tryAutoplayCurrentVideo(fancyboxInstance),
                    'Carousel.change': (fancyboxInstance) => tryAutoplayCurrentVideo(fancyboxInstance),
                },
            });
        }
    </script>
@endpush

