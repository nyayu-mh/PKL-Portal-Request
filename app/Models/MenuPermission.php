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
     * Grup yang bisa diatur aksesnya. Super Admin (role=admin) sengaja tidak masuk daftar ini —
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
}
