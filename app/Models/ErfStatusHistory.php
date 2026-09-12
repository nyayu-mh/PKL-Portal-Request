<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErfStatusHistory extends Model
{
    protected $table = 'erf_status_histories';

    protected $fillable = ['erf_request_id', 'status', 'changed_by_user_id', 'catatan'];

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_user_id');
    }
}
