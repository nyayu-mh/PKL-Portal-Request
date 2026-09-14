@props(['show' => false, 'title' => null, 'maxWidth' => 'lg', 'closeMethod' => 'resetForm'])

@php
    // Sengaja TANPA prefix responsive (bukan "sm:max-w-...") supaya lebar pop-up selalu
    // terbatas di ukuran layar berapa pun — tidak melebar penuh kiri-kanan.
    $maxWidthClass = match ($maxWidth) {
        'xs' => 'max-w-xs',
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md',
    };
@endphp

@if ($show)
    <div
        x-data
        @keydown.escape.window="$wire.{{ $closeMethod }}()"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/50 px-4 py-8"
    >
        <div @click.outside="$wire.{{ $closeMethod }}()" class="w-full {{ $maxWidthClass }} rounded-2xl bg-white shadow-xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <h3 class="text-base font-semibold text-slate-900">{{ $title }}</h3>
                <button type="button" wire:click="{{ $closeMethod }}" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div class="max-h-[75vh] overflow-y-auto px-5 py-5">
                {{ $slot }}
            </div>
        </div>
    </div>
@endif
