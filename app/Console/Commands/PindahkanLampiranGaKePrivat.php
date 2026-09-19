<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PindahkanLampiranGaKePrivat extends Command
{
    protected $signature = 'ga:pindahkan-lampiran-privat';

    protected $description = 'Pindahkan lampiran GA lama dari disk publik (bisa dibuka lewat URL) ke disk privat (dicek hak akses).';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $moved = 0;

        foreach ($public->allFiles('ga') as $path) {
            if ($private->exists($path)) {
                $this->warn("Lewati (sudah ada di privat): {$path}");

                continue;
            }

            $private->writeStream($path, $public->readStream($path));
            $public->delete($path);
            $moved++;
        }

        $this->info("Selesai. {$moved} file dipindahkan ke penyimpanan privat.");

        return self::SUCCESS;
    }
}
