<div class="relative" x-data="{ open: false }" @click.outside="open = false">
    <!-- Bell Button -->
    <button
        @click="open = !open"
        type="button"
        class="relative p-2 rounded-lg text-tinta-redup hover:text-tinta hover:bg-kertas transition focus:outline-none focus:ring-2 focus:ring-batik"
        title="Notifikasi"
        aria-label="Notifikasi"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>

        @if($unreadCount > 0)
            <span class="absolute top-1.5 right-1.5 flex h-4 w-4 items-center justify-center rounded-full bg-keluar text-[10px] font-bold text-white shadow-xs">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown Menu -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 mt-2 w-80 sm:w-96 rounded-2xl bg-permukaan border border-garis shadow-xl z-50 overflow-hidden"
        style="display: none;"
    >
        <div class="p-3 border-b border-garis flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="font-bold text-sm text-tinta font-heading">Notifikasi</span>
                @if($unreadCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-keluar/10 text-keluar">
                        {{ $unreadCount }} belum dibaca
                    </span>
                @endif
            </div>
            @if($unreadCount > 0)
                <button
                    type="button"
                    wire:click="markAllAsRead"
                    class="text-xs text-batik hover:underline font-medium"
                >
                    Tandai semua dibaca
                </button>
            @endif
        </div>

        <div class="max-h-80 overflow-y-auto divide-y divide-garis">
            @forelse($notifications as $n)
                @php
                    $isUnread = $n->unread();
                    $data = $n->data;
                @endphp
                <div
                    wire:click="markAsRead('{{ $n->id }}')"
                    class="p-3.5 hover:bg-kertas/60 cursor-pointer transition flex items-start gap-3 {{ $isUnread ? 'bg-batik/5 dark:bg-batik/10' : '' }}"
                >
                    <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5 {{ $isUnread ? 'bg-keluar/10 text-keluar' : 'bg-garis text-tinta-redup' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-1">
                            <p class="text-xs font-semibold text-tinta truncate">
                                {{ $data['title'] ?? 'Pemberitahuan' }}
                            </p>
                            <span class="text-[10px] text-tinta-redup shrink-0">
                                {{ $n->created_at->diffForHumans(null, true, true) }}
                            </span>
                        </div>
                        <p class="text-xs text-tinta-redup mt-0.5 line-clamp-2">
                            {{ $data['message'] ?? '' }}
                        </p>
                    </div>
                    @if($isUnread)
                        <span class="w-2 h-2 rounded-full bg-batik shrink-0 mt-1.5"></span>
                    @endif
                </div>
            @empty
                <div class="py-8 text-center">
                    <p class="text-xs text-tinta-redup">Tidak ada notifikasi baru.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
