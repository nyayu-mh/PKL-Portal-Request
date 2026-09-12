<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KalenderKerja extends Model
{
    protected $table = 'kalender_kerja';

    protected $fillable = ['tanggal', 'keterangan', 'tipe'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }
}
