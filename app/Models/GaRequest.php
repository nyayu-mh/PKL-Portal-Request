<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GaRequest extends Model
{
    protected $table = 'ga_requests';

    /**
     * ATURAN AKSES REQUEST GA — satu-satunya sumber kebenaran (list, dashboard, detail).
     * User hanya boleh melihat request GA:
     *  - yang ia buat sendiri, atau
     *  - yang atasan langsungnya adalah dia (untuk approval), atau
     *  - kalau ia Tim GA: semua request yang approval atasannya sudah beres (disetujui / tidak perlu approval).
     * Super Admin bisa melihat semuanya. User bercentang "lihat semua request" (mis. Manager HRBP) juga
     * melihat semua request yang approval atasannya sudah beres, sama seperti Tim GA. Tim HR sama sekali tidak punya akses ke modul GA.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if (! $user->canAccessGa()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('atasan_user_id', $user->id);

            if ($user->isGa() || $user->lihat_semua_request) {
                $q->orWhereIn('approval_status', ['disetujui', 'tidak_perlu']);
            }
        });
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->canAccessGa()
            && ($this->user_id === $user->id
                || $this->atasan_user_id === $user->id
                || (($user->isGa() || $user->lihat_semua_request) && $this->isApprovedForProcessing()));
    }

    protected $fillable = [
        'ga_id', 'user_id', 'tanggal_request', 'jenis_request', 'judul', 'deskripsi', 'lokasi_kerja',
        'lampiran_bukti_kondisi', 'lampiran_rekomendasi_vendor', 'catatan',
        'approval_status', 'atasan_user_id', 'catatan_approval_atasan', 'approved_at',
        'pic_ga_user_id', 'butuh_vendor', 'nama_vendor', 'catatan_rab', 'tanggal_mulai_proses',
        'status', 'tanggal_selesai', 'realisasi_biaya', 'catatan_internal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_request' => 'date',
            'tanggal_mulai_proses' => 'date',
            'tanggal_selesai' => 'date',
            'butuh_vendor' => 'boolean',
            'realisasi_biaya' => 'decimal:2',
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

    public function isApprovedForProcessing(): bool
    {
        return in_array($this->approval_status, ['tidak_perlu', 'disetujui']);
    }

    public static function jenisLabels(): array
    {
        return [
            'maintenance' => 'Maintenance',
            'pengadaan_barang' => 'Pengadaan Barang',
            'office_acquisition' => 'Office Acquisition',
            'renovasi_kantor' => 'Renovasi Kantor',
        ];
    }

    public static function lokasiLabels(): array
    {
        return [
            'head_office' => 'Head Office',
            'crm_office' => 'CRM Office',
            'pdn_office' => 'PDN Office',
            'gudang_palembang' => 'Gudang Palembang',
            'gudang_jakarta' => 'Gudang Jakarta',
            'gudang_pekalongan' => 'Gudang Pekalongan',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'diproses_ga' => 'Diproses GA',
            'vendor_diorder' => 'Vendor Diorder',
            'proses_vendor' => 'Proses Vendor',
            'barang_diterima' => 'Barang Diterima',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
        ];
    }

    public function jenisLabel(): string
    {
        return self::jenisLabels()[$this->jenis_request] ?? $this->jenis_request;
    }

    public function lokasiLabel(): string
    {
        return self::lokasiLabels()[$this->lokasi_kerja] ?? $this->lokasi_kerja;
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? $this->status;
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'pending' => 'gray',
            'diproses_ga' => 'amber',
            'vendor_diorder', 'proses_vendor' => 'blue',
            'barang_diterima' => 'indigo',
            'selesai' => 'emerald',
            'ditolak', 'dibatalkan' => 'rose',
            default => 'gray',
        };
    }

    public function pemohon(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function picGa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_ga_user_id');
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_user_id');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(GaVendorQuotation::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(GaStatusHistory::class)->latest();
    }
}
