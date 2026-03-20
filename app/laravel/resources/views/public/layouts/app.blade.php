<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Альбом жизни') }}</title>

        @vite(['resources/css/app.css'])
        @stack('styles')
        <script>
            // If Tailwind is loading, keep the page typography consistent.
            window.__APP_LOCALE__ = 'ru';
        </script>
    </head>

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
    @endphp

    <body
        class="bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC] min-h-screen"
        @if(!empty($siteBgUrl))
            style="background-image: url('{{ $siteBgUrl }}'); background-size: cover; background-position: center; background-attachment: fixed;"
        @endif
    >

        <header
            class="w-full border-b border-[#e3e3e0] dark:border-[#3E3E3A] relative overflow-hidden"
            @if(!empty($headerBgUrl))
                style="background-image: url('{{ $headerBgUrl }}'); background-size: cover; background-position: center;"
            @endif
        >
            @if(!empty($headerBgUrl))
                <div class="absolute inset-0 bg-black/40"></div>
            @endif

            <div class="relative max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
                <div class="flex flex-col">
                    <a href="{{ route('home') }}" class="font-medium text-lg">
                        {{ $headerTitle }}
                    </a>
                    @if(!empty($headerTagline))
                        <div class="text-sm opacity-80">{{ $headerTagline }}</div>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="text-sm underline underline-offset-4">
                        Главная
                    </a>
                </div>
            </div>
        </header>

        <main class="max-w-5xl mx-auto px-4 py-8">
            @yield('content')
        </main>
        @stack('scripts')
    </body>
</html>

