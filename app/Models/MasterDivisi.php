<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function jabatans(): HasMany
    {
        return $this->hasMany(MasterJabatan::class, 'master_divisi_id');
    }
}
