<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kasku - Kendali Penuh Uangmu</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- AOS Animation CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Design Tokens dari DESIGN.md */
        :root {
            --kertas: #F5F6F3;
            --permukaan: #FFFFFF;
            --tinta: #17212B;
            --tinta-redup: #5B6772;
            --garis: #DDE1DC;
            --batik: #2B3A8C;
            --masuk: #0B7A55;
            --keluar: #B93A2B;
            --kunyit: #B7791F;
        }
        .dark {
            --kertas: #0F1416;
            --permukaan: #171E21;
            --tinta: #E7ECEE;
            --tinta-redup: #93A0A8;
            --garis: #2A3438;
            --batik: #8FA0F2;
            --masuk: #4CC79B;
            --keluar: #F08A7C;
            --kunyit: #E6B450;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="antialiased text-[color:var(--tinta)] bg-[color:var(--kertas)] selection:bg-[color:var(--batik)] selection:text-white overflow-x-hidden">
    
    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 bg-[color:var(--kertas)]/80 backdrop-blur-md border-b border-[color:var(--garis)] transition-all">
        <div class="max-w-7xl mx-auto px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-2" data-aos="fade-down" data-aos-duration="500">
                <div class="size-8 rounded-lg bg-[color:var(--batik)] flex items-center justify-center text-white font-bold text-xl">K</div>
                <span class="text-2xl font-extrabold tracking-tight text-[color:var(--batik)]">Kasku.</span>
            </div>
            
            @if (Route::has('login'))
                <div class="flex items-center gap-3 sm:gap-4" data-aos="fade-down" data-aos-duration="500" data-aos-delay="100">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-[color:var(--tinta)] hover:text-[color:var(--batik)] transition-colors">
                            Masuk Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center justify-center px-5 py-2.5 text-sm font-semibold text-[color:var(--tinta)] hover:text-[color:var(--batik)] transition-colors">
                            Masuk
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-6 py-2.5 text-sm font-semibold rounded-full bg-[color:var(--batik)] text-white hover:opacity-90 transition-opacity shadow-sm">
                                Daftar Gratis
                            </a>
                        @endif
                    @endauth
                </div>
            @endif
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 px-6 max-w-7xl mx-auto flex flex-col items-center text-center">
        <div data-aos="zoom-in" data-aos-duration="800">
            <span class="inline-block py-1.5 px-4 rounded-full bg-[color:var(--batik)]/10 text-[color:var(--batik)] text-sm font-semibold mb-6 border border-[color:var(--batik)]/20">
                Pencatatan Keuangan Pribadi v1.1
            </span>
        </div>
        
        <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold tracking-tight mb-6 max-w-4xl leading-[1.1]" data-aos="fade-up" data-aos-delay="100">
            Uangmu ada di mana saja? <br class="hidden lg:block">
            <span class="text-[color:var(--batik)]">Temukan dalam sekali lihat.</span>
        </h1>
        
        <p class="text-lg sm:text-xl text-[color:var(--tinta-redup)] max-w-2xl mb-10 leading-relaxed" data-aos="fade-up" data-aos-delay="200">
            Buku kas yang tenang dan rapi. Kelola Cash, e-Wallet, Rekening Bank, hingga Target Tabungan tanpa pusing. Catat pengeluaranmu dalam hitungan detik.
        </p>
        
        <div class="flex flex-col sm:flex-row items-center gap-4 w-full justify-center" data-aos="fade-up" data-aos-delay="300">
            <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-semibold rounded-full bg-[color:var(--batik)] text-white hover:scale-105 transition-transform shadow-lg">
                Mulai Catat Sekarang — Gratis
            </a>
            <a href="#fitur" class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 text-base font-semibold rounded-full bg-transparent border-2 border-[color:var(--garis)] text-[color:var(--tinta)] hover:border-[color:var(--tinta)] transition-colors">
                Pelajari Fitur
            </a>
        </div>

        <!-- UI Mockup / Visual Hook -->
        <div class="w-full max-w-4xl mt-20 p-6 sm:p-8 bg-[color:var(--permukaan)] rounded-2xl shadow-sm border border-[color:var(--garis)] text-left relative overflow-hidden" data-aos="fade-up" data-aos-delay="500">
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-[color:var(--batik)] to-[color:var(--kunyit)]"></div>
            <p class="text-[13px] font-semibold text-[color:var(--tinta-redup)] uppercase tracking-wider mb-2">Total Kekayaan</p>
            <h2 class="text-3xl font-extrabold tabular-nums tracking-tight mb-6">Rp 12.450.000</h2>
            
            <!-- Signature App Element: Bar Proporsi -->
            <div class="w-full h-3 sm:h-4 flex rounded-full overflow-hidden mb-4">
                <div class="h-full bg-[color:var(--batik)]" style="width: 55%;" title="BCA"></div>
                <div class="h-full bg-[color:var(--masuk)]" style="width: 25%;" title="Tabungan"></div>
                <div class="h-full bg-[color:var(--kunyit)]" style="width: 15%;" title="Gopay"></div>
                <div class="h-full bg-[color:var(--keluar)]" style="width: 5%;" title="Cash"></div>
            </div>
            
            <div class="flex flex-wrap gap-4 text-sm font-medium">
                <div class="flex items-center gap-2"><span class="size-3 rounded-full bg-[color:var(--batik)]"></span> BCA</div>
                <div class="flex items-center gap-2"><span class="size-3 rounded-full bg-[color:var(--masuk)]"></span> Tabungan</div>
                <div class="flex items-center gap-2"><span class="size-3 rounded-full bg-[color:var(--kunyit)]"></span> Gopay</div>
                <div class="flex items-center gap-2"><span class="size-3 rounded-full bg-[color:var(--keluar)]"></span> Cash</div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="fitur" class="py-20 bg-[color:var(--permukaan)] border-y border-[color:var(--garis)]">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16" data-aos="fade-up">
                <h2 class="text-3xl lg:text-4xl font-bold mb-4">Dirancang untuk kecepatan dan ketenangan.</h2>
                <p class="text-[color:var(--tinta-redup)] text-lg">Tidak ada grafik yang membingungkan, hanya data yang kamu butuhkan untuk mengambil keputusan keuangan yang cerdas.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <!-- Feature 1 -->
                <div class="p-8 rounded-2xl bg-[color:var(--kertas)] border border-[color:var(--garis)] hover:border-[color:var(--batik)] transition-colors group" data-aos="fade-up" data-aos-delay="100">
                    <div class="size-12 rounded-full bg-[color:var(--permukaan)] border border-[color:var(--garis)] flex items-center justify-center text-[color:var(--batik)] mb-6 group-hover:scale-110 transition-transform">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Pencatatan < 10 Detik</h3>
                    <p class="text-[color:var(--tinta-redup)] leading-relaxed">Antarmuka yang bersih memastikan kamu bisa mencatat pemasukan dan pengeluaran secara kilat. Termasuk kalkulasi pajak opsional secara otomatis[cite: 2, 3].</p>
                </div>

                <!-- Feature 2 -->
                <div class="p-8 rounded-2xl bg-[color:var(--kertas)] border border-[color:var(--garis)] hover:border-[color:var(--batik)] transition-colors group" data-aos="fade-up" data-aos-delay="200">
                    <div class="size-12 rounded-full bg-[color:var(--permukaan)] border border-[color:var(--garis)] flex items-center justify-center text-[color:var(--kunyit)] mb-6 group-hover:scale-110 transition-transform">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Multi-Sumber Saldo</h3>
                    <p class="text-[color:var(--tinta-redup)] leading-relaxed">Catat uangmu di Cash, e-Wallet (Gopay, Dana), hingga Rekening Bank. Lakukan transfer antar dompet dengan mudah tanpa kehilangan jejak[cite: 2].</p>
                </div>

                <!-- Feature 3 -->
                <div class="p-8 rounded-2xl bg-[color:var(--kertas)] border border-[color:var(--garis)] hover:border-[color:var(--batik)] transition-colors group" data-aos="fade-up" data-aos-delay="300">
                    <div class="size-12 rounded-full bg-[color:var(--permukaan)] border border-[color:var(--garis)] flex items-center justify-center text-[color:var(--masuk)] mb-6 group-hover:scale-110 transition-transform">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Amankan Dana Impian</h3>
                    <p class="text-[color:var(--tinta-redup)] leading-relaxed">Pisahkan dana tabungan liburan atau gadget baru dari saldo tersedia agar tidak tidak terpakai. Pantau progress persentasenya setiap saat[cite: 2].</p>
                </div>

                <!-- Feature 4 -->
                <div class="p-8 rounded-2xl bg-[color:var(--kertas)] border border-[color:var(--garis)] hover:border-[color:var(--batik)] transition-colors group" data-aos="fade-up" data-aos-delay="400">
                    <div class="size-12 rounded-full bg-[color:var(--permukaan)] border border-[color:var(--garis)] flex items-center justify-center text-[color:var(--keluar)] mb-6 group-hover:scale-110 transition-transform">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Rem Otomatis & Budget</h3>
                    <p class="text-[color:var(--tinta-redup)] leading-relaxed">Tentukan batas pengeluaran bulanan. Kasku akan memberi peringatan jika pengeluaranmu sudah menyentuh 80% dari batas kategori tersebut[cite: 2].</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Bottom CTA -->
    <section class="py-24 text-center px-6">
        <div class="max-w-3xl mx-auto" data-aos="zoom-in">
            <h2 class="text-3xl lg:text-4xl font-extrabold mb-6">Mulai rapikan keuanganmu hari ini.</h2>
            <p class="text-[color:var(--tinta-redup)] text-lg mb-10">
                Bergabunglah dan pantau kekayaan, kelola hutang-piutang, serta export laporan dalam format Excel/PDF hanya dengan beberapa klik[cite: 2].
            </p>
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-10 py-4 text-lg font-bold rounded-full bg-[color:var(--batik)] text-white hover:scale-105 transition-transform shadow-xl shadow-[color:var(--batik)]/20">
                Daftar Sekarang
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-10 border-t border-[color:var(--garis)] text-center text-[14px] text-[color:var(--tinta-redup)] bg-[color:var(--permukaan)]">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="font-bold text-[color:var(--tinta)] text-base">Kasku.</div>
            <div>Aplikasi Tabungan & Catatan Keuangan Pribadi v1.1[cite: 2]</div>
            <div>Laravel v{{ Illuminate\Foundation\Application::VERSION }}</div>
        </div>
    </footer>

    <!-- AOS Animation JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            once: true,
            offset: 50,
        });
    </script>
</body>
</html>