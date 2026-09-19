<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class MasterDivisi extends Model
{
    protected $table = 'master_divisi';

    protected $fillable = ['nama_divisi', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Daftar nama divisi untuk dropdown: gabungan Master Data Divisi + divisi yang sudah dipakai di
     * User, Data Karyawan, dan Jabatan & TTF (tanpa duplikat, urut abjad).
     *
     * @return array<string, string> nama => nama
     */
    public static function daftarPilihan(): array
    {
        $names = static::query()->where('is_active', true)->pluck('nama_divisi')
            ->merge(DB::table('users')->pluck('divisi'))
            ->merge(DB::table('master_karyawan')->pluck('divisi'))
            ->merge(DB::table('master_jabatan_ttf')->pluck('divisi'));

        $unique = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name !== '' && ! isset($unique[mb_strtolower($name)])) {
                $unique[mb_strtolower($name)] = $name;
            }
        }

        $list = array_values($unique);
        natcasesort($list);
        $list = array_values($list);

        return array_combine($list, $list);
    }

    public function jabatans(): HasMany
    {
        return $this->hasMany(MasterJabatan::class, 'master_divisi_id');
    }
}
