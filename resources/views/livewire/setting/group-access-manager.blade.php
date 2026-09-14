<div>
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Manajemen Grup &amp; Akses</h2>
        <p class="mt-1 text-sm text-slate-500">Atur hak akses tiap grup ke tiap menu — lihat, tambah, ubah, hapus. Super Admin selalu full akses dan tidak diatur di sini.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <x-card>
        <div class="mb-5 max-w-xs">
            <label class="mb-1 block text-sm font-medium text-slate-700">Pilih Grup</label>
            <select wire:model.live="selectedRole" class="w-full rounded-lg border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($groupOptions as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <form wire:submit="save">
            <div class="-mx-5 overflow-x-auto">
                <table class="w-full min-w-[560px] text-left text-sm">
                    <thead>
                        <tr class="border-y border-slate-100 text-xs uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3 font-medium">Menu</th>
                            <th class="px-5 py-3 text-center font-medium">Lihat</th>
                            <th class="px-5 py-3 text-center font-medium">Tambah</th>
                            <th class="px-5 py-3 text-center font-medium">Ubah</th>
                            <th class="px-5 py-3 text-center font-medium">Hapus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($menuOptions as $key => $label)
                            <tr wire:key="perm-{{ $key }}">
                                <td class="px-5 py-3 text-slate-700">{{ $label }}</td>
                                <td class="px-5 py-3 text-center">
                                    <input type="checkbox" wire:model="perms.{{ $key }}.view" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <input type="checkbox" wire:model="perms.{{ $key }}.create" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <input type="checkbox" wire:model="perms.{{ $key }}.update" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <input type="checkbox" wire:model="perms.{{ $key }}.delete" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <p class="mt-4 rounded-lg bg-amber-50 px-4 py-3 text-xs text-amber-700">
                Catatan: kolom <strong>Lihat</strong> sudah aktif mengatur apakah menu tersebut muncul di sidebar untuk grup ini.
                Kolom <strong>Tambah / Ubah / Hapus</strong> tersimpan tapi belum mengunci tombol aksi di tiap halaman — akan menyusul.
            </p>

            <div class="mt-4 flex justify-end">
                <button type="submit" class="rounded-lg bg-gradient-to-r from-indigo-500 to-violet-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/25 hover:opacity-90">Simpan Perubahan</button>
            </div>
        </form>
    </x-card>
</div>
