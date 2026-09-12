<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Menyediakan alur "Import CSV" yang bisa dipakai ulang di halaman Master Data.
 *
 * Komponen yang memakai trait ini WAJIB:
 *  - memakai trait Livewire\WithFileUploads
 *  - mengimplementasikan 5 method abstrak di bawah
 */
trait WithCsvImport
{
    public bool $showImport = false;

    /** File CSV yang di-upload (Livewire\TemporaryUploadedFile). Jangan diberi type-hint. */
    public $csvFile;

    public int $importedCount = 0;

    /** @var string[] baris yang gagal & dilewati */
    public array $importErrors = [];

    /** @var string[] catatan / peringatan ringan (baris tetap diimport) */
    public array $importNotes = [];

    abstract public function csvTemplateName(): string;

    /** @return string[] daftar nama kolom (baris judul) */
    abstract public function csvTemplateHeaders(): array;

    /** @return array<int, array<int, string>> contoh baris isi untuk template */
    abstract public function csvTemplateExample(): array;

    abstract public function csvImportHint(): string;

    /**
     * Simpan satu baris CSV. Lempar \RuntimeException dengan pesan singkat
     * kalau baris tidak valid (baris akan dilewati & dilaporkan).
     *
     * @param  array<string, string>  $row  data baris, key = nama kolom (lowercase)
     */
    abstract public function importCsvRow(array $row, int $line): void;

    public function toggleImport(): void
    {
        $this->showImport = ! $this->showImport;
        $this->reset(['csvFile', 'importedCount', 'importErrors', 'importNotes']);
        $this->resetErrorBag('csvFile');
    }

    public function downloadTemplate()
    {
        $headers = $this->csvTemplateHeaders();
        $example = $this->csvTemplateExample();
        $name = $this->csvTemplateName();

        return response()->streamDownload(function () use ($headers, $example) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM supaya Excel membaca UTF-8 dengan benar
            fputcsv($out, $headers);
            foreach ($example as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function import(): void
    {
        $this->validate(
            ['csvFile' => ['required', 'file', 'max:4096']],
            [],
            ['csvFile' => 'file CSV'],
        );

        $ext = strtolower($this->csvFile->getClientOriginalExtension());
        if (! in_array($ext, ['csv', 'txt'], true)) {
            $this->addError('csvFile', 'Format file harus .csv (atau .txt). Dari Excel/Spreadsheet pilih "Save As / Download as CSV".');

            return;
        }

        $this->importedCount = 0;
        $this->importErrors = [];
        $this->importNotes = [];

        $handle = fopen($this->csvFile->getRealPath(), 'r');
        if ($handle === false) {
            $this->addError('csvFile', 'File tidak bisa dibaca. Coba simpan ulang lalu upload lagi.');

            return;
        }

        // Deteksi pemisah kolom: koma (,) atau titik-koma (;) — Excel Indonesia sering pakai ";".
        $sample = (string) fgets($handle);
        rewind($handle);
        $delimiter = substr_count($sample, ';') > substr_count($sample, ',') ? ';' : ',';

        $expected = array_map(fn ($h) => Str::lower(trim($h)), $this->csvTemplateHeaders());
        $headers = null;
        $line = 0;
        $success = 0;

        while (($cols = fgetcsv($handle, 0, $delimiter)) !== false) {
            $line++;

            if ($cols === null || count(array_filter($cols, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue; // baris kosong
            }

            if ($headers === null) {
                $headers = array_map(function ($h) {
                    $h = preg_replace('/^\xEF\xBB\xBF/', '', (string) $h);

                    return Str::lower(trim($h));
                }, $cols);

                $missing = array_diff($expected, $headers);
                if (count($missing) > 0) {
                    $this->addError('csvFile', 'Kolom wajib tidak ada di baris judul: '.implode(', ', $missing).'. Pakai file template yang disediakan.');
                    fclose($handle);

                    return;
                }

                continue;
            }

            $cols = array_pad(array_slice($cols, 0, count($headers)), count($headers), '');
            $row = [];
            foreach ($headers as $i => $key) {
                $row[$key] = is_string($cols[$i]) ? trim($cols[$i]) : $cols[$i];
            }

            try {
                $this->importCsvRow($row, $line);
                $success++;
            } catch (\Throwable $e) {
                $this->importErrors[] = "Baris {$line}: ".$e->getMessage();
            }
        }

        fclose($handle);

        $this->importedCount = $success;
        $this->reset('csvFile');

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }

        if ($success > 0) {
            session()->flash('success', "{$success} baris berhasil diimport dari CSV.");
        } elseif (count($this->importErrors) === 0) {
            $this->addError('csvFile', 'Tidak ada baris data yang terbaca. Pastikan ada data di bawah baris judul.');
        }
    }

    protected function importNote(string $message): void
    {
        $this->importNotes[] = $message;
    }

    protected function csvBool($value, bool $default = true): bool
    {
        $v = Str::lower(trim((string) $value));
        if ($v === '') {
            return $default;
        }

        return in_array($v, ['1', 'true', 'ya', 'yes', 'y', 'aktif', 'active'], true);
    }

    protected function csvDate(string $raw): string
    {
        $raw = trim($raw);

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'm/d/Y'] as $fmt) {
            $d = \DateTime::createFromFormat('!'.$fmt, $raw);
            if ($d !== false && $d->format($fmt) === $raw) {
                return $d->format('Y-m-d');
            }
        }

        return Carbon::parse($raw)->toDateString(); // fallback; melempar kalau tidak dikenali
    }
}
