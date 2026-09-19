@props(['user', 'empty' => null])

{{-- Lencana brand milik user (Wookey Wight / So Honey Jr / Semua Brand). Kosong = tidak tampil, kecuali $empty diisi. --}}
@if ($user?->brand)
    <x-status-badge :color="$user->brandBadgeColor()" :label="$user->brandLabel()" {{ $attributes }} />
@elseif ($empty)
    <span {{ $attributes->merge(['class' => 'text-xs text-slate-400']) }}>{{ $empty }}</span>
@endif
