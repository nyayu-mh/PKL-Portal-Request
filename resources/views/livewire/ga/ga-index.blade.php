<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Request General Affair</h2>
            <p class="mt-1 text-sm text-slate-500">Kebutuhan fasilitas &amp; layanan kantor</p>
        </div>
        @if ($canCreate)
            <button type="button" wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                Buat Request GA
            </button>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <x-modal :show="$showForm" :title="$editingId ? 'Ajukan Ulang Request GA' : 'Buat Request General Affair'" maxWidth="lg">
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
                    <div><p class="text-xs font-medium text-slate-500">Nama Pemohon</p><p class="text-sm text-slate-900">{{ auth()->user()->name }}</p></div>
                    <div><p class="text-xs font-medium text-slate-500">Email Pemohon</p><p class="text-sm text-slate-900">{{ auth()->user()->email }}</p></div>
                    <div><p class="text-xs font-medium text-slate-500">Divisi &amp; Jabatan</p><p class="text-sm text-slate-900">{{ auth()->user()->divisi_jabatan }}</p></div>
                    <div><p class="text-xs font-medium text-slate-500">Tanggal Request</p><p class="text-sm text-slate-900">{{ now()->translatedFormat('d F Y') }}</p></div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-5">
                <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-slate-400">Detail Request</p>
                <div class="space-y-5">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Jenis Request</label>
                        <select wire:model.live="jenis_request" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— Pilih Jenis Request —</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="pengadaan_barang">Pengadaan Barang</option>
                            <option value="office_acquisition">Office Acquisition</option>
                            <option value="renovasi_kantor">Renovasi Kantor</option>
                        </select>
                        @error('jenis_request') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Judul Request</label>
                        <input type="text" wire:model="judul" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Contoh: AC Ruang Meeting Lantai 2 Mati">
                        @error('judul') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi Request</label>
                        <textarea wire:model="deskripsi" rows="4" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        @error('deskripsi') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Lokasi Kerja</label>
                        <select wire:model="lokasi_kerja" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">— Pilih Lokasi —</option>
                            <option value="head_office">Head Office</option>
                            <option value="crm_office">CRM Office</option>
                            <option value="pdn_office">PDN Office</option>
                            <option value="gudang_palembang">Gudang Palembang</option>
                            <option value="gudang_jakarta">Gudang Jakarta</option>
                            <option value="gudang_pekalongan">Gudang Pekalongan</option>
                        </select>
                        @error('lokasi_kerja') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    @if (auth()->user()->needsAtasanApproval())
                        <div class="rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-3 text-xs text-indigo-700">
                            Setelah diajukan, request GA ini akan menunggu <strong>approval dari atasan Anda ({{ auth()->user()->atasan->name ?? '-' }})</strong> langsung di aplikasi sebelum diteruskan ke tim GA.
                        </div>
                    @endif

                    @if ($jenis_request === 'maintenance')
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Lampiran Bukti Kondisi/Kerusakan <span class="text-rose-500">*wajib</span></label>
                            @if ($existing_lampiran_bukti_kondisi)
                                <p class="mb-1 text-xs text-emerald-600">Sudah ada file terupload sebelumnya — pilih file baru di sini kalau mau menggantinya.</p>
                            @endif
                            <input type="file" wire:model="lampiran_bukti_kondisi" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('lampiran_bukti_kondisi') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Rekomendasi Vendor <span class="text-slate-400">(opsional)</span></label>
                        @if ($existing_lampiran_rekomendasi_vendor)
                            <p class="mb-1 text-xs text-emerald-600">Sudah ada file terupload sebelumnya — pilih file baru di sini kalau mau menggantinya.</p>
                        @endif
                        <input type="file" wire:model="lampiran_rekomendasi_vendor" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('lampiran_rekomendasi_vendor') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan <span class="text-slate-400">(opsional)</span></label>
                        <textarea wire:model="catatan" rows="2" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                <button type="button" wire:click="resetForm" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" wire:loading.attr="disabled" wire:target="save" class="rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90 disabled:opacity-60">
                    <span wire:loading.remove wire:target="save">{{ $editingId ? 'Ajukan Ulang' : 'Ajukan Request' }}</span>
                    <span wire:loading wire:target="save">Menyimpan...</span>
                </button>
            </div>
        </form>
    </x-modal>

    <x-card>
        <div class="mb-4 flex flex-wrap gap-3">
            <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari GA ID / judul..." class="w-full max-w-xs rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                        <th class="px-5 py-3 font-medium">GA ID</th>
                        <th class="px-5 py-3 font-medium">Judul</th>
                        <th class="px-5 py-3 font-medium">Pemohon</th>
                        <th class="px-5 py-3 font-medium">Jenis</th>
                        <th class="px-5 py-3 font-medium">Lokasi</th>
                        <th class="px-5 py-3 font-medium">Approval Atasan</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($gas as $ga)
                        <tr wire:key="{{ $ga->id }}" onclick="window.location='{{ route('ga.show', $ga) }}'" class="cursor-pointer hover:bg-slate-50">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $ga->ga_id }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $ga->judul }}</td>
                            <td class="px-5 py-3">
                                <span class="block text-slate-900">{{ $ga->pemohon->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $ga->pemohon->jabatan ?: '-' }}</span>
                                <x-brand-badge :user="$ga->pemohon" class="mt-1" />
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $ga->jenisLabel() }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $ga->lokasiLabel() }}</td>
                            <td class="px-5 py-3"><x-status-badge :color="$ga->approvalStatusBadgeColor()" :label="$ga->approvalStatusLabel()" /></td>
                            <td class="px-5 py-3"><x-status-badge :color="$ga->statusBadgeColor()" :label="$ga->statusLabel()" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada data request GA.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $gas->links() }}</div>
    </x-card>
</div>
