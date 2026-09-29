<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kasku') }}</title>

        <!-- Theme Initialization -->
        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <!-- Fonts: Plus Jakarta Sans -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-kertas text-tinta font-sans antialiased min-h-screen flex flex-col justify-center items-center p-4">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <a href="/" wire:navigate class="inline-flex items-center gap-2 text-2xl font-extrabold text-batik">
                    <span class="text-3xl leading-none">◈</span>
                    <span>{{ config('app.name', 'Kasku') }}</span>
                </a>
            </div>

            <div class="w-full bg-permukaan border border-garis rounded-2xl p-6 sm:p-8 shadow-sm">
                {{ $slot }}
            </div>
        </div>

        <x-toast />
    </body>
</html>
