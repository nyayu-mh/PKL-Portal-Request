<?php

namespace Tests\Feature;

use App\Livewire\Master\JabatanTtfManager;
use App\Livewire\Setting\GroupAccessManager;
use App\Models\MasterDivisi;
use App\Models\MasterJabatan;
use App\Models\MasterJabatanTtf;
use App\Models\MenuPermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Search di menu Jabatan & TTF, dan Manajemen Grup & Akses berbasis jabatan (Master Data > Jabatan).
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

    public function test_grup_akses_menawarkan_pilihan_dari_master_jabatan(): void
    {
        $divisi = MasterDivisi::create(['nama_divisi' => 'IT']);
        MasterJabatan::create(['nama_jabatan' => 'Manager IT', 'master_divisi_id' => $divisi->id, 'is_active' => true]);
        MasterJabatan::create(['nama_jabatan' => 'Staff Nonaktif', 'is_active' => false]);

        Livewire::test(GroupAccessManager::class)
            ->assertSee('Berdasarkan Jabatan')
            ->assertSee('Manager IT')
            ->assertDontSee('Staff Nonaktif');
    }

    public function test_hak_akses_jabatan_menimpa_hak_akses_peran_untuk_user_dengan_jabatan_itu(): void
    {
        $divisi = MasterDivisi::create(['nama_divisi' => 'HRD']);
        $jabatan = MasterJabatan::create(['nama_jabatan' => 'Staff HR', 'master_divisi_id' => $divisi->id, 'is_active' => true]);

        // Peran "hr" secara default melihat menu ga (fallback true kalau belum diatur), tapi kita matikan eksplisit.
        MenuPermission::create(['role' => 'hr', 'menu_key' => 'master_karyawan', 'can_view' => true]);
        MenuPermission::create(['role' => "jabatan:{$jabatan->id}", 'menu_key' => 'master_karyawan', 'can_view' => false]);

        $staffHr = User::create([
            'name' => 'Lidya', 'email' => 'lidya@example.test', 'password' => 'password',
            'jabatan_level' => 'staff', 'role' => 'hr', 'jabatan' => 'Staff HR', 'is_active' => true,
        ]);

        // aturan jabatan (false) menimpa aturan peran (true)
        $this->assertFalse(MenuPermission::allowsForUser($staffHr, 'master_karyawan'));

        $hrLain = User::create([
            'name' => 'Prima', 'email' => 'prima@example.test', 'password' => 'password',
            'jabatan_level' => 'staff', 'role' => 'hr', 'jabatan' => 'HR Recruitment', 'is_active' => true,
        ]);

        // HR lain yang jabatannya tidak cocok dengan grup jabatan tetap pakai aturan peran (true)
        $this->assertTrue(MenuPermission::allowsForUser($hrLain, 'master_karyawan'));
    }

    public function test_simpan_hak_akses_grup_jabatan_dari_ui_dan_pesan_flash_menyebut_nama_jabatan(): void
    {
        $jabatan = MasterJabatan::create(['nama_jabatan' => 'Leader CRM', 'is_active' => true]);

        Livewire::test(GroupAccessManager::class)
            ->set('selectedRole', "jabatan:{$jabatan->id}")
            ->set('perms.erf.view', false)
            ->call('save')
            ->assertSee('Leader CRM')
            ->assertSee('berhasil disimpan');

        $this->assertFalse(MenuPermission::where('role', "jabatan:{$jabatan->id}")->where('menu_key', 'erf')->value('can_view'));
    }
}
