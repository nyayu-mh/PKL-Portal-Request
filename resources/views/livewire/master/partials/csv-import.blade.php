@if ($showImport)
    <x-card title="Import dari CSV" class="mb-6">
        <div class="space-y-4 text-sm">
            <div class="flex flex-wrap items-center gap-3">
                <button type="button" wire:click="downloadTemplate"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    &#8595; Download Template CSV
                </button>
                <span class="text-xs text-slate-500">Isi di Excel / Google Sheets, lalu simpan sebagai <strong>.csv</strong> sebelum di-upload.</span>
            </div>

            <div class="rounded-lg bg-slate-50 px-3 py-2 text-xs leading-relaxed text-slate-600">
                <strong>Kolom (urut sesuai template):</strong> {{ implode(', ', $this->csvTemplateHeaders()) }}<br>
                {{ $this->csvImportHint() }}
            </div>

            <div>
                <input type="file" wire:model="csvFile" accept=".csv,text/csv"
                    class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                <div wire:loading wire:target="csvFile" class="mt-1 text-xs text-slate-400">Mengunggah file…</div>
                @error('csvFile') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" wire:click="toggleImport"
                    class="rounded-lg border border-slate-300 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">Tutup</button>
                <button type="button" wire:click="import" wire:loading.attr="disabled" wire:target="import,csvFile"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700 disabled:opacity-50">
                    <span wire:loading.remove wire:target="import">Proses Import</span>
                    <span wire:loading wire:target="import">Memproses…</span>
                </button>
            </div>

            @if ($importedCount > 0 || count($importErrors) || count($importNotes))
                <div class="space-y-2 border-t border-slate-100 pt-3">
                    @if ($importedCount > 0)
                        <p class="text-xs font-semibold text-emerald-700">&#10003; {{ $importedCount }} baris berhasil diimport.</p>
                    @endif

                    @if (count($importNotes))
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-800">
                            <p class="mb-1 font-semibold">Catatan:</p>
                            <ul class="list-disc space-y-0.5 pl-4">
                                @foreach ($importNotes as $n) <li>{{ $n }}</li> @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (count($importErrors))
                        <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2 text-xs text-rose-700">
                            <p class="mb-1 font-semibold">{{ count($importErrors) }} baris dilewati:</p>
                            <ul class="list-disc space-y-0.5 pl-4">
                                @foreach ($importErrors as $e) <li>{{ $e }}</li> @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-card>
@endif
