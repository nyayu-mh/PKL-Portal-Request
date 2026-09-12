<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Request ERF</h2>
            <p class="mt-1 text-sm text-slate-500">Employee Requisition Form</p>
        </div>
        @if ($canCreate)
            <a href="{{ route('erf.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Buat ERF
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <x-card>
        <div class="mb-4 flex flex-wrap gap-3">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari ERF ID / nama pemohon..." class="w-full max-w-xs rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <select wire:model.live="status" class="rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua Status</option>
                @foreach ($statusOptions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="-mx-5 overflow-x-auto">
            <table class="w-full min-w-[720px] text-left text-sm">
                <thead>
                    <tr class="border-y border-slate-100 text-xs uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3 font-medium">ERF ID</th>
                        <th class="px-5 py-3 font-medium">Pemohon</th>
                        <th class="px-5 py-3 font-medium">Jenis</th>
                        <th class="px-5 py-3 font-medium">Jabatan</th>
                        <th class="px-5 py-3 font-medium">Estimasi Fulfillment</th>
                        <th class="px-5 py-3 font-medium">Approval Atasan</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($erfs as $erf)
                        <tr wire:key="{{ $erf->id }}" onclick="window.location='{{ route('erf.show', $erf) }}'" class="cursor-pointer hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $erf->erf_id }}</td>
                            <td class="px-5 py-3">
                                <span class="block text-slate-900">{{ $erf->pemohon->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $erf->pemohon->jabatan ?: '-' }}</span>
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $erf->jenis_erf === 'karyawan_baru' ? 'Karyawan Baru' : 'Pengganti' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $erf->jabatanDibutuhkan->nama_jabatan ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ optional($erf->estimasi_tanggal_fulfillment)->translatedFormat('d M Y') ?? '-' }}</td>
                            <td class="px-5 py-3"><x-status-badge :color="$erf->approvalStatusBadgeColor()" :label="$erf->approvalStatusLabel()" /></td>
                            <td class="px-5 py-3"><x-status-badge :color="$erf->statusBadgeColor()" :label="$erf->statusLabel()" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada data ERF.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $erfs->links() }}</div>
    </x-card>
</div>
