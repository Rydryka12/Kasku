<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $defaultRoute = Auth::user()->isAdmin() ? route('admin.dashboard', absolute: false) : route('dashboard', absolute: false);

        $this->redirectIntended(default: $defaultRoute, navigate: true);
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
            <h1 class="text-2xl font-bold text-[color:var(--tinta)]">Masuk ke Kasku</h1>
            <p class="text-sm text-[color:var(--tinta-redup)] mt-1">Kelola uangmu dengan tenang dan rapi.</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login" class="space-y-5">
            <!-- Email Address -->
            <div>
                <label for="email" class="block text-[13px] font-bold text-[color:var(--tinta)] mb-1">Email</label>
                <input wire:model="form.email" id="email" type="email" required autofocus autocomplete="username"
                    class="h-10 w-full px-3 rounded-lg border border-[color:var(--garis)] bg-[color:var(--permukaan)] text-[15px] text-[color:var(--tinta)] placeholder:text-[color:var(--garis)] focus:outline-none focus:ring-2 focus:ring-[color:var(--batik)] focus:ring-offset-2 focus:border-[color:var(--batik)] disabled:opacity-50 transition-shadow" />
                
                <!-- Tampilan Error Sesuai DESIGN.md (dengan Ikon) -->
                @error('form.email')
                    <div class="flex items-start gap-1.5 mt-2 text-[color:var(--keluar)]">
                        <svg class="size-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-[13px] font-medium">{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-[13px] font-bold text-[color:var(--tinta)] mb-1">Password</label>
                <input wire:model="form.password" id="password" type="password" required autocomplete="current-password"
                    class="h-10 w-full px-3 rounded-lg border border-[color:var(--garis)] bg-[color:var(--permukaan)] text-[15px] text-[color:var(--tinta)] placeholder:text-[color:var(--garis)] focus:outline-none focus:ring-2 focus:ring-[color:var(--batik)] focus:ring-offset-2 focus:border-[color:var(--batik)] disabled:opacity-50 transition-shadow" />
                
                @error('form.password')
                    <div class="flex items-start gap-1.5 mt-2 text-[color:var(--keluar)]">
                        <svg class="size-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span class="text-[13px] font-medium">{{ $message }}</span>
                    </div>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between mt-4">
                <label for="remember" class="inline-flex items-center gap-2 cursor-pointer">
                    <input wire:model="form.remember" id="remember" type="checkbox" class="size-4 rounded border-[color:var(--garis)] text-[color:var(--batik)] shadow-sm focus:ring-[color:var(--batik)] focus:ring-offset-2">
                    <span class="text-[13px] font-medium text-[color:var(--tinta)]">Ingat saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" wire:navigate class="text-[13px] font-semibold text-[color:var(--batik)] hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--batik)] rounded-sm transition">
                        Lupa password?
                    </a>
                @endif
            </div>

            <!-- Submit Button dengan Loading State -->
            <!-- <div class="pt-2">
                <button type="submit" wire:loading.attr="disabled" class="w-full h-10 inline-flex items-center justify-center px-4 rounded-lg bg-[color:var(--batik)] text-white text-[15px] font-bold hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--batik)] transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="login">Masuk</span>
                    <span wire:loading wire:target="login" class="inline-flex items-center gap-2">
                        <svg class="animate-spin size-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memproses...
                    </span>
                </button>
            </div> -->
            <div class="pt-2">
                <button type="submit" wire:loading.attr="disabled" class="w-full h-10 flex items-center justify-center gap-2 px-4 rounded-lg bg-[#2B3A8C] text-white text-[15px] font-bold hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#2B3A8C] transition disabled:opacity-70 disabled:cursor-not-allowed">
                    <svg wire:loading wire:target="login" class="animate-spin size-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    <span>Masuk</span>
                </button>
            </div>
        </form>

        <!-- Tautan Pendaftaran -->
        @if (Route::has('register'))
            <div class="mt-8 text-center text-[13px] text-[color:var(--tinta-redup)] border-t border-[color:var(--garis)] pt-6">
                Belum punya akun? 
                <a href="{{ route('register') }}" wire:navigate class="font-bold text-[color:var(--batik)] hover:underline focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[color:var(--batik)] rounded-sm transition">
                    Daftar di sini
                </a>
            </div>
        @endif
    </div>
</div>