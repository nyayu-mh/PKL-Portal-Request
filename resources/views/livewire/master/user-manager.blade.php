<div>
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Manajemen User</h2>
            <p class="mt-1 text-sm text-slate-500">Hanya Admin yang bisa menambahkan akun baru. Tidak ada pendaftaran mandiri (self-register).</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="button" wire:click="toggleImport" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                &#8593; Import CSV
            </button>
            <button wire:click="create" class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90">
                + Tambah User
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

    <x-modal :show="$showForm" :title="$editingId ? 'Edit User' : 'Tambah User'">
            <form wire:submit="save" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                    <input type="text" wire:model="name" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" wire:model="email" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">
                        Password @if($editingId) <span class="text-slate-400">(kosongkan jika tidak diubah)</span> @endif
                    </label>
                    <input type="text" wire:model="password" placeholder="{{ $editingId ? '••••••••' : 'Minimal 8 karakter' }}" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('password') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Divisi</label>
                    <input type="text" wire:model="divisi" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Jabatan (tampilan)</label>
                    <input type="text" wire:model="jabatan" placeholder="Contoh: Staff IT" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Level Jabatan</label>
                    <select wire:model="jabatan_level" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($jabatanLevelOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Menentukan hak buat ERF/GA &amp; wajib lampiran approval.</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Role Sistem</label>
                    <select wire:model="role" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($roleOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Tim HR memproses ERF, Tim GA memproses request GA, Admin akses penuh.</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Atasan Langsung</label>
                    <select wire:model="atasan_id" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Tidak ada / Top Level —</option>
                        @foreach ($atasanOptions as $a)
                            <option value="{{ $a->id }}">{{ $a->name }} ({{ $a->jabatanLevelLabel() }})</option>
                        @endforeach
                    </select>
                    @error('atasan_id') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-slate-400">Wajib diisi untuk user dengan level Junior Leader / Leader agar bisa mengajukan ERF/GA (perlu approval atasan sebelum diproses HR/GA).</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="mb-1 block text-sm font-medium text-slate-700">Brand</label>
                    <select wire:model="brand" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— Belum ditentukan —</option>
                        @foreach ($brandOptions as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Supaya Admin tahu brand apa yang mengajukan saat user ini bikin request.</p>
                    @error('brand') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="is_active" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Akun Aktif (bisa login)
                </label>
                <label class="flex items-start gap-2 text-sm text-slate-700">
                    <input type="checkbox" wire:model="lihat_semua_request" class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <span>Bisa melihat semua request ERF &amp; GA
                        <span class="block text-xs text-slate-400">Untuk pejabat yang memantau seluruh request (mis. Manager HRBP). Super Admin sudah otomatis.</span>
                    </span>
                </label>
                <div class="sm:col-span-2 flex justify-end gap-3 border-t border-slate-100 pt-4">
                    <button type="button" wire:click="resetForm" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
                    <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Simpan</button>
                </div>
            </form>
    </x-modal>

    <x-card>
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama / email..." class="mb-4 w-full max-w-xs rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">

        <div class="-mx-5 overflow-x-auto">
            <table class="w-full min-w-[760px] text-left text-sm">
                <thead>
                    <tr class="border-y border-slate-100 text-xs uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Email</th>
                        <th class="px-5 py-3 font-medium">Divisi - Jabatan</th>
                        <th class="px-5 py-3 font-medium">Level</th>
                        <th class="px-5 py-3 font-medium">Role</th>
                        <th class="px-5 py-3 font-medium">Atasan Langsung</th>
                        <th class="px-5 py-3 font-medium">Brand</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr wire:key="{{ $item->id }}">
                            <td class="px-5 py-3 font-medium text-slate-900">{{ $item->name }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $item->email }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $item->divisi_jabatan }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $item->jabatanLevelLabel() }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $item->roleLabel() }}</td>
                            <td class="px-5 py-3 text-slate-600">{{ $item->atasan->name ?? '-' }}</td>
                            <td class="px-5 py-3 text-slate-600">
                                @if ($item->brand)
                                    <x-status-badge :color="$item->brand === 'semua' ? 'emerald' : ($item->brand === 'wookey_wight' ? 'indigo' : 'orange')" :label="$item->brandLabel()" />
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if ($item->is_active)
                                    <x-status-badge color="emerald" label="Aktif" />
                                @else
                                    <x-status-badge color="gray" label="Non-aktif" />
                                @endif
                            </td>
                            <td class="px-5 py-3 text-right">
                                <button wire:click="edit({{ $item->id }})" class="mr-2 text-xs font-medium text-indigo-600 hover:underline">Edit</button>
                                <button wire:click="toggleActive({{ $item->id }})" class="mr-2 text-xs font-medium text-slate-500 hover:underline">{{ $item->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                                <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus user {{ $item->name }}? Tindakan ini tidak bisa dibatalkan." class="text-xs font-medium text-rose-600 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-5 py-10 text-center text-sm text-slate-400">Belum ada data user.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>
    </x-card>
</div>
