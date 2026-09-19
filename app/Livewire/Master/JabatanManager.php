<?php

namespace App\Livewire\Master;

use App\Livewire\Concerns\WithCsvImport;
use App\Models\MasterDivisi;
use App\Models\MasterJabatan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class JabatanManager extends Component
{
    use WithCsvImport, WithFileUploads, WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $nama_jabatan = '';

    public ?int $master_divisi_id = null;

    /** Nama divisi baru yang diketik manual (dibuat di Master Data Divisi saat disimpan). */
    public string $divisi_baru = '';

    public bool $is_active = true;

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'nama_jabatan' => ['required', 'string', 'max:150'],
            'master_divisi_id' => ['nullable', 'exists:master_divisi,id'],
            'divisi_baru' => ['nullable', 'string', 'max:150'],
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
        $j = MasterJabatan::findOrFail($id);
        $this->editingId = $j->id;
        $this->nama_jabatan = $j->nama_jabatan;
        $this->master_divisi_id = $j->master_divisi_id;
        $this->is_active = $j->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $divisiBaru = trim($data['divisi_baru'] ?? '');
        unset($data['divisi_baru']);
        if ($divisiBaru !== '') {
            // Cocokkan tanpa membedakan huruf besar/kecil supaya tidak muncul divisi ganda.
            $divisi = MasterDivisi::whereRaw('LOWER(nama_divisi) = ?', [mb_strtolower($divisiBaru)])->first()
                ?? MasterDivisi::create(['nama_divisi' => $divisiBaru]);
            $data['master_divisi_id'] = $divisi->id;
        }

        MasterJabatan::updateOrCreate(['id' => $this->editingId], $data);

        session()->flash('success', 'Data jabatan berhasil disimpan.');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        MasterJabatan::findOrFail($id)->delete();
        session()->flash('success', 'Data jabatan berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'nama_jabatan', 'master_divisi_id', 'divisi_baru', 'showForm']);
        $this->is_active = true;
        $this->resetErrorBag();
    }

    // ── Import CSV ────────────────────────────────────────────────

    public function csvTemplateName(): string
    {
        return 'template-jabatan.csv';
    }

    public function csvTemplateHeaders(): array
    {
        return ['nama_jabatan', 'nama_divisi', 'is_active'];
    }

    public function csvTemplateExample(): array
    {
        return [
            ['Finance Manager', 'Finance', 'aktif'],
            ['Software Engineer', 'IT', 'aktif'],
        ];
    }

    public function csvImportHint(): string
    {
        return 'Kolom "nama_divisi" (opsional) diisi nama divisi yang sudah ada di Master Data > Divisi (dibuat otomatis kalau belum ada). '
            .'"is_active": aktif / nonaktif (boleh kosong, default aktif). Data dicocokkan berdasarkan nama jabatan.';
    }

    public function importCsvRow(array $row, int $line): void
    {
        $nama = (string) ($row['nama_jabatan'] ?? '');
        if ($nama === '') {
            throw new \RuntimeException('kolom "nama_jabatan" wajib diisi.');
        }

        $divisiId = null;
        $divisiNama = (string) ($row['nama_divisi'] ?? '');
        if ($divisiNama !== '') {
            $divisi = MasterDivisi::firstOrCreate(['nama_divisi' => $divisiNama]);
            $divisiId = $divisi->id;
        }

        MasterJabatan::updateOrCreate(
            ['nama_jabatan' => $nama],
            [
                'master_divisi_id' => $divisiId,
                'is_active' => $this->csvBool($row['is_active'] ?? '', true),
            ],
        );
    }

    public function render()
    {
        return view('livewire.master.jabatan-manager', [
            'items' => MasterJabatan::with('divisi')
                ->when($this->search, fn ($q) => $q->where('nama_jabatan', 'like', "%{$this->search}%"))
                ->orderBy('nama_jabatan')->paginate(15),
            'divisiOptions' => MasterDivisi::orderBy('nama_divisi')->get(),
        ]);
    }
}
