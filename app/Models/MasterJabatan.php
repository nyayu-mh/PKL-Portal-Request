<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterJabatan extends Model
{
    protected $table = 'master_jabatan';

    protected $fillable = ['nama_jabatan', 'master_divisi_id', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(MasterDivisi::class, 'master_divisi_id');
    }
}
