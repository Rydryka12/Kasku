@props([
    'variant' => 'row', // row, panel, chart
    'count' => 1,
])

<div {{ $attributes->merge(['class' => 'space-y-3 animate-pulse motion-reduce:animate-none']) }}>
    @if($variant === 'row')
        @for($i = 0; $i < $count; $i++)
            <div class="h-14 bg-garis/50 rounded-lg w-full flex items-center justify-between px-4">
                <div class="flex items-center gap-3 w-1/2">
                    <div class="w-3 h-3 rounded-full bg-garis"></div>
                    <div class="h-4 bg-garis rounded w-3/4"></div>
                </div>
                <div class="h-4 bg-garis rounded w-24"></div>
            </div>
        @endfor
    @elseif($variant === 'panel')
        <div class="p-6 bg-permukaan border border-garis rounded-2xl space-y-4">
            <div class="h-4 bg-garis rounded w-1/3"></div>
            <div class="h-8 bg-garis rounded w-1/2"></div>
            <div class="h-3 bg-garis rounded w-full"></div>
        </div>
    @elseif($variant === 'chart')
        <div class="p-6 bg-permukaan border border-garis rounded-2xl space-y-4">
            <div class="h-5 bg-garis rounded w-1/4"></div>
            <div class="h-48 bg-garis/40 rounded-xl w-full flex items-end justify-between p-4 gap-2">
                <div class="h-20 bg-garis rounded w-full"></div>
                <div class="h-32 bg-garis rounded w-full"></div>
                <div class="h-16 bg-garis rounded w-full"></div>
                <div class="h-40 bg-garis rounded w-full"></div>
                <div class="h-28 bg-garis rounded w-full"></div>
            </div>
        </div>
    @endif
</div>
