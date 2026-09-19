@props([
    'model',                      // nama properti Livewire yang menyimpan pilihan (mis. "divisi" atau "master_divisi_id")
    'manualModel' => null,        // properti untuk teks ketik manual; kosong = sama dengan $model
    'options' => [],              // [nilai => label]
    'current' => null,            // nilai $model saat ini
    'currentManual' => null,      // nilai $manualModel saat ini (kalau beda properti)
    'placeholder' => '— Pilih divisi —',
])

@php
    $manualModel ??= $model;
    $sameProperty = $manualModel === $model;
    $isManual = $sameProperty
        ? filled($current) && ! in_array((string) $current, array_map('strval', array_keys($options)), true)
        : filled($currentManual);
    $fieldClass = 'w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500';
@endphp

{{--
    Dropdown divisi + opsi "Ketik manual". Pilih dari daftar (data divisi yang sudah ada) atau ketik sendiri kalau belum ada.
    Nilai dikirim ke Livewire lewat $wire.set(), jadi "__manual__" tidak pernah ikut tersimpan.
--}}
<div x-data="{ manual: @js($isManual) }" class="space-y-1.5">
    <select
        x-show="!manual"
        x-ref="sel"
        @if ($isManual) style="display: none" @endif
        class="{{ $fieldClass }}"
        @change="
            if ($event.target.value === '__manual__') {
                manual = true;
                $event.target.value = '';
                $wire.set('{{ $model }}', '', false);
                $nextTick(() => $refs.txt.focus());
            } else {
                $wire.set('{{ $model }}', $event.target.value, false);
            }
        "
    >
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $value => $label)
            <option value="{{ $value }}" @selected(! $isManual && (string) $current === (string) $value)>{{ $label }}</option>
        @endforeach
        <option value="__manual__">✏️ Ketik manual…</option>
    </select>

    <div x-show="manual" class="space-y-1" @unless ($isManual) style="display: none" @endunless>
        <input
            type="text"
            x-ref="txt"
            wire:model="{{ $manualModel }}"
            placeholder="Ketik nama divisi…"
            class="{{ $fieldClass }}"
        >
        <button
            type="button"
            class="text-xs font-medium text-indigo-600 hover:underline"
            @click="
                manual = false;
                $refs.sel.value = '';
                $wire.set('{{ $manualModel }}', '', false);
                $wire.set('{{ $model }}', '', false);
            "
        >← Pilih dari daftar divisi</button>
    </div>
</div>
