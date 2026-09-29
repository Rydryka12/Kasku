<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-tinta font-heading">Riwayat Export</h1>
            <p class="text-sm text-tinta-redup">Daftar berkas laporan keuangan dan transaksi yang pernah Anda unduh.</p>
        </div>
        <div>
            <a
                href="{{ route('reports.index') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-kertas border border-garis text-sm font-medium text-tinta hover:bg-garis/50 transition shadow-xs"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Kembali ke Laporan</span>
            </a>
        </div>
    </div>

    <!-- Table of Export Histories -->
    <div class="bg-permukaan border border-garis rounded-2xl p-5 sm:p-6 shadow-xs space-y-4">
        @if($histories->isEmpty())
            <div class="text-center py-10">
                <div class="w-12 h-12 rounded-full bg-garis/50 text-tinta-redup flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-tinta">Belum ada riwayat export</p>
                <p class="text-xs text-tinta-redup mt-1">Berkas laporan PDF atau spreadsheet Excel yang Anda unduh akan tercatat otomatis di sini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm divide-y divide-garis">
                    <thead>
                        <tr class="text-xs text-tinta-redup uppercase tracking-wider">
                            <th class="py-3 px-3">Tanggal &amp; Waktu</th>
                            <th class="py-3 px-3">Tipe Berkas</th>
                            <th class="py-3 px-3">Nama Berkas</th>
                            <th class="py-3 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-garis">
                        @foreach($histories as $h)
                            <tr class="hover:bg-kertas/50 transition">
                                <td class="py-3 px-3 text-xs text-tinta font-medium whitespace-nowrap">
                                    {{ $h->created_at->translatedFormat('d M Y, H:i') }}
                                </td>
                                <td class="py-3 px-3 text-xs whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full font-medium {{ str_contains($h->type, 'pdf') ? 'bg-keluar/10 text-keluar' : 'bg-masuk/10 text-masuk' }}">
                                        {{ $h->typeLabel() }}
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-xs text-tinta font-mono truncate max-w-xs">
                                    {{ $h->file_name }}
                                </td>
                                <td class="py-3 px-3 text-xs text-right whitespace-nowrap space-x-2">
                                    <a
                                        href="{{ route('exports.download', $h) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-batik text-white font-medium hover:bg-batik-gelap transition shadow-xs"
                                        title="Unduh Ulang"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                        </svg>
                                        <span>Unduh Ulang</span>
                                    </a>

                                    <button
                                        type="button"
                                        wire:click="delete({{ $h->id }})"
                                        wire:confirm="Yakin ingin menghapus riwayat dan berkas export ini?"
                                        class="p-1 rounded-lg text-tinta-redup hover:text-keluar hover:bg-keluar/10 transition"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $histories->links() }}
            </div>
        @endif
    </div>
</div>
