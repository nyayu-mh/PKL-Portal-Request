<?php

namespace Tests\Feature;

use App\Livewire\Master\JabatanTtfManager;
use App\Livewire\Setting\GroupAccessManager;
use App\Models\MasterJabatanTtf;
use App\Models\MenuPermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Search di menu Jabatan & TTF, dan Manajemen Grup & Akses berbasis Divisi-Level (dari Manajemen User).
 */
class MasterDataExtraTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password',
            'jabatan_level' => 'manager', 'role' => 'admin', 'is_active' => true,
        ]);
        $this->actingAs($this->admin);
    }

    public function test_search_jabatan_ttf_menyaring_berdasarkan_nama_jabatan_atau_divisi(): void
    {
        MasterJabatanTtf::create(['nama_jabatan' => 'Staff IT Support', 'divisi' => 'IT', 'target_hari_kerja' => 14]);
        MasterJabatanTtf::create(['nama_jabatan' => 'Staff Finance', 'divisi' => 'Finance', 'target_hari_kerja' => 14]);

        Livewire::test(JabatanTtfManager::class)
            ->assertSee('Staff IT Support')->assertSee('Staff Finance')
            ->set('search', 'IT')
            ->assertSee('Staff IT Support')->assertDontSee('Staff Finance')
            ->set('search', 'Finance')
            ->assertSee('Staff Finance')->assertDontSee('Staff IT Support');
    }

    public function test_grup_akses_menawarkan_pilihan_dari_kombinasi_divisi_level_di_manajemen_user(): void
    {
        User::create(['name' => 'A', 'email' => 'a@example.test', 'password' => 'password', 'divisi' => 'HR', 'jabatan_level' => 'staff', 'is_active' => true]);
        User::create(['name' => 'B', 'email' => 'b@example.test', 'password' => 'password', 'divisi' => 'HR', 'jabatan_level' => 'manager', 'is_active' => true]);
        // Super Admin dan user nonaktif tidak ikut membentuk pilihan grup.
        User::create(['name' => 'C', 'email' => 'c@example.test', 'password' => 'password', 'divisi' => 'HR', 'jabatan_level' => 'leader', 'role' => 'admin', 'is_active' => true]);
        User::create(['name' => 'D', 'email' => 'd@example.test', 'password' => 'password', 'divisi' => 'Finance', 'jabatan_level' => 'staff', 'is_active' => false]);

        Livewire::test(GroupAccessManager::class)
            ->assertSee('Berdasarkan Divisi')
            ->assertSee('HR — Staff')
            ->assertSee('HR — Manager')
            ->assertDontSee(MenuPermission::divisiLevelKey('HR', 'leader'), false)
            ->assertDontSee(MenuPermission::divisiLevelKey('Finance', 'staff'), false);
    }

    public function test_hak_akses_divisi_level_menimpa_hak_akses_peran_untuk_user_dengan_divisi_level_itu(): void
    {
        $key = MenuPermission::divisiLevelKey('HR', 'staff');

        // Peran "hr" secara default melihat menu master_karyawan, tapi kita matikan eksplisit untuk grup HR-Staff.
        MenuPermission::create(['role' => 'hr', 'menu_key' => 'master_karyawan', 'can_view' => true]);
        MenuPermission::create(['role' => $key, 'menu_key' => 'master_karyawan', 'can_view' => false]);

        $staffHr = User::create([
            'name' => 'Lidya', 'email' => 'lidya@example.test', 'password' => 'password',
            'jabatan_level' => 'staff', 'role' => 'hr', 'divisi' => 'HR', 'is_active' => true,
        ]);

        // aturan Divisi-Level (false) menimpa aturan peran (true)
        $this->assertFalse(MenuPermission::allowsForUser($staffHr, 'master_karyawan'));

        $managerHr = User::create([
            'name' => 'Kemas', 'email' => 'kemas@example.test', 'password' => 'password',
            'jabatan_level' => 'manager', 'role' => 'hr', 'divisi' => 'HR', 'is_active' => true,
        ]);

        // HR lain dengan Divisi-Level berbeda (HR-Manager) tetap pakai aturan peran (true)
        $this->assertTrue(MenuPermission::allowsForUser($managerHr, 'master_karyawan'));
    }

    public function test_simpan_hak_akses_grup_divisi_level_dari_ui_dan_pesan_flash_menyebut_divisi_level(): void
    {
        User::create(['name' => 'Leader CRM', 'email' => 'leader.crm@example.test', 'password' => 'password', 'divisi' => 'CRM', 'jabatan_level' => 'leader', 'is_active' => true]);
        $key = MenuPermission::divisiLevelKey('CRM', 'leader');

        Livewire::test(GroupAccessManager::class)
            ->set('selectedRole', $key)
            ->set('perms.erf.view', false)
            ->call('save')
            ->assertSee('CRM — Leader')
            ->assertSee('berhasil disimpan');

        $this->assertFalse(MenuPermission::where('role', $key)->where('menu_key', 'erf')->value('can_view'));
    }
}
