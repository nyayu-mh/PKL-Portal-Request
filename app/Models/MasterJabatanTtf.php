<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterJabatanTtf extends Model
{
    protected $table = 'master_jabatan_ttf';

    protected $fillable = ['nama_jabatan', 'divisi', 'pic_hr_user_id', 'target_hari_kerja', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function picHr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_hr_user_id');
    }
}
