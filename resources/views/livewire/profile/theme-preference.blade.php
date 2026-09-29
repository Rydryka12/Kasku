<section>
    <header class="mb-4">
        <h2 class="text-base font-bold text-tinta">
            Tampilan & Tema
        </h2>
        <p class="mt-1 text-sm text-tinta-redup">
            Pilih preferensi tema warna antarmuka aplikasi.
        </p>
    </header>

    <div
        x-data="{
            theme: localStorage.theme || 'system',
            setTheme(val) {
                this.theme = val;
                if (val === 'system') {
                    localStorage.removeItem('theme');
                    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } else if (val === 'dark') {
                    localStorage.theme = 'dark';
                    document.documentElement.classList.add('dark');
                } else {
                    localStorage.theme = 'light';
                    document.documentElement.classList.remove('dark');
                }
            }
        }"
        class="grid grid-cols-3 gap-3 max-w-md"
    >
        <button
            type="button"
            @click="setTheme('light')"
            class="flex flex-col items-center justify-center p-3 rounded-xl border text-sm font-medium transition"
            :class="theme === 'light' ? 'border-batik bg-batik/10 text-batik font-bold' : 'border-garis bg-permukaan text-tinta hover:bg-kertas'"
        >
            <svg class="w-5 h-5 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <span>Terang</span>
        </button>

        <button
            type="button"
            @click="setTheme('dark')"
            class="flex flex-col items-center justify-center p-3 rounded-xl border text-sm font-medium transition"
            :class="theme === 'dark' ? 'border-batik bg-batik/10 text-batik font-bold' : 'border-garis bg-permukaan text-tinta hover:bg-kertas'"
        >
            <svg class="w-5 h-5 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            <span>Gelap</span>
        </button>

        <button
            type="button"
            @click="setTheme('system')"
            class="flex flex-col items-center justify-center p-3 rounded-xl border text-sm font-medium transition"
            :class="theme === 'system' ? 'border-batik bg-batik/10 text-batik font-bold' : 'border-garis bg-permukaan text-tinta hover:bg-kertas'"
        >
            <svg class="w-5 h-5 mb-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <span>Sistem</span>
        </button>
    </div>
</section>
