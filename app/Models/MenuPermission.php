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
     * Kunci grup Divisi-Level untuk sepasang (divisi, level) — dipakai baik untuk membangun daftar pilihan
     * maupun untuk mencocokkan seorang user ke grupnya. Divisi diambil apa adanya dari field "Divisi" di
     * Manajemen User (bukan dari Master Data > Divisi), supaya sama persis dengan yang admin lihat di sana.
     */
    public static function divisiLevelKey(string $divisi, string $level): string
    {
        return 'dl:'.base64_encode(trim($divisi)).':'.$level;
    }

    /**
     * Grup tambahan berdasarkan kombinasi Divisi & Level yang benar-benar ada di data Manajemen User
     * (mis. "HR — Staff", "HR — Leader", "HR — Manager"). Sistemnya memang berpusat pada pembagian
     * Divisi-Level ini, jadi lebih gampang diatur dibanding per jabatan satu-satu. Kunci diberi awalan "dl:"
     * supaya tidak bentrok dengan kunci role di atas. Kalau ada baris hak akses eksplisit untuk grup
     * Divisi-Level seorang user, aturannya menimpa aturan peran (lihat allowsForUser()).
     *
     * @return array<string, string> "dl:<divisi base64>:<level>" => "Divisi — Level"
     */
    public static function divisiLevelGroupOptions(): array
    {
        $levelLabels = User::jabatanLevelLabels();
        $levelOrder = array_flip(array_keys($levelLabels));

        $combos = User::query()
            ->where('role', '!=', 'admin')
            ->where('is_active', true)
            ->whereNotNull('divisi')->where('divisi', '!=', '')
            ->select('divisi', 'jabatan_level')
            ->distinct()
            ->get()
            ->map(fn ($row) => ['divisi' => trim($row->divisi), 'level' => $row->jabatan_level])
            ->unique(fn ($c) => mb_strtolower($c['divisi']).'|'.$c['level'])
            ->sort(fn ($a, $b) => [mb_strtolower($a['divisi']), $levelOrder[$a['level']] ?? 99]
                <=> [mb_strtolower($b['divisi']), $levelOrder[$b['level']] ?? 99]);

        $options = [];
        foreach ($combos as $c) {
            $options[static::divisiLevelKey($c['divisi'], $c['level'])] = $c['divisi'].' — '.($levelLabels[$c['level']] ?? $c['level']);
        }

        return $options;
    }

    /**
     * Label yang enak dibaca untuk kunci grup apa pun (role biasa atau "dl:<divisi>:<level>") — dipakai di
     * pesan flash.
     */
    public static function groupLabel(string $groupKey): string
    {
        if (isset(static::groupLabels()[$groupKey])) {
            return static::groupLabels()[$groupKey];
        }

        if (str_starts_with($groupKey, 'dl:')) {
            [, $divisiB64, $level] = array_pad(explode(':', $groupKey, 3), 3, '');
            $divisi = base64_decode($divisiB64) ?: '(divisi tidak diketahui)';

            return $divisi.' — '.(User::jabatanLevelLabels()[$level] ?? $level);
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
     * Hak akses efektif seorang user: kalau grup Divisi-Levelnya (dari field Divisi + Level di profil)
     * punya aturan eksplisit untuk menu ini, aturan itu yang dipakai. Kalau tidak, pakai aturan berdasarkan
     * peran (karyawan/hr/ga) seperti sebelumnya. null = belum diatur sama sekali, pakai default.
     */
    public static function allowsForUser(User $user, string $menuKey, string $action = 'view'): ?bool
    {
        if ($divisiLevelKey = $user->divisiLevelGroupKey()) {
            $viaDivisiLevel = static::allows($divisiLevelKey, $menuKey, $action);
            if ($viaDivisiLevel !== null) {
                return $viaDivisiLevel;
            }
        }

        return static::allows($user->role, $menuKey, $action);
    }
}
