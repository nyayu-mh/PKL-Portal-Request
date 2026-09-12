@props(['color' => 'gray', 'label'])

@php
    $classes = match ($color) {
        'gray' => 'bg-slate-100 text-slate-700 ring-slate-600/10',
        'amber' => 'bg-amber-100 text-amber-800 ring-amber-600/10',
        'blue' => 'bg-blue-100 text-blue-700 ring-blue-600/10',
        'indigo' => 'bg-indigo-100 text-indigo-700 ring-indigo-600/10',
        'emerald' => 'bg-emerald-100 text-emerald-700 ring-emerald-600/10',
        'orange' => 'bg-orange-100 text-orange-700 ring-orange-600/10',
        'rose' => 'bg-rose-100 text-rose-700 ring-rose-600/10',
        default => 'bg-slate-100 text-slate-700 ring-slate-600/10',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset $classes"]) }}>
    {{ $label }}
</span>
