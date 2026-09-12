@props(['label', 'value', 'color' => 'indigo', 'icon' => null, 'href' => null])

@php
    $ring = match ($color) {
        'indigo' => 'from-indigo-500 to-violet-500',
        'emerald' => 'from-emerald-500 to-teal-500',
        'amber' => 'from-amber-500 to-orange-500',
        'rose' => 'from-rose-500 to-pink-500',
        default => 'from-slate-500 to-slate-700',
    };

    $cardClass = 'block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm'
        . ($href ? ' transition hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500' : '');
@endphp

@if ($href)
    <a href="{{ $href }}" class="{{ $cardClass }}">
@else
    <div class="{{ $cardClass }}">
@endif
        <div class="flex items-center justify-between">
            <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br {{ $ring }} text-white shadow">
                {{ $icon }}
            </div>
        </div>
        <p class="mt-3 text-2xl font-semibold text-slate-900">{{ $value }}</p>
@if ($href)
    </a>
@else
    </div>
@endif
