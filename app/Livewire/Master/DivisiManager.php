<?php

namespace App\Livewire\Master;

use App\Livewire\Concerns\WithCsvImport;
use App\Models\MasterDivisi;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class DivisiManager extends Component
{
    use WithCsvImport, WithFileUploads, WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $nama_divisi = '';

    public bool $is_active = true;

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'nama_divisi' => ['required', 'string', 'max:150'],
            'is_active' => ['boolean'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $d = MasterDivisi::findOrFail($id);
        $this->editingId = $d->id;
        $this->nama_divisi = $d->nama_divisi;
        $this->is_active = $d->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        MasterDivisi::updateOrCreate(['id' => $this->editingId], $data);

        session()->flash('success', 'Data divisi berhasil disimpan.');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $d = MasterDivisi::findOrFail($id);

        if ($d->jabatans()->exists()) {
            session()->flash('error', "Divisi \"{$d->nama_divisi}\" masih dipakai oleh data Jabatan — hapus/pindahkan dulu jabatan di dalamnya.");

            return;
        }

        $d->delete();
        session()->flash('success', 'Data divisi berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'nama_divisi', 'showForm']);
        $this->is_active = true;
        $this->resetErrorBag();
    }

    // ── Import CSV ────────────────────────────────────────────────

    public function csvTemplateName(): string
    {
        return 'template-divisi.csv';
    }

    public function csvTemplateHeaders(): array
    {
        return ['nama_divisi', 'is_active'];
    }

    public function csvTemplateExample(): array
    {
        return [
            ['Finance', 'aktif'],
            ['Supply Chain', 'aktif'],
        ];
    }

    public function csvImportHint(): string
    {
        return 'Kolom "is_active": aktif / nonaktif (boleh kosong, default aktif). Data dicocokkan berdasarkan nama divisi.';
    }

    public function importCsvRow(array $row, int $line): void
    {
        $nama = (string) ($row['nama_divisi'] ?? '');
        if ($nama === '') {
            throw new \RuntimeException('kolom "nama_divisi" wajib diisi.');
        }

        MasterDivisi::updateOrCreate(
            ['nama_divisi' => $nama],
            ['is_active' => $this->csvBool($row['is_active'] ?? '', true)],
        );
    }

    public function render()
    {
        return view('livewire.master.divisi-manager', [
            'items' => MasterDivisi::when($this->search, fn ($q) => $q->where('nama_divisi', 'like', "%{$this->search}%"))
                ->orderBy('nama_divisi')->paginate(15),
        ]);
    }
}
