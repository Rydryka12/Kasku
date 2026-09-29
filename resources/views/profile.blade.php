<x-app-layout>
<x-slot name="header">
        Profil Saya
    </x-slot>

    <div class="space-y-6">
        <!-- Bagian 1: Informasi Profil -->
        <div class="p-6 bg-permukaan border border-garis rounded-2xl">
            <div class="max-w-xl">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <!-- Bagian 2: Ubah Password -->
        <div class="p-6 bg-permukaan border border-garis rounded-2xl">
            <div class="max-w-xl">
                <livewire:profile.update-password-form />
            </div>
        </div>

        <!-- Bagian 3: Tampilan & Tema -->
        <div class="p-6 bg-permukaan border border-garis rounded-2xl">
            <div class="max-w-xl">
                @include('livewire.profile.theme-preference')
            </div>
        </div>

        <!-- Bagian 4: Hapus Akun -->
        <div class="p-6 bg-permukaan border border-garis rounded-2xl">
            <div class="max-w-xl">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</x-app-layout>
