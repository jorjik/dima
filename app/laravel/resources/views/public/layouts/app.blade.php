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

    $socialLinks = [
        'telegram' => $setting?->social_telegram,
        'vk' => $setting?->social_vk,
        'instagram' => $setting?->social_instagram,
        'youtube' => $setting?->social_youtube,
        'whatsapp' => $setting?->social_whatsapp,
    ];

    $siteBgOverlayPercent = max(0, min(100, (int) ($setting?->site_background_overlay_percent ?? 20)));
    $headerOpacity = max(0, min(100, (int) ($setting?->header_background_opacity ?? 25)));

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
    } elseif (!\Illuminate\Support\Str::contains($pageTitle, $siteName)) {
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
        rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <script>
        // If Tailwind is loading, keep the page typography consistent.
        window.__APP_LOCALE__ = 'ru';
    </script>
</head>

<body class="font-sans antialiased bg-[#020617] text-[#E2E8F0] min-h-screen" @if(!empty($siteBgUrl))
    style="background-image: url('{{ $siteBgUrl }}'); background-size: cover; background-position: center; background-attachment: fixed;"
@endif>
    @if(!empty($siteBgUrl) && $siteBgOverlayPercent > 0)
        <div class="pointer-events-none fixed inset-0 z-0"
            style="background-color: rgba(0, 0, 0, {{ $siteBgOverlayPercent / 100 }});"></div>
    @endif

    <header class="w-full border-b border-white/15 relative z-10 overflow-hidden backdrop-blur"
        style="background-color: rgba(0, 0, 0, {{ $headerOpacity / 100 }});" data-animate @if(!empty($headerBgUrl))
            style="background-image: url('{{ $headerBgUrl }}'); background-size: cover; background-position: center; background-color: rgba(0, 0, 0, {{ $headerOpacity / 100 }});"
        @endif>
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

            <div class="flex items-center gap-4">
                @if($socialLinks['telegram'])
                    <a href="{{ $socialLinks['telegram'] }}" target="_blank" class="text-white/80 hover:text-white transition-colors interactive-surface" title="Telegram">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.12-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .33z"/></svg>
                    </a>
                @endif
                @if($socialLinks['vk'])
                    <a href="{{ $socialLinks['vk'] }}" target="_blank" class="text-white/80 hover:text-white transition-colors interactive-surface" title="VK">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M13.162 18.994c-6.098 0-10.307-4.133-10.307-10.826 0-.2.03-.393.088-.581H5.43c.121.57.182 1.15.182 1.737 0 .86-.142 1.713-.424 2.533-.357 1.258-1.03 2.378-1.95 3.308l-.131.13c.484 1.15 1.34 2.115 2.456 2.77 1.12.658 2.392 1.01 3.65 1.01h9.349c1.028 0 1.954-.334 2.685-1.16a2.633 2.633 0 00.75-1.896V9.45a3.155 3.155 0 00-1.102-2.316 2.613 2.613 0 00-1.87-.803h-2.17c-.126 0-.251.013-.375.039v1.942l.006.002h2.539c.071 0 .141.015.207.045s.124.073.17.127.08.118.1.189.03.111.03.14l-.001-.005.001 7.15c0 .085-.034.167-.094.227s-.142.094-.227.094h-8.084c-.33 0-.649-.131-.882-.364s-.363-.551-.363-.881V9.45c0-.085.034-.167.094-.227s.142-.094.227-.094h.619V7.188h-1.636c-.477 0-.916.142-1.282.385l-.022.016c-.05.14-.075.283-.075.428V16.34c0 .324.06.643.178.943.203.513.568.951 1.032 1.25.335.217.724.331 1.12.331h3.189z"/></svg>
                    </a>
                @endif
                @if($socialLinks['instagram'])
                    <a href="{{ $socialLinks['instagram'] }}" target="_blank" class="text-white/80 hover:text-white transition-colors interactive-surface" title="Instagram">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"></line></svg>
                    </a>
                @endif
                @if($socialLinks['youtube'])
                    <a href="{{ $socialLinks['youtube'] }}" target="_blank" class="text-white/80 hover:text-white transition-colors interactive-surface" title="YouTube">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    </a>
                @endif
                @if($socialLinks['whatsapp'])
                    <a href="{{ $socialLinks['whatsapp'] }}" target="_blank" class="text-white/80 hover:text-white transition-colors interactive-surface" title="WhatsApp">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.539 2.016 2.107-.534a5.72 5.72 0 002.946.81h.001c3.181 0 5.766-2.586 5.767-5.766 0-3.18-2.585-5.779-5.766-5.779zm3.387 8.113c-.147.41-.852.827-1.147.877-.297.049-.598.073-.93.073-.4 0-.9-.091-1.758-.452-1.465-.618-2.398-2.12-2.47-2.218-.073-.098-.593-.791-.593-1.488 0-.697.352-1.041.498-1.189.146-.148.339-.185.45-.185h.334c.108 0 .252 0 .393.308.144.316.488 1.192.531 1.284a.333.333 0 010 .324c-.094.195-.145.315-.285.457-.15.15-.315.335-.45.45-.147.123-.3-.257-.134-.54a1.867 1.867 0 01.442-.486c.153-.131.245-.21.406-.117.162.091 1.072.526 1.258.62.185.093.308.139.34.195a.44.44 0 01-.026.402z"/></svg>
                    </a>
                @endif
            </div>
        </div>
    </header>

    <main class="relative z-10 max-w-4xl mx-auto px-4 py-8" data-animate data-animate-delay="60">
        @yield('content')
    </main>
    @stack('scripts')
</body>

</html>