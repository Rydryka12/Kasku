<div>
    @if(count($items) > 0)
        <div class="bg-permukaan border border-garis rounded-2xl p-5 sm:p-6 shadow-xs space-y-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                <h2 class="text-base font-bold text-tinta font-heading">Perlu Perhatian</h2>
                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-700 dark:text-amber-400 font-semibold">
                    {{ count($items) }} hal
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-1">
                @foreach($items as $item)
                    <div class="p-3.5 rounded-xl border {{ $item['severity'] === 'danger' ? 'bg-keluar/5 border-keluar/30' : 'bg-amber-500/5 border-amber-500/30' }} flex flex-col justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-xs font-bold text-tinta truncate">
                                    {{ $item['title'] }}
                                </span>
                                <span class="text-[10px] font-semibold uppercase tracking-wider px-1.5 py-0.5 rounded {{ $item['severity'] === 'danger' ? 'bg-keluar/15 text-keluar' : 'bg-amber-500/15 text-amber-700 dark:text-amber-300' }}">
                                    {{ $item['severity'] === 'danger' ? 'Penting' : 'Peringatan' }}
                                </span>
                            </div>
                            <p class="text-xs text-tinta-redup leading-relaxed">
                                {{ $item['message'] }}
                            </p>
                        </div>

                        <div class="pt-1">
                            <a
                                href="{{ $item['url'] }}"
                                wire:navigate
                                class="inline-flex items-center gap-1 text-xs font-semibold text-batik hover:underline"
                            >
                                <span>{{ $item['action_label'] }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
