<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Kalender Kerja</h2>
            <p class="mt-1 text-sm text-slate-500">Default Senin–Jumat hari kerja, Sabtu &amp; Minggu libur. Tambahkan tanggal di sini untuk hari libur nasional/cuti bersama, atau hari masuk pengganti.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" wire:click="toggleImport" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                &#8593; Import CSV
            </button>
            <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90">
                + Tambah Tanggal
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @include('livewire.master.partials.csv-import')

    <x-modal :show="$showForm" :title="$editingId ? 'Edit Tanggal' : 'Tambah Tanggal'">
        <form wire:submit="save" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                <input type="date" wire:model="tanggal" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                @error('tanggal') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Tipe</label>
                <select wire:model="tipe" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="libur">Libur (walau hari kerja biasa)</option>
                    <option value="kerja">Masuk Kerja (walau weekend)</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">Keterangan</label>
                <input type="text" wire:model="keterangan" placeholder="Contoh: Hari Raya Idul Fitri" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="sm:col-span-2 flex justify-end gap-3 border-t border-slate-100 pt-4">
                <button type="button" wire:click="resetForm" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan</button>
            </div>
        </form>
    </x-modal>

    <x-card>
        <div class="-mx-5 overflow-x-auto">
            <table class="w-full min-w-[560px] text-left text-sm">
                <thead>
                    <tr class="border-y border-slate-100 text-xs uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Tipe</th>
                        <th class="px-5 py-3 font-medium">Keterangan</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr wire:key="{{ $item->id }}">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $item->tanggal->translatedFormat('d F Y (l)') }}</td>
                            <td class="px-5 py-3">
                                @if ($item->tipe === 'libur')
                                    <x-status-badge color="rose" label="Libur" />
                                @else
                                    <x-status-badge color="emerald" label="Masuk Kerja" />
                                @endif
                            </td>
                            <td class="px-5 py-3 text-slate-600">{{ $item->keterangan ?: '-' }}</td>
                            <td class="px-5 py-3 text-right">
                                <button wire:click="edit({{ $item->id }})" class="mr-2 text-xs font-medium text-indigo-600 hover:underline">Edit</button>
                                <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus tanggal ini?" class="text-xs font-medium text-rose-600 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada data kalender kerja.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>
    </x-card>
</div>
