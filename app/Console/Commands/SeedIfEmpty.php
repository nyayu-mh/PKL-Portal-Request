<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Jalankan DatabaseSeeder hanya kalau tabel users masih kosong.
 * Aman dipanggil berulang (mis. tiap kali container start saat deploy) —
 * tidak akan menggandakan data atau error kalau sudah pernah seed sebelumnya.
 */
class SeedIfEmpty extends Command
{
    protected $signature = 'app:seed-if-empty';

    protected $description = 'Jalankan db:seed hanya jika tabel users masih kosong (aman dipanggil berulang).';

    public function handle(): int
    {
        if (User::count() > 0) {
            $this->info('Sudah ada data user — seeding dilewati.');

            return self::SUCCESS;
        }

        $this->info('Tabel users masih kosong — menjalankan db:seed...');
        $this->call('db:seed', ['--force' => true]);

        return self::SUCCESS;
    }
}
