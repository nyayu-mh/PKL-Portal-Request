<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">{{ $erfRequest ? 'Ajukan Ulang ERF' : 'Buat Request ERF' }}</h2>
            <p class="mt-1 text-sm text-slate-500">Employee Requisition Form — pengajuan karyawan baru / penggantian karyawan lama.</p>
        </div>
        <a href="{{ route('erf.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">&larr; Kembali</a>
    </div>

    @if ($erfRequest && $erfRequest->catatan_approval_atasan)
        <div class="mb-6 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-medium">Catatan revisi dari atasan ({{ $erfRequest->atasan->name ?? '-' }}):</p>
            <p class="mt-1 whitespace-pre-line">{{ $erfRequest->catatan_approval_atasan }}</p>
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        {{-- Info otomatis pemohon --}}
        <x-card title="Data Pemohon (otomatis)">
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
        </x-card>

        <x-card title="Detail Kebutuhan">
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
        </x-card>

        @if ($jenis_erf !== '')
            <div class="flex justify-end gap-3">
                <a href="{{ route('erf.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</a>
                <button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90 disabled:opacity-60">
                    <span wire:loading.remove>{{ $erfRequest ? 'Ajukan Ulang' : 'Ajukan ERF' }}</span>
                    <span wire:loading>Menyimpan...</span>
                </button>
            </div>
        @endif
    </form>
</div>
