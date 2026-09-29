<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = \App\Enums\Role::User;
        $validated['is_active'] = true;

        event(new Registered($user = User::create($validated)));

        // Salin kategori default ke user baru saat registrasi (K-18)
        app(\App\Services\CategoryService::class)->copyDefaultCategoriesToUser($user);

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="min-h-screen flex flex-col items-center justify-center bg-[color:var(--kertas)] px-4 py-8">
    
    <!-- Tombol Kembali ke Landing -->
    <div class="w-full max-w-[400px] mb-6">
        <a href="{{ url('/') }}" wire:navigate class="inline-flex items-center gap-2 text-[13px] font-semibold text-[color:var(--tinta-redup)] hover:text-[color:var(--batik)] transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--batik)] rounded-sm">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Beranda
        </a>
    </div>

    <!-- Panel Form Maks 400px -->
    <div class="w-full max-w-[400px] bg-[color:var(--permukaan)] rounded-[14px] shadow-sm border border-[color:var(--garis)] p-6 sm:p-8">
        
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center size-10 rounded-lg bg-[color:var(--batik)] text-white font-bold text-2xl mb-4">K</div>
            <h1 class="text-2xl font-bold text-[color:var(--tinta)]">Buat Akun Kasku</h1>
            <p class="text-sm text-[color:var(--tinta-redup)] mt-1">Langkah pertama merapikan keuanganmu.</p>
        </div>

        <form wire:submit="register" class="space-y-5">
            <!-- Name -->
            <div>
                <label for="name" class="block text-[13px] font-bold text-[color:var(--tinta)] mb-1">Nama Lengkap</label>
                <input wire:model="name" id="name" type="text" required autofocus autocomplete="name"
                    class="h-10 w-full px-3 rounded-lg border border-[color:var(--garis)] bg-[color:var(--permukaan)] text-[15px] text-[color:var(--tinta)] placeholder:text-[color:var(--garis)] focus:outline-none focus:ring-2 focus:ring-[color:var(--batik)] focus:ring-offset-2 focus:border-[color:var(--batik)] disabled:opacity-50 transition-shadow" />
                
                @error('name')
                    <div class="flex items-start gap-1.5 mt-2 text-[color:var(--keluar)]">
                        <svg class="size-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-[13px] font-medium">{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-[13px] font-bold text-[color:var(--tinta)] mb-1">Email</label>
                <input wire:model="email" id="email" type="email" required autocomplete="username"
                    class="h-10 w-full px-3 rounded-lg border border-[color:var(--garis)] bg-[color:var(--permukaan)] text-[15px] text-[color:var(--tinta)] placeholder:text-[color:var(--garis)] focus:outline-none focus:ring-2 focus:ring-[color:var(--batik)] focus:ring-offset-2 focus:border-[color:var(--batik)] disabled:opacity-50 transition-shadow" />
                
                @error('email')
                    <div class="flex items-start gap-1.5 mt-2 text-[color:var(--keluar)]">
                        <svg class="size-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-[13px] font-medium">{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-[13px] font-bold text-[color:var(--tinta)] mb-1">Password</label>
                <input wire:model="password" id="password" type="password" required autocomplete="new-password"
                    class="h-10 w-full px-3 rounded-lg border border-[color:var(--garis)] bg-[color:var(--permukaan)] text-[15px] text-[color:var(--tinta)] placeholder:text-[color:var(--garis)] focus:outline-none focus:ring-2 focus:ring-[color:var(--batik)] focus:ring-offset-2 focus:border-[color:var(--batik)] disabled:opacity-50 transition-shadow" />
                
                @error('password')
                    <div class="flex items-start gap-1.5 mt-2 text-[color:var(--keluar)]">
                        <svg class="size-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-[13px] font-medium">{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-[13px] font-bold text-[color:var(--tinta)] mb-1">Konfirmasi Password</label>
                <input wire:model="password_confirmation" id="password_confirmation" type="password" required autocomplete="new-password"
                    class="h-10 w-full px-3 rounded-lg border border-[color:var(--garis)] bg-[color:var(--permukaan)] text-[15px] text-[color:var(--tinta)] placeholder:text-[color:var(--garis)] focus:outline-none focus:ring-2 focus:ring-[color:var(--batik)] focus:ring-offset-2 focus:border-[color:var(--batik)] disabled:opacity-50 transition-shadow" />
                
                @error('password_confirmation')
                    <div class="flex items-start gap-1.5 mt-2 text-[color:var(--keluar)]">
                        <svg class="size-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-[13px] font-medium">{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <!-- Submit Button dengan Loading State -->
            <!-- <div class="pt-2">
                <button type="submit" wire:loading.attr="disabled" class="w-full h-10 inline-flex items-center justify-center px-4 rounded-lg bg-[color:var(--batik)] text-white text-[15px] font-bold hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--batik)] transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="register">Daftar</span>
                    <span wire:loading wire:target="register" class="inline-flex items-center gap-2">
                        <svg class="animate-spin size-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memproses...
                    </span>
                </button>
            </div> -->
            <div class="pt-2">
                <button type="submit" wire:loading.attr="disabled" class="w-full h-10 flex items-center justify-center gap-2 px-4 rounded-lg bg-[#2B3A8C] text-white text-[15px] font-bold hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2B3A8C] transition disabled:opacity-70 disabled:cursor-not-allowed">
                    <svg wire:loading wire:target="register" class="animate-spin size-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Daftar</span>
                </button>
            </div>
        </form>

        <!-- Tautan Login -->
        <div class="mt-8 text-center text-[13px] text-[color:var(--tinta-redup)] border-t border-[color:var(--garis)] pt-6">
            Sudah punya akun? 
            <a href="{{ route('login') }}" wire:navigate class="font-bold text-[color:var(--batik)] hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--batik)] rounded-sm transition">
                Masuk di sini
            </a>
        </div>
    </div>
</div>