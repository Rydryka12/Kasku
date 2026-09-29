<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kasku') }}</title>

        <!-- Theme Initialization to prevent flash -->
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
    <body class="h-full bg-kertas text-tinta antialiased flex flex-col font-sans">
        <div class="flex-1 flex overflow-hidden">
            <!-- Sidebar Desktop (>= 1024px) -->
            <aside class="hidden lg:flex lg:flex-col w-58 shrink-0 bg-permukaan border-r border-garis">
                <!-- Brand -->
                <div class="h-16 flex items-center px-6 border-b border-garis">
                    <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 text-xl font-bold text-batik">
                        <span class="text-2xl leading-none">◈</span>
                        <span>{{ config('app.name', 'Kasku') }}</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    <x-nav-item href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="home">
                        Beranda
                    </x-nav-item>

                    <x-nav-item href="{{ route('transactions.index') }}" :active="request()->routeIs('transactions.*')" icon="receipt">
                        Transaksi
                    </x-nav-item>

                    <x-nav-item href="{{ route('transfers.index') }}" :active="request()->routeIs('transfers.*')" icon="arrows">
                        Transfer
                    </x-nav-item>

                    <x-nav-item href="{{ route('savings.index') }}" :active="request()->routeIs('savings.*')" icon="piggy">
                        Tabungan
                    </x-nav-item>

                    <x-nav-item href="{{ route('budgets.index') }}" :active="request()->routeIs('budgets.*')" icon="calculator">
                        Budget
                    </x-nav-item>

                    <x-nav-item href="{{ route('recurring.index') }}" :active="request()->routeIs('recurring.*')" icon="repeat">
                        Berulang
                    </x-nav-item>

                    <x-nav-item href="{{ route('debts.index') }}" :active="request()->routeIs('debts.*')" icon="scale">
                        Hutang
                    </x-nav-item>

                    <x-nav-item href="{{ route('sources.index') }}" :active="request()->routeIs('sources.*')" icon="wallet">
                        Sumber
                    </x-nav-item>

                    <x-nav-item href="{{ route('reconciliations.index') }}" :active="request()->routeIs('reconciliations.*')" icon="check-badge">
                        Rekonsil.
                    </x-nav-item>

                    <x-nav-item href="{{ route('reports.index') }}" :active="request()->routeIs('reports.*')" icon="chart">
                        Laporan
                    </x-nav-item>

                    <div class="pt-4 mt-4 border-t border-garis">
                        <x-nav-item href="{{ route('categories.index') }}" :active="request()->routeIs('categories.*')" icon="tags">
                            Kategori
                        </x-nav-item>

                        <x-nav-item href="{{ route('profile') }}" :active="request()->routeIs('profile')" icon="user">
                            Profil
                        </x-nav-item>
                    </div>
                </nav>

                <!-- User dropdown / footer -->
                <div class="p-3 border-t border-garis">
                    <div class="flex items-center justify-between px-3 py-2 rounded-lg bg-kertas text-sm">
                        <div class="truncate">
                            <p class="font-medium text-tinta truncate">{{ auth()->user()?->name ?? 'Pengguna' }}</p>
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

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
                <!-- Top Header -->
                <header class="h-16 shrink-0 bg-permukaan border-b border-garis flex items-center justify-between px-4 sm:px-6">
                    <div class="flex items-center gap-3">
                        <!-- Mobile brand icon -->
                        <div class="lg:hidden text-batik font-bold text-xl flex items-center gap-1">
                            <span>◈</span>
                            <span>{{ config('app.name', 'Kasku') }}</span>
                        </div>
                        @if(isset($header))
                            <div class="hidden lg:block text-xl font-bold text-tinta">
                                {{ $header }}
                            </div>
                        @endif
                    </div>

                    <!-- Header Actions -->
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

                        <!-- Notification bell -->
                        <livewire:notifications.dropdown />

                        <!-- Global Catat Button -->
                        <button
                            type="button"
                            @click="$dispatch('open-transaction-modal')"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-batik text-white text-sm font-semibold hover:opacity-90 transition shadow-xs focus:outline-none focus:ring-2 focus:ring-batik"
                        >
                            <span>+</span>
                            <span>Catat</span>
                        </button>
                    </div>
                </header>

                <!-- Page Content Body -->
                <main class="flex-1 overflow-y-auto pb-20 lg:pb-10 p-4 sm:p-6 lg:p-8">
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

        <!-- Mobile Bottom Navigation (< 768px) -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 bg-permukaan border-t border-garis z-40 flex items-center justify-around h-16 px-2 safe-area-inset-bottom">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex flex-col items-center justify-center flex-1 py-1 text-xs {{ request()->routeIs('dashboard') ? 'text-batik font-bold' : 'text-tinta-redup' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Beranda</span>
            </a>

            <a href="{{ route('transactions.index') }}" wire:navigate class="flex flex-col items-center justify-center flex-1 py-1 text-xs {{ request()->routeIs('transactions.*') ? 'text-batik font-bold' : 'text-tinta-redup' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span>Transaksi</span>
            </a>

            <!-- Big Center (+) Catat Button -->
            <button
                type="button"
                @click="$dispatch('open-transaction-modal')"
                class="flex flex-col items-center justify-center w-12 h-12 -mt-4 rounded-full bg-batik text-white shadow-lg focus:outline-none focus:ring-2 focus:ring-batik"
                aria-label="Catat Transaksi"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            </button>

            <a href="{{ route('savings.index') }}" wire:navigate class="flex flex-col items-center justify-center flex-1 py-1 text-xs {{ request()->routeIs('savings.*') ? 'text-batik font-bold' : 'text-tinta-redup' }}">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Tabungan</span>
            </a>

            <!-- Mobile 'Lainnya' Menu Sheet -->
            <div x-data="{ openMore: false }" class="flex-1 flex flex-col items-center justify-center">
                <button
                    @click="openMore = true"
                    type="button"
                    class="flex flex-col items-center justify-center py-1 text-xs text-tinta-redup"
                >
                    <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span>Lainnya</span>
                </button>

                <x-modal name="mobile-menu-modal" show="false" x-show="openMore" maxWidth="sm" title="Menu Lengkap">
                    <div class="grid grid-cols-2 gap-3 py-2">
                        <a href="{{ route('transfers.index') }}" wire:navigate @click="show = false" class="p-3 rounded-xl border border-garis bg-kertas hover:bg-permukaan text-center text-sm font-medium">
                            Transfer
                        </a>
                        <a href="{{ route('sources.index') }}" wire:navigate @click="show = false" class="p-3 rounded-xl border border-garis bg-kertas hover:bg-permukaan text-center text-sm font-medium">
                            Sumber Saldo
                        </a>
                        <a href="{{ route('budgets.index') }}" wire:navigate @click="show = false" class="p-3 rounded-xl border border-garis bg-kertas hover:bg-permukaan text-center text-sm font-medium">
                            Budget
                        </a>
                        <a href="{{ route('recurring.index') }}" wire:navigate @click="show = false" class="p-3 rounded-xl border border-garis bg-kertas hover:bg-permukaan text-center text-sm font-medium">
                            Berulang
                        </a>
                        <a href="{{ route('debts.index') }}" wire:navigate @click="show = false" class="p-3 rounded-xl border border-garis bg-kertas hover:bg-permukaan text-center text-sm font-medium">
                            Hutang
                        </a>
                        <a href="{{ route('reconciliations.index') }}" wire:navigate @click="show = false" class="p-3 rounded-xl border border-garis bg-kertas hover:bg-permukaan text-center text-sm font-medium">
                            Rekonsil.
                        </a>
                        <a href="{{ route('reports.index') }}" wire:navigate @click="show = false" class="p-3 rounded-xl border border-garis bg-kertas hover:bg-permukaan text-center text-sm font-medium">
                            Laporan
                        </a>
                        <a href="{{ route('categories.index') }}" wire:navigate @click="show = false" class="p-3 rounded-xl border border-garis bg-kertas hover:bg-permukaan text-center text-sm font-medium">
                            Kategori
                        </a>
                        <a href="{{ route('profile') }}" wire:navigate @click="show = false" class="p-3 rounded-xl border border-garis bg-kertas hover:bg-permukaan text-center text-sm font-medium col-span-2">
                            Profil Saya
                        </a>
                    </div>
                </x-modal>
            </div>
        </nav>

        <!-- Global Toast Component -->
        <x-toast />

        <!-- Global Transaction Form Modal Component (Livewire) -->
        @auth
            <livewire:transactions.form-modal />
        @endauth
    </body>
</html>
