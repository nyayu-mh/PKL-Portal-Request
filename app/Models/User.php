<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'divisi', 'jabatan', 'jabatan_level', 'role', 'atasan_id', 'is_active',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ── Label bantu ────────────────────────────────────────────────

    public function getDivisiJabatanAttribute(): string
    {
        return trim(($this->divisi ?: '-').' - '.($this->jabatan ?: '-'));
    }

    public static function jabatanLevelLabels(): array
    {
        return [
            'staff' => 'Staff',
            'junior_leader' => 'Junior Leader',
            'leader' => 'Leader',
            'manager' => 'Manager',
        ];
    }

    public static function roleLabels(): array
    {
        return [
            'karyawan' => 'Karyawan',
            'hr' => 'Tim HR',
            'ga' => 'Tim General Affair',
            'admin' => 'Administrator',
        ];
    }

    public function jabatanLevelLabel(): string
    {
        return self::jabatanLevelLabels()[$this->jabatan_level] ?? $this->jabatan_level;
    }

    public function roleLabel(): string
    {
        return self::roleLabels()[$this->role] ?? $this->role;
    }

    // ── Helper hak akses ──────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isHr(): bool
    {
        return $this->role === 'hr';
    }

    public function isGa(): bool
    {
        return $this->role === 'ga';
    }

    /**
     * ERF hanya boleh dibuat oleh Leader & Manager (sesuai definisi ERF).
     */
    public function canCreateErf(): bool
    {
        return in_array($this->jabatan_level, ['leader', 'manager']) || $this->isAdmin();
    }

    /**
     * Request GA hanya boleh dibuat oleh Junior Leader, Leader & Manager. Staff tidak bisa.
     */
    public function canCreateGa(): bool
    {
        return in_array($this->jabatan_level, ['junior_leader', 'leader', 'manager']) || $this->isAdmin();
    }

    /**
     * Approval atasan (di aplikasi) wajib untuk level Leader ke bawah (Junior Leader & Leader),
     * tidak wajib untuk Manager (dianggap sudah level tertinggi/approver).
     */
    public function needsAtasanApproval(): bool
    {
        return in_array($this->jabatan_level, ['junior_leader', 'leader']);
    }

    public function atasan(): BelongsTo
    {
        return $this->belongsTo(User::class, 'atasan_id');
    }
}
