<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuPermission extends Model
{
    protected $fillable = ['role', 'menu_key', 'can_view', 'can_create', 'can_update', 'can_delete'];

    protected function casts(): array
    {
        return [
            'can_view' => 'boolean',
            'can_create' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
        ];
    }

    /**
     * Daftar menu yang aksesnya bisa diatur per grup lewat Setting > Manajemen Grup & Akses.
     * Menu di dalam grup "Setting" sendiri tidak dimasukkan di sini karena memang selalu khusus Super Admin.
     *
     * @return array<string, string> menu_key => label
     */
    public static function menuList(): array
    {
        return [
            'dashboard' => 'Dashboard',
            'approval' => 'Approval Saya',
            'erf' => 'Request ERF',
            'ga' => 'Request GA',
            'tech' => 'Request Tech',
            'creative' => 'Request Creative Design',
            'business_trip' => 'Business Trip',
            'master_karyawan' => 'Master Data — Data Karyawan',
            'master_jabatan_ttf' => 'Master Data — Jabatan & TTF',
            'master_kalender' => 'Master Data — Kalender Kerja',
            'master_divisi' => 'Master Data — Divisi',
            'master_jabatan' => 'Master Data — Jabatan',
        ];
    }

    /**
     * Grup dasar berdasarkan peran. Super Admin (role=admin) sengaja tidak masuk daftar ini —
     * Super Admin selalu full akses ke semua menu.
     *
     * @return array<string, string> role => label
     */
    public static function groupLabels(): array
    {
        return [
            'karyawan' => 'Karyawan',
            'hr' => 'Tim HR',
            'ga' => 'Tim General Affair',
        ];
    }

    /**
     * Grup tambahan berdasarkan jabatan di Master Data > Jabatan (mis. "Manager", "Leader", "Staff HR").
     * Kunci diberi awalan "jabatan:" supaya tidak bentrok dengan kunci role di atas. Kalau ada baris hak akses
     * eksplisit untuk jabatan seorang user, aturannya menimpa aturan peran (lihat allowsForUser()).
     *
     * @return array<string, string> "jabatan:<id>" => "Nama Jabatan — Divisi"
     */
    public static function jabatanGroupOptions(): array
    {
        return MasterJabatan::query()->where('is_active', true)->with('divisi')->get()
            ->sortBy(fn (MasterJabatan $j) => mb_strtolower(($j->divisi->nama_divisi ?? 'zzz').' '.$j->nama_jabatan))
            ->mapWithKeys(fn (MasterJabatan $j) => [
                "jabatan:{$j->id}" => $j->nama_jabatan.($j->divisi ? ' — '.$j->divisi->nama_divisi : ''),
            ])
            ->all();
    }

    /**
     * Label yang enak dibaca untuk kunci grup apa pun (role biasa atau "jabatan:<id>") — dipakai di pesan flash.
     */
    public static function groupLabel(string $groupKey): string
    {
        if (isset(static::groupLabels()[$groupKey])) {
            return static::groupLabels()[$groupKey];
        }

        if (str_starts_with($groupKey, 'jabatan:')) {
            $jabatan = MasterJabatan::with('divisi')->find((int) substr($groupKey, 8));

            return $jabatan
                ? $jabatan->nama_jabatan.($jabatan->divisi ? ' — '.$jabatan->divisi->nama_divisi : '')
                : 'Jabatan (sudah dihapus)';
        }

        return $groupKey;
    }

    /**
     * null = belum diatur (pakai default bawaan aplikasi), true/false = sudah diatur eksplisit oleh Super Admin.
     */
    public static function allows(string $role, string $menuKey, string $action = 'view'): ?bool
    {
        $row = static::query()->where('role', $role)->where('menu_key', $menuKey)->first();

        if (! $row) {
            return null;
        }

        return match ($action) {
            'create' => $row->can_create,
            'update' => $row->can_update,
            'delete' => $row->can_delete,
            default => $row->can_view,
        };
    }

    /**
     * Hak akses efektif seorang user: kalau jabatannya (field "Jabatan" di profil, dicocokkan ke Master Data >
     * Jabatan) punya aturan eksplisit untuk menu ini, aturan itu yang dipakai. Kalau tidak, pakai aturan
     * berdasarkan peran (karyawan/hr/ga) seperti sebelumnya. null = belum diatur sama sekali, pakai default.
     */
    public static function allowsForUser(User $user, string $menuKey, string $action = 'view'): ?bool
    {
        if ($jabatanKey = $user->jabatanGroupKey()) {
            $viaJabatan = static::allows($jabatanKey, $menuKey, $action);
            if ($viaJabatan !== null) {
                return $viaJabatan;
            }
        }

        return static::allows($user->role, $menuKey, $action);
    }
}
