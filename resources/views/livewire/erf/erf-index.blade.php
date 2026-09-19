<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Request ERF</h2>
            <p class="mt-1 text-sm text-slate-500">Employee Requisition Form</p>
        </div>
        @if ($canCreate)
            <button type="button" wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Buat ERF
            </button>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <x-modal :show="$showForm" :title="$editingId ? 'Ajukan Ulang ERF' : 'Buat Request ERF'" maxWidth="lg">
        @if ($revisiCatatan)
            <div class="mb-5 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <p class="font-medium">Catatan revisi dari atasan ({{ $revisiAtasanName ?? '-' }}):</p>
                <p class="mt-1 whitespace-pre-line">{{ $revisiCatatan }}</p>
            </div>
        @endif

        <form wire:submit="save" class="space-y-6">
            <div>
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Data Pemohon (otomatis)</p>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium text-slate-500">Nama Pemohon</p>
                        <p class="text-sm text-slate-900">{{ auth()->user()->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500">Email Pemohon</p>
                        <p class="text-sm text-slate-900">{{ auth()->user()->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500">Divisi &amp; Jabatan</p>
                        <p class="text-sm text-slate-900">{{ auth()->user()->divisi_jabatan }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-slate-500">Tanggal Request</p>
                        <p class="text-sm text-slate-900">{{ now()->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-5">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Detail Kebutuhan</p>
                <div class="space-y-5">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Jenis ERF</label>
                        <select wire:model.live="jenis_erf" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— Pilih Jenis ERF —</option>
                            <option value="karyawan_baru">Karyawan Baru</option>
                            <option value="pengganti_karyawan_lama">Pengganti Karyawan Lama</option>
                        </select>
                        @error('jenis_erf') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Jumlah Karyawan Dibutuhkan</label>
                        <input type="number" min="1" wire:model="jumlah_karyawan" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('jumlah_karyawan') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    @if ($jenis_erf === 'pengganti_karyawan_lama')
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Karyawan yang Diganti</label>
                            <select wire:model="karyawan_diganti_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Pilih Karyawan —</option>
                                @foreach ($karyawanOptions as $k)
                                    <option value="{{ $k->id }}">{{ $k->nama }} ({{ $k->divisi_jabatan }})</option>
                                @endforeach
                            </select>
                            @error('karyawan_diganti_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Alasan Penggantian</label>
                            <textarea wire:model="alasan_penggantian" rows="3" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            @error('alasan_penggantian') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    @if ($jenis_erf !== '')
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Jabatan Karyawan Dibutuhkan</label>
                            <select wire:model="jabatan_dibutuhkan_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— Pilih Jabatan —</option>
                                @foreach ($jabatanOptions as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama_jabatan }}@if($j->divisi) ({{ $j->divisi }})@endif</option>
                                @endforeach
                            </select>
                            @error('jabatan_dibutuhkan_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            <p class="mt-1 text-xs text-slate-400">PIC HR &amp; estimasi tanggal fulfillment akan otomatis ditentukan sistem berdasarkan jabatan ini.</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Uraian Tugas / Job Description</label>
                            <textarea wire:model="uraian_tugas" rows="4" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            @error('uraian_tugas') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Kualifikasi Kandidat</label>
                            <textarea wire:model="kualifikasi_kandidat" rows="4" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            @error('kualifikasi_kandidat') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Catatan <span class="text-slate-400">(opsional)</span></label>
                            <textarea wire:model="catatan" rows="2" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        @if (auth()->user()->needsAtasanApproval())
                            <div class="rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 text-xs text-indigo-700">
                                Setelah diajukan, ERF ini akan menunggu <strong>approval dari atasan Anda ({{ auth()->user()->atasan->name ?? '-' }})</strong> langsung di aplikasi sebelum diteruskan ke HR.
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            @if ($jenis_erf !== '')
                <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" wire:click="resetForm" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="submit" wire:loading.attr="disabled" wire:target="save" class="rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90 disabled:opacity-60">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Ajukan Ulang' : 'Ajukan ERF' }}</span>
                        <span wire:loading wire:target="save">Menyimpan...</span>
                    </button>
                </div>
            @else
                <div class="flex justify-end border-t border-slate-100 pt-4">
                    <button type="button" wire:click="resetForm" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                </div>
            @endif
        </form>
    </x-modal>

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
                                <x-brand-badge :user="$erf->pemohon" class="mt-1" />
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
