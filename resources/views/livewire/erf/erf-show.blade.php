<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('erf.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">&larr; Daftar ERF</a>
            <div class="mt-1 flex items-center gap-3">
                <h2 class="text-xl font-semibold text-slate-900">{{ $erfRequest->erf_id }}</h2>
                <x-status-badge :color="$erfRequest->statusBadgeColor()" :label="$erfRequest->statusLabel()" />
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-card title="Data Pemohon">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-medium text-slate-500">Nama Pemohon</dt><dd class="text-sm text-slate-900">{{ $erfRequest->pemohon->name }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Email Pemohon</dt><dd class="text-sm text-slate-900">{{ $erfRequest->pemohon->email }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Brand</dt><dd class="text-sm text-slate-900"><x-brand-badge :user="$erfRequest->pemohon" empty="Belum diatur" /></dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Divisi &amp; Jabatan</dt><dd class="text-sm text-slate-900">{{ $erfRequest->pemohon->divisi_jabatan }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Tanggal Request</dt><dd class="text-sm text-slate-900">{{ $erfRequest->tanggal_request->translatedFormat('d F Y') }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Detail Kebutuhan">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-medium text-slate-500">Jenis ERF</dt><dd class="text-sm text-slate-900">{{ $erfRequest->jenis_erf === 'karyawan_baru' ? 'Karyawan Baru' : 'Pengganti Karyawan Lama' }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Jumlah Karyawan Dibutuhkan</dt><dd class="text-sm text-slate-900">{{ $erfRequest->jumlah_karyawan }} orang</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Jabatan Dibutuhkan</dt><dd class="text-sm text-slate-900">{{ $erfRequest->jabatanDibutuhkan->nama_jabatan ?? '-' }}</dd></div>
                    @if ($erfRequest->jenis_erf === 'pengganti_karyawan_lama')
                        <div><dt class="text-xs font-medium text-slate-500">Karyawan yang Diganti</dt><dd class="text-sm text-slate-900">{{ $erfRequest->karyawanDiganti->nama ?? '-' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Alasan Penggantian</dt><dd class="whitespace-pre-line text-sm text-slate-900">{{ $erfRequest->alasan_penggantian }}</dd></div>
                    @endif
                    <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Uraian Tugas / Job Description</dt><dd class="whitespace-pre-line text-sm text-slate-900">{{ $erfRequest->uraian_tugas }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Kualifikasi Kandidat</dt><dd class="whitespace-pre-line text-sm text-slate-900">{{ $erfRequest->kualifikasi_kandidat }}</dd></div>
                    @if ($erfRequest->catatan)
                        <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Catatan</dt><dd class="whitespace-pre-line text-sm text-slate-900">{{ $erfRequest->catatan }}</dd></div>
                    @endif
                </dl>
            </x-card>

            @if ($erfRequest->approval_status !== 'tidak_perlu')
                <x-card title="Approval Atasan">
                    <div class="mb-4 flex items-center gap-3">
                        <x-status-badge :color="$erfRequest->approvalStatusBadgeColor()" :label="$erfRequest->approvalStatusLabel()" />
                        <span class="text-sm text-slate-500">Atasan: {{ $erfRequest->atasan->name ?? '-' }}</span>
                    </div>

                    @if ($erfRequest->approval_status === 'revisi' && $erfRequest->catatan_approval_atasan)
                        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            <p class="font-medium">Catatan revisi:</p>
                            <p class="mt-1 whitespace-pre-line">{{ $erfRequest->catatan_approval_atasan }}</p>
                        </div>
                    @endif

                    @if ($erfRequest->approval_status === 'ditolak')
                        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <p class="font-medium">Alasan ditolak:</p>
                            <p class="mt-1 whitespace-pre-line">{{ $erfRequest->catatan_approval_atasan ?: '-' }}</p>
                            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-rose-500">Case closed &mdash; request ini sudah final, tidak bisa direvisi atau diajukan ulang.</p>
                        </div>
                    @endif

                    @if ($erfRequest->approval_status === 'disetujui' && $erfRequest->catatan_approval_atasan)
                        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            <p class="whitespace-pre-line">{{ $erfRequest->catatan_approval_atasan }}</p>
                        </div>
                    @endif

                    @if ($isApprover)
                        <form class="space-y-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Catatan <span class="text-slate-400">(wajib diisi kalau minta revisi / menolak)</span></label>
                                <textarea wire:model="catatan_approval" rows="2" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                @error('catatan_approval') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button type="button" wire:click="approve" wire:confirm="Setujui ERF ini?" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Setujui</button>
                                <button type="button" wire:click="requestRevision" class="rounded-lg border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-600 hover:bg-amber-50">Kembalikan untuk Revisi</button>
                                <button type="button" wire:click="rejectFinal" wire:confirm="Tolak ERF ini secara final? Setelah ditolak, request TIDAK BISA direvisi/diajukan ulang lagi (case closed)." class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">Tolak</button>
                            </div>
                        </form>
                    @endif

                    @if ($isOwner && in_array($erfRequest->approval_status, ['menunggu', 'revisi']))
                        <div class="flex gap-3 {{ $isApprover ? 'mt-4 border-t border-slate-100 pt-4' : '' }}">
                            @if ($erfRequest->approval_status === 'revisi')
                                <a href="{{ route('erf.index', ['edit' => $erfRequest->id]) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Edit &amp; Ajukan Ulang</a>
                            @endif
                            <button type="button" wire:click="cancel" wire:confirm="Yakin ingin menghapus request ini?" class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">Hapus Request</button>
                        </div>
                    @endif
                </x-card>
            @endif

            @if ($canManage && ! $erfRequest->isApprovedForProcessing() && ! $erfRequest->isRejectedFinal())
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    ERF ini belum bisa diproses HR — masih {{ strtolower($erfRequest->approvalStatusLabel()) }}.
                </div>
            @endif

            @if ($canManage && $erfRequest->isApprovedForProcessing())
                <x-card title="Update Proses HR">
                    <form wire:submit="updateStatus" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                                <select wire:model="status" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ($statusOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div></div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                                <input type="date" wire:model="tanggal_selesai" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Karyawan Masuk</label>
                                <input type="date" wire:model="tanggal_karyawan_masuk" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Catatan Perubahan <span class="text-slate-400">(opsional)</span></label>
                            <textarea wire:model="catatan_update" rows="2" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90">Simpan Perubahan</button>
                        </div>
                    </form>
                </x-card>
            @endif

            <x-card title="Riwayat Status">
                <div class="flow-root">
                    <ul class="-mb-6">
                        @forelse ($histories as $h)
                            <li class="relative pb-6">
                                @if (!$loop->last)<span class="absolute left-2 top-2 h-full w-px bg-slate-200"></span>@endif
                                <div class="relative flex gap-3">
                                    <span class="mt-1 h-4 w-4 shrink-0 rounded-full bg-indigo-500 ring-4 ring-indigo-100"></span>
                                    <div>
                                        <p class="text-sm font-medium text-slate-900">{{ \App\Models\ErfRequest::statusLabels()[$h->status] ?? $h->status }}</p>
                                        <p class="text-xs text-slate-500">{{ $h->changedBy->name ?? 'Sistem' }} &middot; {{ $h->created_at->translatedFormat('d M Y H:i') }}</p>
                                        @if ($h->catatan)<p class="mt-1 text-sm text-slate-600">{{ $h->catatan }}</p>@endif
                                    </div>
                                </div>
                            </li>
                        @empty
                            <p class="text-sm text-slate-400">Belum ada riwayat.</p>
                        @endforelse
                    </ul>
                </div>
            </x-card>
        </div>

        <div class="space-y-6">
            <x-card title="Otomatis dari Sistem">
                <dl class="space-y-4">
                    <div><dt class="text-xs font-medium text-slate-500">PIC HR</dt><dd class="text-sm text-slate-900">{{ $erfRequest->picHr->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Target TTF</dt><dd class="text-sm text-slate-900">{{ $erfRequest->ttf_hari }} hari kerja</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Tanggal Mencari Kandidat</dt><dd class="text-sm text-slate-900">{{ optional($erfRequest->tanggal_mencari_kandidat)->translatedFormat('d F Y') }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Estimasi Tanggal Fulfillment</dt><dd class="text-sm font-semibold text-indigo-600">{{ optional($erfRequest->estimasi_tanggal_fulfillment)->translatedFormat('d F Y') }}</dd></div>
                    @if ($erfRequest->tanggal_selesai)
                        <div><dt class="text-xs font-medium text-slate-500">Tanggal Selesai</dt><dd class="text-sm text-slate-900">{{ $erfRequest->tanggal_selesai->translatedFormat('d F Y') }}</dd></div>
                    @endif
                    @if ($erfRequest->tanggal_karyawan_masuk)
                        <div><dt class="text-xs font-medium text-slate-500">Tanggal Karyawan Masuk</dt><dd class="text-sm text-slate-900">{{ $erfRequest->tanggal_karyawan_masuk->translatedFormat('d F Y') }}</dd></div>
                    @endif
                </dl>
            </x-card>
        </div>
    </div>
</div>
