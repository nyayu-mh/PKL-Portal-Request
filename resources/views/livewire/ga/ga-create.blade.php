<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">{{ $gaRequest ? 'Ajukan Ulang Request GA' : 'Buat Request General Affair' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Pengajuan kebutuhan fasilitas / layanan kantor ke divisi GA.</p>
        </div>
        <a href="{{ route('ga.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">&larr; Kembali</a>
    </div>

    @if ($gaRequest && $gaRequest->catatan_approval_atasan)
        <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-medium">Catatan revisi dari atasan ({{ $gaRequest->atasan->name ?? '-' }}):</p>
            <p class="mt-1 whitespace-pre-line">{{ $gaRequest->catatan_approval_atasan }}</p>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <x-card title="Data Pemohon (otomatis)">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div><p class="text-xs font-medium text-slate-500">Nama Pemohon</p><p class="text-sm text-slate-900">{{ auth()->user()->name }}</p></div>
                <div><p class="text-xs font-medium text-slate-500">Email Pemohon</p><p class="text-sm text-slate-900">{{ auth()->user()->email }}</p></div>
                <div><p class="text-xs font-medium text-slate-500">Divisi &amp; Jabatan</p><p class="text-sm text-slate-900">{{ auth()->user()->divisi_jabatan }}</p></div>
                <div><p class="text-xs font-medium text-slate-500">Tanggal Request</p><p class="text-sm text-slate-900">{{ now()->translatedFormat('d F Y') }}</p></div>
            </div>
        </x-card>

        <x-card title="Detail Request">
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
                        <input type="file" wire:model="lampiran_bukti_kondisi" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                        @error('lampiran_bukti_kondisi') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Rekomendasi Vendor <span class="text-slate-400">(opsional)</span></label>
                    <input type="file" wire:model="lampiran_rekomendasi_vendor" class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('lampiran_rekomendasi_vendor') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Catatan <span class="text-slate-400">(opsional)</span></label>
                    <textarea wire:model="catatan" rows="2" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                </div>
            </div>
        </x-card>

        <div class="flex justify-end gap-3">
            <a href="{{ route('ga.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
            <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90 disabled:opacity-60">
                <span wire:loading.remove>{{ $gaRequest ? 'Ajukan Ulang' : 'Ajukan Request' }}</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </form>
</div>
