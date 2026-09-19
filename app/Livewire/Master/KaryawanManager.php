<?php

namespace App\Livewire\Master;

use App\Livewire\Concerns\WithCsvImport;
use App\Models\MasterDivisi;
use App\Models\MasterKaryawan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class KaryawanManager extends Component
{
    use WithCsvImport, WithFileUploads, WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public string $nama = '';

    public string $email = '';

    public string $divisi = '';

    public string $jabatan = '';

    public bool $status_aktif = true;

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150'],
            'divisi' => ['nullable', 'string', 'max:100'],
            'jabatan' => ['nullable', 'string', 'max:100'],
            'status_aktif' => ['boolean'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $k = MasterKaryawan::findOrFail($id);
        $this->editingId = $k->id;
        $this->nama = $k->nama;
        $this->email = (string) $k->email;
        $this->divisi = (string) $k->divisi;
        $this->jabatan = (string) $k->jabatan;
        $this->status_aktif = $k->status_aktif;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        MasterKaryawan::updateOrCreate(['id' => $this->editingId], $data);

        session()->flash('success', 'Data karyawan berhasil disimpan.');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        MasterKaryawan::findOrFail($id)->delete();
        session()->flash('success', 'Data karyawan berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'nama', 'email', 'divisi', 'jabatan', 'showForm']);
        $this->status_aktif = true;
        $this->resetErrorBag();
    }

    // ── Import CSV ────────────────────────────────────────────────

    public function csvTemplateName(): string
    {
        return 'template-karyawan.csv';
    }

    public function csvTemplateHeaders(): array
    {
        return ['nama', 'email', 'divisi', 'jabatan', 'status_aktif'];
    }

    public function csvTemplateExample(): array
    {
        return [
            ['Budi Santoso', 'budi@contoh.com', 'IT', 'Staff IT Support', 'aktif'],
            ['Siti Aminah', '', 'Finance', 'Staff Finance', 'aktif'],
        ];
    }

    public function csvImportHint(): string
    {
        return 'Kolom "status_aktif": isi aktif / nonaktif (boleh kosong, default aktif). '
            .'Baris dicocokkan berdasarkan email (kalau ada) atau nama — data yang cocok diperbarui, bukan digandakan.';
    }

    public function importCsvRow(array $row, int $line): void
    {
        $nama = (string) ($row['nama'] ?? '');
        if ($nama === '') {
            throw new \RuntimeException('kolom "nama" wajib diisi.');
        }

        $email = (string) ($row['email'] ?? '');
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException("email \"{$email}\" tidak valid.");
        }

        $data = [
            'nama' => $nama,
            'email' => $email !== '' ? $email : null,
            'divisi' => ($row['divisi'] ?? '') !== '' ? $row['divisi'] : null,
            'jabatan' => ($row['jabatan'] ?? '') !== '' ? $row['jabatan'] : null,
            'status_aktif' => $this->csvBool($row['status_aktif'] ?? '', true),
        ];

        $match = $email !== '' ? ['email' => $email] : ['nama' => $nama];

        MasterKaryawan::updateOrCreate($match, $data);
    }

    public function render()
    {
        return view('livewire.master.karyawan-manager', [
            'divisiPilihan' => MasterDivisi::daftarPilihan(),
            'items' => MasterKaryawan::when($this->search, fn ($q) => $q->where('nama', 'like', "%{$this->search}%"))
                ->orderBy('nama')->paginate(10),
        ]);
    }
}
