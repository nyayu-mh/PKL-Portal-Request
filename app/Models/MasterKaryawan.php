<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKaryawan extends Model
{
    protected $table = 'master_karyawan';

    protected $fillable = ['nama', 'email', 'divisi', 'jabatan', 'status_aktif'];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
        ];
    }

    public function getDivisiJabatanAttribute(): string
    {
        return trim(($this->divisi ?: '-').' - '.($this->jabatan ?: '-'));
    }
}
