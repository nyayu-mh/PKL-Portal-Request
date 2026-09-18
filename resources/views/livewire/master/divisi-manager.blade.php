<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Master Data Divisi</h2>
            <p class="mt-1 text-sm text-slate-500">Referensi divisi/departemen perusahaan, sesuai struktur organisasi.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" wire:click="toggleImport" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                &#8593; Import CSV
            </button>
            <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90">
                + Tambah Divisi
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
    @endif

    @include('livewire.master.partials.csv-import')

    <x-modal :show="$showForm" :title="$editingId ? 'Edit Divisi' : 'Tambah Divisi'">
        <form wire:submit="save" class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Nama Divisi</label>
                <input type="text" wire:model="nama_divisi" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('nama_divisi') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                Divisi Aktif
            </label>
            <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
                <button type="button" wire:click="resetForm" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </x-modal>

    <x-card>
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama divisi..." class="mb-4 w-full max-w-xs rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">

        <div class="-mx-5 overflow-x-auto">
            <table class="w-full min-w-[480px] text-left text-sm">
                <thead>
                    <tr class="border-y border-slate-100 text-xs uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3 font-medium">Nama Divisi</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr wire:key="{{ $item->id }}">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $item->nama_divisi }}</td>
                            <td class="px-5 py-3">
                                @if ($item->is_active)
                                    <x-status-badge color="emerald" label="Aktif" />
                                @else
                                    <x-status-badge color="gray" label="Non-aktif" />
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button wire:click="edit({{ $item->id }})" class="mr-2 text-xs font-medium text-indigo-600 hover:underline">Edit</button>
                                <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus divisi ini?" class="text-xs font-medium text-rose-600 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada data divisi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>
    </x-card>
</div>
