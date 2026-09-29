<x-app-layout>
    <x-slot name="header">
        Ringkasan Keuangan
    </x-slot>

    <div class="space-y-6">
        <!-- Widget 1: Total Saldo & Distribusi (Lazy) -->
        <livewire:dashboard.total-balance-widget />

        <!-- Widget 2: Panel Perlu Perhatian (FR-DSH-06) (Lazy) -->
        <livewire:dashboard.attention-widget />

        <!-- Widget 3: Transaksi Terakhir (Lazy) -->
        <livewire:dashboard.recent-transactions-widget />
    </div>
</x-app-layout>
