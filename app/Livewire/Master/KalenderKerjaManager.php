<?php

namespace App\Livewire\Master;

use App\Livewire\Concerns\WithCsvImport;
use App\Models\KalenderKerja;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class KalenderKerjaManager extends Component
{
    use WithCsvImport, WithFileUploads, WithPagination;

    public ?int $editingId = null;

    public string $tanggal = '';

    public string $keterangan = '';

    public string $tipe = 'libur';

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:150'],
            'tipe' => ['required', 'in:libur,kerja'],
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $k = KalenderKerja::findOrFail($id);
        $this->editingId = $k->id;
        $this->tanggal = $k->tanggal->toDateString();
        $this->keterangan = (string) $k->keterangan;
        $this->tipe = $k->tipe;
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        if (! $this->editingId) {
            $data['tanggal'] = $this->tanggal;
            $exists = KalenderKerja::where('tanggal', $this->tanggal)->exists();
            if ($exists) {
                $this->addError('tanggal', 'Tanggal ini sudah terdaftar di kalender kerja.');

                return;
            }
        }

        KalenderKerja::updateOrCreate(['id' => $this->editingId], $data);

        session()->flash('success', 'Kalender kerja berhasil disimpan.');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        KalenderKerja::findOrFail($id)->delete();
        session()->flash('success', 'Kalender kerja berhasil dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'tanggal', 'keterangan', 'showForm']);
        $this->tipe = 'libur';
        $this->resetErrorBag();
    }

    // ── Import CSV ────────────────────────────────────────────────

    public function csvTemplateName(): string
    {
        return 'template-kalender-kerja.csv';
    }

    public function csvTemplateHeaders(): array
    {
        return ['tanggal', 'tipe', 'keterangan'];
    }

    public function csvTemplateExample(): array
    {
        return [
            ['2026-01-01', 'libur', 'Tahun Baru Masehi'],
            ['2026-03-19', 'libur', 'Cuti Bersama'],
            ['2026-04-11', 'kerja', 'Sabtu masuk pengganti'],
        ];
    }

    public function csvImportHint(): string
    {
        return 'Format "tanggal": YYYY-MM-DD (mis. 2026-01-01) atau DD/MM/YYYY. '
            .'"tipe": libur atau kerja (kosong = libur). Tanggal yang sudah terdaftar akan diperbarui.';
    }

    public function importCsvRow(array $row, int $line): void
    {
        $raw = (string) ($row['tanggal'] ?? '');
        if ($raw === '') {
            throw new \RuntimeException('kolom "tanggal" wajib diisi.');
        }

        try {
            $tanggal = $this->csvDate($raw);
        } catch (\Throwable $e) {
            throw new \RuntimeException("tanggal \"{$raw}\" tidak dikenali (pakai format YYYY-MM-DD).");
        }

        $tipe = Str::lower(trim((string) ($row['tipe'] ?? 'libur')));
        if (! in_array($tipe, ['libur', 'kerja'], true)) {
            $tipe = 'libur';
        }

        KalenderKerja::updateOrCreate(
            ['tanggal' => $tanggal],
            [
                'tipe' => $tipe,
                'keterangan' => ($row['keterangan'] ?? '') !== '' ? $row['keterangan'] : null,
            ],
        );
    }

    public function render()
    {
        return view('livewire.master.kalender-kerja-manager', [
            'items' => KalenderKerja::orderBy('tanggal')->paginate(15),
        ]);
    }
}
