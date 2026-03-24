<!DOCTYPE html>
<html lang="ru">
    @php
        /** @var \App\Models\SiteSetting|null $setting */
        $setting = \App\Models\SiteSetting::query()->first();
        $headerTitle = $setting?->header_title ?: 'Альбом жизни';
        $headerTagline = $setting?->header_tagline;
        $headerBgUrl = $setting?->header_background_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($setting->header_background_path)
            : null;
        $siteBgUrl = $setting?->site_background_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($setting->site_background_path)
            : null;

        $siteBgOverlayPercent = max(0, min(100, (int) ($setting?->site_background_overlay_percent ?? 20)));

        $siteName = $headerTitle;
        $routeName = request()->route()?->getName();

        $homeMetaTitle = trim((string) ($setting?->home_meta_title ?: $siteName));
        $homeMetaDescription = trim((string) ($setting?->home_meta_description ?: ($setting?->home_hero_text ?: 'Фото и видео из семейного архива.')));

        $generatedTitle = $siteName;
        $generatedDescription = $homeMetaDescription;

        if (request()->routeIs('home')) {
            $generatedTitle = $homeMetaTitle;
            $generatedDescription = $homeMetaDescription;
        } elseif (request()->routeIs('folder.show') && isset($folder)) {
            $postsCount = isset($posts) ? $posts->count() : $folder->posts()->count();
            $generatedTitle = trim($folder->title . ' - ' . $siteName);
            $generatedDescription = trim("Папка {$folder->title}. Постов: {$postsCount}.");
        } elseif (request()->routeIs('post.show') && isset($post)) {
            $generatedTitle = trim($post->title . ' - ' . $siteName);

            $plainText = trim(strip_tags(\Illuminate\Support\Str::markdown((string) ($post->body_markdown ?? ''))));
            $plainText = preg_replace('/\s+/u', ' ', $plainText) ?? '';
            $folderName = $folder->title ?? $post->folder?->title ?? null;
            $generatedDescription = $plainText !== ''
                ? \Illuminate\Support\Str::limit($plainText, 160)
                : trim('Пост' . ($folderName ? " из папки {$folderName}" : '') . '.');
        } elseif (filled($routeName)) {
            $generatedTitle = trim(\Illuminate\Support\Str::headline(str_replace('.', ' ', $routeName)) . ' - ' . $siteName);
            $generatedDescription = trim("Страница сайта {$siteName}.");
        }

        $pageTitle = trim((string) $__env->yieldContent('title'));
        if ($pageTitle === '') {
            $pageTitle = $generatedTitle;
        } elseif (! \Illuminate\Support\Str::contains($pageTitle, $siteName)) {
            $pageTitle = trim($pageTitle . ' - ' . $siteName);
        }

        $metaDescription = trim((string) $__env->yieldContent('meta_description'));
        if ($metaDescription === '') {
            $metaDescription = $generatedDescription;
        }
        $metaDescription = trim(strip_tags($metaDescription));
        $metaDescription = preg_replace('/\s+/u', ' ', $metaDescription) ?? '';
        $metaDescription = \Illuminate\Support\Str::limit($metaDescription, 160);
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $metaDescription }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link
            href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
            rel="stylesheet"
        >
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
        <script>
            // If Tailwind is loading, keep the page typography consistent.
            window.__APP_LOCALE__ = 'ru';
        </script>
    </head>

    <body
        class="font-sans antialiased bg-[#020617] text-[#E2E8F0] min-h-screen"
        @if(!empty($siteBgUrl))
            style="background-image: url('{{ $siteBgUrl }}'); background-size: cover; background-position: center; background-attachment: fixed;"
        @endif
    >
        @if(!empty($siteBgUrl) && $siteBgOverlayPercent > 0)
            <div
                class="pointer-events-none fixed inset-0 z-0"
                style="background-color: rgba(0, 0, 0, {{ $siteBgOverlayPercent / 100 }});"
            ></div>
        @endif

        <header
            class="w-full border-b border-white/15 bg-black/25 relative z-10 overflow-hidden backdrop-blur"
            data-animate
            @if(!empty($headerBgUrl))
                style="background-image: url('{{ $headerBgUrl }}'); background-size: cover; background-position: center;"
            @endif
        >
            @if(!empty($headerBgUrl))
                <div class="absolute inset-0 bg-black/40"></div>
            @endif

            <div class="relative max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex flex-col">
                    <a href="{{ route('home') }}" class="font-semibold text-lg text-white interactive-surface">
                        {{ $headerTitle }}
                    </a>
                    @if(!empty($headerTagline))
                        <div class="text-sm text-white/80">{{ $headerTagline }}</div>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="text-sm text-white/90 underline underline-offset-4 transition-colors duration-200 hover:text-white interactive-surface">
                        Главная
                    </a>
                </div>
            </div>
        </header>

        <main class="relative z-10 max-w-4xl mx-auto px-4 py-8" data-animate data-animate-delay="60">
            @yield('content')
        </main>
        @stack('scripts')
    </body>
</html>

