<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GaVendorQuotation extends Model
{
    protected $table = 'ga_vendor_quotations';

    protected $fillable = ['ga_request_id', 'nama_vendor', 'file_path', 'harga_penawaran', 'is_terpilih'];

    protected function casts(): array
    {
        return [
            'harga_penawaran' => 'decimal:2',
            'is_terpilih' => 'boolean',
        ];
    }

    public function gaRequest(): BelongsTo
    {
        return $this->belongsTo(GaRequest::class);
    }
}
