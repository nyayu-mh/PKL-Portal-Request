<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ErfRequest extends Model
{
    protected $table = 'erf_requests';

    /**
     * ATURAN AKSES ERF (data sensitif) — satu-satunya sumber kebenaran, dipakai di list,
     * dashboard, dan halaman detail. User hanya boleh melihat ERF:
     *  - yang ia buat sendiri, atau
     *  - yang atasan langsungnya adalah dia (untuk approval), atau
     *  - kalau ia Tim HR: semua ERF yang approval atasannya sudah beres (disetujui / tidak perlu approval).
     * Super Admin bisa melihat semuanya. Tim GA sama sekali tidak punya akses ke modul ERF.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if (! $user->canAccessErf()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('atasan_user_id', $user->id);

            if ($user->isHr()) {
                $q->orWhereIn('approval_status', ['disetujui', 'tidak_perlu']);
            }
        });
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->canAccessErf()
            && ($this->user_id === $user->id
                || $this->atasan_user_id === $user->id
                || ($user->isHr() && $this->isApprovedForProcessing()));
    }

    protected $fillable = [
        'erf_id', 'user_id', 'tanggal_request', 'jenis_erf', 'jumlah_karyawan',
        'jabatan_dibutuhkan_id', 'uraian_tugas', 'kualifikasi_kandidat', 'catatan',
        'karyawan_diganti_id', 'alasan_penggantian',
        'approval_status', 'atasan_user_id', 'catatan_approval_atasan', 'approved_at',
        'pic_hr_user_id', 'ttf_hari', 'tanggal_mencari_kandidat', 'estimasi_tanggal_fulfillment',
        'status', 'tanggal_selesai', 'tanggal_karyawan_masuk',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_request' => 'date',
            'tanggal_mencari_kandidat' => 'date',
            'estimasi_tanggal_fulfillment' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_karyawan_masuk' => 'date',
            'approved_at' => 'datetime',
        ];
    }

    public static function approvalStatusLabels(): array
    {
        return [
            'tidak_perlu' => 'Tidak Perlu Approval',
            'menunggu' => 'Menunggu Approval Atasan',
            'disetujui' => 'Disetujui Atasan',
            'revisi' => 'Perlu Revisi',
            'ditolak' => 'Ditolak',
        ];
    }

    public function approvalStatusLabel(): string
    {
        return self::approvalStatusLabels()[$this->approval_status] ?? $this->approval_status;
    }

    public function approvalStatusBadgeColor(): string
    {
        return match ($this->approval_status) {
            'tidak_perlu' => 'gray',
            'menunggu' => 'amber',
            'disetujui' => 'emerald',
            'revisi' => 'orange',
            'ditolak' => 'rose',
            default => 'gray',
        };
    }

    /**
     * Ditolak final oleh atasan — case closed, tidak bisa direvisi/diajukan ulang lagi.
     */
    public function isRejectedFinal(): bool
    {
        return $this->approval_status === 'ditolak';
    }

    /**
     * Sudah boleh diproses HR? (approval atasan sudah beres / memang tidak perlu)
     */
    public function isApprovedForProcessing(): bool
    {
        return in_array($this->approval_status, ['tidak_perlu', 'disetujui']);
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'in_review' => 'In Review',
            'approved' => 'Approved',
            'on_progress' => 'On Progress',
            'interview_hr' => 'Interview HR',
            'interview_user' => 'Interview User',
            'offering' => 'Offering',
            'kandidat_fix' => 'Kandidat Fix',
            'completed' => 'Completed',
            'on_hold' => 'On Hold',
            'rejected' => 'Rejected',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'pending' => 'gray',
            'in_review' => 'amber',
            'approved' => 'blue',
            'on_progress', 'interview_hr', 'interview_user', 'offering', 'kandidat_fix' => 'indigo',
            'completed' => 'emerald',
            'on_hold' => 'orange',
            'rejected' => 'rose',
            default => 'gray',
        };
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jabatanDibutuhkan(): BelongsTo
    {
        return $this->belongsTo(MasterJabatanTtf::class, 'jabatan_dibutuhkan_id');
    }

    public function karyawanDiganti(): BelongsTo
    {
        return $this->belongsTo(MasterKaryawan::class, 'karyawan_diganti_id');
    }

    public function picHr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_hr_user_id');
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_user_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ErfStatusHistory::class)->latest();
    }
}
