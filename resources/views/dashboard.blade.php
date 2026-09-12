<x-layouts.app title="Dashboard">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Halo, {{ auth()->user()->name }} 👋</h2>
        <p class="mt-1 text-sm text-slate-500">Ringkasan request Anda di Portal Request Brilliant Think Center.</p>
    </div>

    <div class="mb-8">
        <a href="{{ route('erf.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm font-semibold text-slate-700 hover:text-indigo-600">
            Request ERF
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </a>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <x-stat-card label="Total ERF" :value="$erfStats['total']" color="indigo" :href="route('erf.index')" />
            <x-stat-card label="Menunggu" :value="$erfStats['pending']" color="amber" :href="route('erf.index')" />
            <x-stat-card label="Sedang Proses" :value="$erfStats['on_progress']" color="indigo" :href="route('erf.index')" />
            <x-stat-card label="Selesai" :value="$erfStats['completed']" color="emerald" :href="route('erf.index')" />
        </div>
    </div>

    <div class="mb-8">
        <a href="{{ route('ga.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm font-semibold text-slate-700 hover:text-indigo-600">
            Request General Affair
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </a>
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <x-stat-card label="Total GA" :value="$gaStats['total']" color="indigo" :href="route('ga.index')" />
            <x-stat-card label="Menunggu" :value="$gaStats['pending']" color="amber" :href="route('ga.index')" />
            <x-stat-card label="Sedang Proses" :value="$gaStats['on_progress']" color="indigo" :href="route('ga.index')" />
            <x-stat-card label="Selesai" :value="$gaStats['completed']" color="emerald" :href="route('ga.index')" />
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-card title="ERF Terbaru">
            @if ($recentErf->isEmpty())
                <p class="py-6 text-center text-sm text-slate-400">Belum ada request ERF.</p>
            @else
                <div class="-mx-5 -mb-5 divide-y divide-slate-100">
                    @foreach ($recentErf as $erf)
                        <a href="{{ route('erf.show', $erf) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-slate-50">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-900">{{ $erf->erf_id }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $erf->pemohon->name }} &middot; {{ $erf->tanggal_request->translatedFormat('d M Y') }}</p>
                            </div>
                            <x-status-badge :color="$erf->statusBadgeColor()" :label="$erf->statusLabel()" />
                        </a>
                    @endforeach
                </div>
            @endif
        </x-card>

        <x-card title="GA Terbaru">
            @if ($recentGa->isEmpty())
                <p class="py-6 text-center text-sm text-slate-400">Belum ada request GA.</p>
            @else
                <div class="-mx-5 -mb-5 divide-y divide-slate-100">
                    @foreach ($recentGa as $ga)
                        <a href="{{ route('ga.show', $ga) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-slate-50">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-900">{{ $ga->ga_id }} &middot; {{ $ga->judul }}</p>
                                <p class="truncate text-xs text-slate-500">{{ $ga->pemohon->name }} &middot; {{ $ga->tanggal_request->translatedFormat('d M Y') }}</p>
                            </div>
                            <x-status-badge :color="$ga->statusBadgeColor()" :label="$ga->statusLabel()" />
                        </a>
                    @endforeach
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.app>
