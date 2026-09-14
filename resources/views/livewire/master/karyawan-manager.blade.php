<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Master Data Karyawan</h2>
            <p class="mt-1 text-sm text-slate-500">Referensi Nama, Email, Divisi &amp; Jabatan karyawan (dipakai di dropdown "Karyawan yang Diganti").</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" wire:click="toggleImport" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                &#8593; Import CSV
            </button>
            <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90">
                + Tambah Karyawan
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @include('livewire.master.partials.csv-import')

    <x-modal :show="$showForm" :title="$editingId ? 'Edit Karyawan' : 'Tambah Karyawan'">
        <form wire:submit="save" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                <input type="text" wire:model="nama" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('nama') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                <input type="email" wire:model="email" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Divisi</label>
                <input type="text" wire:model="divisi" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Jabatan</label>
                <input type="text" wire:model="jabatan" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" wire:model="status_aktif" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Karyawan Aktif
            </label>
            <div class="sm:col-span-2 flex justify-end gap-3 border-t border-slate-100 pt-4">
                <button type="button" wire:click="resetForm" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </x-modal>

    <x-card>
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama karyawan..." class="mb-4 w-full max-w-xs rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">

        <div class="-mx-5 overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead>
                    <tr class="border-y border-slate-100 text-xs uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Email</th>
                        <th class="px-5 py-3 font-medium">Divisi - Jabatan</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr wire:key="{{ $item->id }}">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $item->nama }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $item->email ?: '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $item->divisi_jabatan }}</td>
                            <td class="px-5 py-3">
                                @if ($item->status_aktif)
                                    <x-status-badge color="emerald" label="Aktif" />
                                @else
                                    <x-status-badge color="gray" label="Non-aktif" />
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button wire:click="edit({{ $item->id }})" class="mr-2 text-xs font-medium text-indigo-600 hover:underline">Edit</button>
                                <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus data karyawan ini?" class="text-xs font-medium text-rose-600 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada data karyawan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>
    </x-card>
</div>
