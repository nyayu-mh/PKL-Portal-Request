<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('ga.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-900">&larr; Daftar Request GA</a>
            <div class="mt-1 flex items-center gap-3">
                <h2 class="text-xl font-semibold text-slate-900">{{ $gaRequest->ga_id }}</h2>
                <x-status-badge :color="$gaRequest->statusBadgeColor()" :label="$gaRequest->statusLabel()" />
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
                    <div><dt class="text-xs font-medium text-slate-500">Nama Pemohon</dt><dd class="text-sm text-slate-900">{{ $gaRequest->pemohon->name }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Email Pemohon</dt><dd class="text-sm text-slate-900">{{ $gaRequest->pemohon->email }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Divisi &amp; Jabatan</dt><dd class="text-sm text-slate-900">{{ $gaRequest->pemohon->divisi_jabatan }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Tanggal Request</dt><dd class="text-sm text-slate-900">{{ $gaRequest->tanggal_request->translatedFormat('d F Y') }}</dd></div>
                </dl>
            </x-card>

            <x-card title="Detail Request">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-medium text-slate-500">Jenis Request</dt><dd class="text-sm text-slate-900">{{ $gaRequest->jenisLabel() }}</dd></div>
                    <div><dt class="text-xs font-medium text-slate-500">Lokasi Kerja</dt><dd class="text-sm text-slate-900">{{ $gaRequest->lokasiLabel() }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Judul</dt><dd class="text-sm text-slate-900">{{ $gaRequest->judul }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Deskripsi</dt><dd class="whitespace-pre-line text-sm text-slate-900">{{ $gaRequest->deskripsi }}</dd></div>
                    @if ($gaRequest->catatan)
                        <div class="sm:col-span-2"><dt class="text-xs font-medium text-slate-500">Catatan Pemohon</dt><dd class="whitespace-pre-line text-sm text-slate-900">{{ $gaRequest->catatan }}</dd></div>
                    @endif
                </dl>

                <div class="mt-4 flex flex-wrap gap-2">
                    @if ($gaRequest->lampiran_bukti_kondisi)
                        <a href="{{ route('ga.lampiran', [$gaRequest, 'bukti']) }}" target="_blank" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">📎 Bukti Kondisi/Kerusakan</a>
                    @endif
                    @if ($gaRequest->lampiran_rekomendasi_vendor)
                        <a href="{{ route('ga.lampiran', [$gaRequest, 'rekomendasi']) }}" target="_blank" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">📎 Rekomendasi Vendor (Pemohon)</a>
                    @endif
                </div>
            </x-card>

            @if ($canManage || $quotations->isNotEmpty())
                <x-card title="Quotation Vendor" subtitle="Minimal 3 quotation vendor sebagai bahan pertimbangan">
                    <div class="space-y-3">
                        @forelse ($quotations as $q)
                            <div wire:key="quo-{{ $q->id }}" class="flex items-center justify-between gap-3 rounded-lg border {{ $q->is_terpilih ? 'border-emerald-300 bg-emerald-50' : 'border-slate-200' }} px-4 py-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-slate-900">{{ $q->nama_vendor }} @if($q->is_terpilih)<span class="ml-1 text-xs font-semibold text-emerald-600">✓ Terpilih</span>@endif</p>
                                    <p class="text-xs text-slate-500">
                                        @if($q->harga_penawaran) Rp {{ number_format($q->harga_penawaran, 0, ',', '.') }} &middot; @endif
                                        <a href="{{ route('ga.quotation', [$gaRequest, $q]) }}" target="_blank" class="text-indigo-600 hover:underline">Lihat File</a>
                                    </p>
                                </div>
                                @if ($canManage)
                                    <div class="flex shrink-0 gap-2">
                                        @unless($q->is_terpilih)
                                            <button type="button" wire:click="selectQuotation({{ $q->id }})" class="rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">Pilih</button>
                                        @endunless
                                        <button type="button" wire:click="removeQuotation({{ $q->id }})" wire:confirm="Hapus quotation ini?" class="rounded-lg border border-rose-200 px-2.5 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50">Hapus</button>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-slate-400">Belum ada quotation vendor.</p>
                        @endforelse
                    </div>

                    @if ($canManage)
                        <form wire:submit="addQuotation" class="mt-5 grid grid-cols-1 gap-3 border-t border-slate-100 pt-4 sm:grid-cols-3">
                            <div>
                                <input type="text" wire:model="new_vendor_nama" placeholder="Nama vendor" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('new_vendor_nama') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <input type="number" step="0.01" wire:model="new_vendor_harga" placeholder="Harga penawaran (opsional)" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <input type="file" wire:model="new_vendor_file" class="block w-full text-xs text-slate-600 file:mr-2 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-indigo-700">
                                @error('new_vendor_file') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="sm:col-span-3">
                                <button type="submit" class="rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-100">+ Tambah Quotation</button>
                            </div>
                        </form>
                    @endif
                </x-card>
            @endif

            @if ($gaRequest->approval_status !== 'tidak_perlu')
                <x-card title="Approval Atasan">
                    <div class="mb-4 flex items-center gap-3">
                        <x-status-badge :color="$gaRequest->approvalStatusBadgeColor()" :label="$gaRequest->approvalStatusLabel()" />
                        <span class="text-sm text-slate-500">Atasan: {{ $gaRequest->atasan->name ?? '-' }}</span>
                    </div>

                    @if ($gaRequest->approval_status === 'revisi' && $gaRequest->catatan_approval_atasan)
                        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                            <p class="font-medium">Catatan revisi:</p>
                            <p class="mt-1 whitespace-pre-line">{{ $gaRequest->catatan_approval_atasan }}</p>
                        </div>
                    @endif

                    @if ($gaRequest->approval_status === 'ditolak')
                        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                            <p class="font-medium">Alasan ditolak:</p>
                            <p class="mt-1 whitespace-pre-line">{{ $gaRequest->catatan_approval_atasan ?: '-' }}</p>
                            <p class="mt-2 text-xs font-semibold uppercase tracking-wide text-rose-500">Case closed &mdash; request ini sudah final, tidak bisa direvisi atau diajukan ulang.</p>
                        </div>
                    @endif

                    @if ($gaRequest->approval_status === 'disetujui' && $gaRequest->catatan_approval_atasan)
                        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                            <p class="whitespace-pre-line">{{ $gaRequest->catatan_approval_atasan }}</p>
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
                                <button type="button" wire:click="approve" wire:confirm="Setujui request GA ini?" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Setujui</button>
                                <button type="button" wire:click="requestRevision" class="rounded-lg border border-amber-300 px-4 py-2 text-sm font-semibold text-amber-600 hover:bg-amber-50">Kembalikan untuk Revisi</button>
                                <button type="button" wire:click="rejectFinal" wire:confirm="Tolak request GA ini secara final? Setelah ditolak, request TIDAK BISA direvisi/diajukan ulang lagi (case closed)." class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">Tolak</button>
                            </div>
                        </form>
                    @endif

                    @if ($isOwner && in_array($gaRequest->approval_status, ['menunggu', 'revisi']))
                        <div class="flex gap-3 {{ $isApprover ? 'mt-4 border-t border-slate-100 pt-4' : '' }}">
                            @if ($gaRequest->approval_status === 'revisi')
                                <a href="{{ route('ga.index', ['edit' => $gaRequest->id]) }}" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Edit &amp; Ajukan Ulang</a>
                            @endif
                            <button type="button" wire:click="cancel" wire:confirm="Yakin ingin menghapus request ini?" class="rounded-lg border border-rose-300 px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">Hapus Request</button>
                        </div>
                    @endif
                </x-card>
            @endif

            @if ($canManage && ! $gaRequest->isApprovedForProcessing() && ! $gaRequest->isRejectedFinal())
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    Request GA ini belum bisa diproses tim GA — masih {{ strtolower($gaRequest->approvalStatusLabel()) }}.
                </div>
            @endif

            @if ($canManage && $gaRequest->isApprovedForProcessing())
                <x-card title="Update Proses GA">
                    <form wire:submit="updateStatus" class="space-y-4">
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Butuh Vendor?</label>
                                <select wire:model="butuh_vendor" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="">- Belum ditentukan -</option>
                                    <option value="1">Ya</option>
                                    <option value="0">Tidak</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Mulai Proses</label>
                                <input type="date" wire:model="tanggal_mulai_proses" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-slate-700">Nama Vendor <span class="text-slate-400">(otomatis terisi jika memilih quotation di atas, atau isi manual)</span></label>
                                <input type="text" wire:model="nama_vendor" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-slate-700">Catatan RAB (estimasi anggaran)</label>
                                <textarea wire:model="catatan_rab" rows="2" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                                <select wire:model="status" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach ($statusOptions as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                                <input type="date" wire:model="tanggal_selesai" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Realisasi Biaya (Rp)</label>
                                <input type="number" step="0.01" wire:model="realisasi_biaya" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-slate-700">Catatan Internal GA</label>
                                <textarea wire:model="catatan_internal" rows="2" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-sm font-medium text-slate-700">Catatan Perubahan Status <span class="text-slate-400">(opsional, akan tercatat di riwayat)</span></label>
                                <textarea wire:model="catatan_update" rows="2" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            </div>
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
                                        <p class="text-sm font-medium text-slate-900">{{ \App\Models\GaRequest::statusLabels()[$h->status] ?? $h->status }}</p>
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
                    <div><dt class="text-xs font-medium text-slate-500">PIC GA</dt><dd class="text-sm text-slate-900">{{ $gaRequest->picGa->name ?? '-' }}</dd></div>
                    @if ($gaRequest->nama_vendor)
                        <div><dt class="text-xs font-medium text-slate-500">Vendor Terpilih</dt><dd class="text-sm font-semibold text-emerald-600">{{ $gaRequest->nama_vendor }}</dd></div>
                    @endif
                    @if ($gaRequest->realisasi_biaya)
                        <div><dt class="text-xs font-medium text-slate-500">Realisasi Biaya</dt><dd class="text-sm text-slate-900">Rp {{ number_format($gaRequest->realisasi_biaya, 0, ',', '.') }}</dd></div>
                    @endif
                </dl>
            </x-card>
        </div>
    </div>
</div>
