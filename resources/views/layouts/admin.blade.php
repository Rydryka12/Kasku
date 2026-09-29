<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Admin - {{ config('app.name', 'Kasku') }}</title>

        <!-- Theme Initialization -->
        <script>
            if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="h-full bg-kertas text-tinta antialiased flex flex-col font-sans">
        <div class="flex-1 flex overflow-hidden">
            <!-- Admin Sidebar Desktop -->
            <aside class="hidden lg:flex lg:flex-col w-58 shrink-0 bg-permukaan border-r border-garis">
                <!-- Brand + Admin Badge -->
                <div class="h-16 flex items-center justify-between px-6 border-b border-garis">
                    <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center gap-2 text-xl font-bold text-batik">
                        <span class="text-2xl leading-none">◈</span>
                        <span>{{ config('app.name', 'Kasku') }}</span>
                    </a>
                    <span class="px-2 py-0.5 text-xs font-bold bg-keluar/10 text-keluar rounded-full border border-keluar/20">
                        Admin
                    </span>
                </div>

                <!-- Admin Navigation: NO financial routes -->
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    <x-nav-item href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')" icon="home">
                        Ringkasan
                    </x-nav-item>

                    <x-nav-item href="{{ route('admin.users') }}" :active="request()->routeIs('admin.users*')" icon="user">
                        Pengguna
                    </x-nav-item>

                    <x-nav-item href="{{ route('admin.categories') }}" :active="request()->routeIs('admin.categories*')" icon="tags">
                        Kategori Default
                    </x-nav-item>

                    <div class="pt-4 mt-4 border-t border-garis">
                        <x-nav-item href="{{ route('profile') }}" :active="request()->routeIs('profile')" icon="user">
                            Profil
                        </x-nav-item>
                    </div>
                </nav>

                <!-- Admin user footer -->
                <div class="p-3 border-t border-garis">
                    <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-kertas text-sm">
                        <div class="truncate">
                            <p class="font-medium text-tinta truncate">{{ auth()->user()?->name ?? 'Admin' }}</p>
                            <p class="text-xs text-tinta-redup truncate">{{ auth()->user()?->email ?? '' }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" title="Keluar" class="text-tinta-redup hover:text-keluar p-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </aside>

            <!-- Main Admin Content Area -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                <!-- Top Header -->
                <header class="h-16 shrink-0 bg-permukaan border-b border-garis flex items-center justify-between px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <div class="lg:hidden text-batik font-bold text-xl flex items-center gap-1">
                            <span>◈ Admin</span>
                        </div>
                        @if(isset($header))
                            <div class="hidden lg:block text-xl font-bold text-tinta">
                                {{ $header }}
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center gap-3">
                        <!-- Theme Toggle Button -->
                        <button
                            type="button"
                            x-data="{
                                dark: document.documentElement.classList.contains('dark'),
                                toggle() {
                                    this.dark = !this.dark;
                                    if (this.dark) {
                                        document.documentElement.classList.add('dark');
                                        localStorage.theme = 'dark';
                                    } else {
                                        document.documentElement.classList.remove('dark');
                                        localStorage.theme = 'light';
                                    }
                                }
                            }"
                            @click="toggle()"
                            class="p-2 rounded-lg text-tinta-redup hover:text-tinta hover:bg-kertas transition focus:outline-none focus:ring-2 focus:ring-batik"
                            title="Ganti Tema"
                            aria-label="Ganti Tema"
                        >
                            <svg x-show="!dark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <svg x-show="dark" style="display: none;" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        </button>
                    </div>
                </header>

                <!-- Page Content Body -->
                <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                    <div class="max-w-[1120px] mx-auto">
                        @if(isset($header))
                            <div class="lg:hidden mb-4">
                                <h1 class="text-xl font-bold text-tinta">{{ $header }}</h1>
                            </div>
                        @endif

                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>

        <x-toast />
    </body>
</html>
