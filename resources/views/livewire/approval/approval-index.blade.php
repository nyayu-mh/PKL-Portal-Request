<div>
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Approval Saya</h2>
        <p class="mt-1 text-sm text-slate-500">Daftar request dari tim Anda yang menunggu persetujuan Anda sebagai atasan langsung.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="space-y-6">
        <x-card title="Request ERF Menunggu Approval">
            <div class="-mx-5 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead>
                        <tr class="border-y border-slate-100 text-xs uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3 font-medium">ERF ID</th>
                            <th class="px-5 py-3 font-medium">Pemohon</th>
                            <th class="px-5 py-3 font-medium">Jabatan Dibutuhkan</th>
                            <th class="px-5 py-3 font-medium">Tanggal Request</th>
                            <th class="px-5 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($erfs as $erf)
                            <tr wire:key="erf-{{ $erf->id }}" onclick="window.location='{{ route('erf.show', $erf) }}'" class="cursor-pointer hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-900">{{ $erf->erf_id }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $erf->pemohon->name }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $erf->jabatanDibutuhkan->nama_jabatan ?? '-' }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $erf->tanggal_request->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3 text-right"><span class="text-xs font-semibold text-indigo-600">Review &rarr;</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada request ERF yang menunggu approval Anda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="Request GA Menunggu Approval">
            <div class="-mx-5 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead>
                        <tr class="border-y border-slate-100 text-xs uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3 font-medium">GA ID</th>
                            <th class="px-5 py-3 font-medium">Pemohon</th>
                            <th class="px-5 py-3 font-medium">Judul</th>
                            <th class="px-5 py-3 font-medium">Tanggal Request</th>
                            <th class="px-5 py-3 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($gas as $ga)
                            <tr wire:key="ga-{{ $ga->id }}" onclick="window.location='{{ route('ga.show', $ga) }}'" class="cursor-pointer hover:bg-slate-50">
                                <td class="px-5 py-3 font-medium text-slate-900">{{ $ga->ga_id }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $ga->pemohon->name }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $ga->judul }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $ga->tanggal_request->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3 text-right"><span class="text-xs font-semibold text-indigo-600">Review &rarr;</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">Tidak ada request GA yang menunggu approval Anda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</div>
