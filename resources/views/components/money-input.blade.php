@props([
    'placeholder' => '0',
    'id' => null,
])

<div
    x-data="{
        displayVal: '',
        rawVal: @entangle($attributes->wire('model')),
        init() {
            this.formatDisplay();
            this.$watch('rawVal', (newVal) => {
                if (newVal === null || newVal === undefined || newVal === '') {
                    this.displayVal = '';
                } else {
                    const formatted = new Intl.NumberFormat('id-ID').format(newVal);
                    if (this.displayVal.replace(/\D/g, '') !== String(newVal)) {
                        this.displayVal = formatted;
                    }
                }
            });
        },
        formatDisplay() {
            if (this.rawVal !== null && this.rawVal !== undefined && this.rawVal !== '') {
                this.displayVal = new Intl.NumberFormat('id-ID').format(this.rawVal);
            } else {
                this.displayVal = '';
            }
        },
        handleInput(e) {
            let digits = e.target.value.replace(/\D/g, '');
            if (digits === '') {
                this.rawVal = null;
                this.displayVal = '';
                return;
            }
            let intVal = parseInt(digits, 10);
            this.rawVal = intVal;
            this.displayVal = new Intl.NumberFormat('id-ID').format(intVal);
        }
    }"
    class="relative rounded-md"
>
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <span class="text-tinta-redup sm:text-sm font-semibold">Rp</span>
    </div>
    <input
        type="text"
        inputmode="numeric"
        :id="$id('money-input')"
        :value="displayVal"
        @input="handleInput"
        placeholder="{{ $placeholder }}"
        {{ $attributes->whereDoesntStartWith('wire:model')->merge([
            'class' => 'block w-full rounded-lg border border-garis bg-permukaan pl-10 pr-3 py-2 text-tinta text-base sm:text-sm tabular-nums text-right focus:border-batik focus:ring-2 focus:ring-batik focus:outline-none transition'
        ]) }}
    />
</div>
