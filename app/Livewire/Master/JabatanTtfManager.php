<?php

namespace App\Livewire\Master;

use App\Livewire\Concerns\WithCsvImport;
use App\Models\MasterDivisi;
use App\Models\MasterJabatanTtf;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class JabatanTtfManager extends Component
{
    use WithCsvImport, WithFileUploads, WithPagination;

    public ?int $editingId = null;

    public string $nama_jabatan = '';

    public string $divisi = '';

    public ?int $pic_hr_user_id = null;

    public int $target_hari_kerja = 14;

    public bool $is_active = true;

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'nama_jabatan' => ['required', 'string', 'max:150'],
            'divisi' => ['nullable', 'string', 'max:100'],
            'pic_hr_user_id' => ['nullable', 'exists:users,id'],
            'target_hari_kerja' => ['required', 'integer', 'min:1'],
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
        $j = MasterJabatanTtf::findOrFail($id);
        $this->editingId = $j->id;
        $this->nama_jabatan = $j->nama_jabatan;
        $this->divisi = (string) $j->divisi;
        $this->pic_hr_user_id = $j->pic_hr_user_id;
        $this->target_hari_kerja = $j->target_hari_kerja;
        $this->is_active = $j->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        MasterJabatanTtf::updateOrCreate(['id' => $this->editingId], $data);

        session()->flash('success', 'Data jabatan & TTF berhasil disimpan.');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        MasterJabatanTtf::findOrFail($id)->delete();
        session()->flash('success', 'Data jabatan & TTF berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'nama_jabatan', 'divisi', 'pic_hr_user_id', 'showForm']);
        $this->target_hari_kerja = 14;
        $this->is_active = true;
        $this->resetErrorBag();
    }

    // ── Import CSV ────────────────────────────────────────────────

    public function csvTemplateName(): string
    {
        return 'template-jabatan-ttf.csv';
    }

    public function csvTemplateHeaders(): array
    {
        return ['nama_jabatan', 'divisi', 'pic_hr_email', 'target_hari_kerja', 'is_active'];
    }

    public function csvTemplateExample(): array
    {
        return [
            ['Staff IT Support', 'IT', 'hr@brilliantthinkcenter.com', '14', 'aktif'],
            ['Customer Relationship Officer', 'CRM', '', '21', 'aktif'],
        ];
    }

    public function csvImportHint(): string
    {
        return 'Kolom "pic_hr_email": email user ber-role Tim HR (boleh kosong). '
            .'"target_hari_kerja": angka hari kerja (kosong/0 dianggap 14). "is_active": aktif / nonaktif. '
            .'Baris dicocokkan berdasarkan nama jabatan.';
    }

    public function importCsvRow(array $row, int $line): void
    {
        $nama = (string) ($row['nama_jabatan'] ?? '');
        if ($nama === '') {
            throw new \RuntimeException('kolom "nama_jabatan" wajib diisi.');
        }

        $picId = null;
        $picEmail = (string) ($row['pic_hr_email'] ?? '');
        if ($picEmail !== '') {
            $pic = User::where('email', $picEmail)->first();
            if (! $pic) {
                throw new \RuntimeException("PIC HR dengan email \"{$picEmail}\" tidak ada di data user.");
            }
            if ($pic->role !== 'hr') {
                $this->importNote("Baris {$line}: \"{$picEmail}\" bukan role Tim HR, tetap dipakai sebagai PIC.");
            }
            $picId = $pic->id;
        }

        $target = (int) ($row['target_hari_kerja'] ?? 0);
        if ($target < 1) {
            $target = 14;
        }

        MasterJabatanTtf::updateOrCreate(
            ['nama_jabatan' => $nama],
            [
                'divisi' => ($row['divisi'] ?? '') !== '' ? $row['divisi'] : null,
                'pic_hr_user_id' => $picId,
                'target_hari_kerja' => $target,
                'is_active' => $this->csvBool($row['is_active'] ?? '', true),
            ],
        );
    }

    public function render()
    {
        return view('livewire.master.jabatan-ttf-manager', [
            'items' => MasterJabatanTtf::with('picHr')->orderBy('nama_jabatan')->paginate(10),
            'hrUsers' => User::where('role', 'hr')->orderBy('name')->get(),
            'divisiPilihan' => MasterDivisi::daftarPilihan(),
        ]);
    }
}
