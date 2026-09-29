<div>
    <x-slot name="header">
        Kelola Pengguna
    </x-slot>

    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama atau email..."
                class="rounded-lg border border-garis bg-permukaan px-3 py-2 text-sm text-tinta max-w-sm w-full focus:border-batik focus:ring-2 focus:ring-batik"
            />
        </div>

        <div class="bg-permukaan border border-garis rounded-2xl overflow-hidden divide-y divide-garis">
            <div class="p-4 bg-kertas/50 grid grid-cols-12 text-xs font-semibold text-tinta-redup uppercase tracking-wider">
                <span class="col-span-5">Nama & Email</span>
                <span class="col-span-3">Peran</span>
                <span class="col-span-2">Status</span>
                <span class="col-span-2 text-end">Aksi</span>
            </div>

            @forelse($users as $u)
                <div class="p-4 grid grid-cols-12 items-center hover:bg-kertas/30 transition text-sm">
                    <div class="col-span-5">
                        <p class="font-bold text-tinta">{{ $u->name }}</p>
                        <p class="text-xs text-tinta-redup">{{ $u->email }}</p>
                    </div>

                    <div class="col-span-3">
                        <x-badge :variant="$u->isAdmin() ? 'batik' : 'default'">
                            {{ $u->role->label() }}
                        </x-badge>
                    </div>

                    <div class="col-span-2">
                        <x-badge :variant="$u->is_active ? 'aktif' : 'gagal'">
                            {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                        </x-badge>
                    </div>

                    <div class="col-span-2 text-end">
                        @if($u->id === auth()->id())
                            <button
                                type="button"
                                disabled
                                title="Tidak dapat menonaktifkan akun sendiri (FR-ADM-06)"
                                class="px-3 py-1 text-xs font-semibold rounded-lg border border-garis text-tinta-redup opacity-50 cursor-not-allowed"
                            >
                                Akun Anda
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="toggleActive({{ $u->id }})"
                                wire:confirm="Apakah Anda yakin ingin {{ $u->is_active ? 'menonaktifkan' : 'mengaktifkan' }} pengguna {{ $u->name }}?"
                                class="px-3 py-1 text-xs font-semibold rounded-lg border border-garis {{ $u->is_active ? 'text-keluar hover:bg-keluar/10' : 'text-masuk hover:bg-masuk/10' }} transition"
                            >
                                {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8">
                    <x-empty title="Tidak ada pengguna" description="Tidak ditemukan pengguna dengan kriteria pencarian ini." />
                </div>
            @endforelse
        </div>

        <div>
            {{ $users->links() }}
        </div>
    </div>
</div>
