<div
    x-data="{
        messages: [],
        add(detail) {
            let id = Date.now() + Math.random();
            let msg = {
                id: id,
                text: typeof detail === 'string' ? detail : detail.message,
                type: detail.type || 'info',
            };
            this.messages.push(msg);
            setTimeout(() => {
                this.remove(id);
            }, 4000);
        },
        remove(id) {
            this.messages = this.messages.filter(m => m.id !== id);
        }
    }"
    @toast.window="add($event.detail)"
    class="fixed z-50 pointer-events-none flex flex-col gap-2 p-4 top-4 right-4 max-w-sm w-full"
>
    <template x-for="msg in messages" :key="msg.id">
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="pointer-events-auto bg-permukaan border border-garis rounded-xl p-3.5 shadow-lg flex items-center justify-between text-sm text-tinta gap-3"
            :class="{
                'border-l-4 border-l-masuk': msg.type === 'success',
                'border-l-4 border-l-keluar': msg.type === 'error',
                'border-l-4 border-l-batik': msg.type === 'info',
                'border-l-4 border-l-kunyit': msg.type === 'warning',
            }"
        >
            <div class="flex items-center gap-2">
                <span x-text="msg.text" class="font-medium"></span>
            </div>
            <button @click="remove(msg.id)" class="text-tinta-redup hover:text-tinta p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
