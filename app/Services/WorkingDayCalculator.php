<?php

namespace App\Services;

use App\Models\KalenderKerja;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Helper untuk menghitung tanggal berbasis HARI KERJA (bukan hari kalender biasa).
 *
 * Aturan default: Senin–Jumat = hari kerja, Sabtu & Minggu = libur.
 * Aturan itu bisa di-override lewat tabel master `kalender_kerja`:
 *  - tipe "libur" -> tanggal tsb dianggap LIBUR walau hari kerja biasa (mis. cuti bersama/hari raya)
 *  - tipe "kerja" -> tanggal tsb dianggap MASUK walau weekend (mis. Sabtu pengganti)
 */
class WorkingDayCalculator
{
    protected ?Collection $overrides = null;

    protected function overrides(): Collection
    {
        if ($this->overrides === null) {
            $this->overrides = KalenderKerja::query()->get()->keyBy(fn ($row) => $row->tanggal->toDateString());
        }

        return $this->overrides;
    }

    public function isWorkingDay(Carbon $date): bool
    {
        $override = $this->overrides()->get($date->toDateString());

        if ($override) {
            return $override->tipe === 'kerja';
        }

        return ! $date->isWeekend();
    }

    /**
     * Tanggal hari kerja berikutnya setelah $date (tidak termasuk $date itu sendiri).
     */
    public function nextWorkingDay(Carbon $date): Carbon
    {
        return $this->addWorkingDays($date, 1);
    }

    /**
     * "Tanggal efektif" untuk mulai dihitung H+1, dengan aturan jam cut-off.
     * Jika request masuk jam $cutoffHour (default 16.00 / jam 4 sore) atau lebih,
     * dianggap masuk di HARI BERIKUTNYA (baru mulai dihitung besok).
     */
    public function effectiveBaseDate(Carbon $submittedAt, int $cutoffHour = 16): Carbon
    {
        $base = $submittedAt->copy()->startOfDay();

        if ($submittedAt->hour >= $cutoffHour) {
            $base = $base->addDay();
        }

        return $base;
    }

    /**
     * Menambahkan $days hari kerja dari $date (tidak termasuk $date itu sendiri).
     */
    public function addWorkingDays(Carbon $date, int $days): Carbon
    {
        $result = $date->copy();
        $counted = 0;

        while ($counted < $days) {
            $result = $result->addDay();
            if ($this->isWorkingDay($result)) {
                $counted++;
            }
        }

        return $result;
    }
}
