<?php

namespace Tests\Feature;

use App\Livewire\Master\JabatanManager;
use App\Livewire\Master\JabatanTtfManager;
use App\Livewire\Master\KaryawanManager;
use App\Livewire\Master\UserManager;
use App\Models\MasterDivisi;
use App\Models\MasterJabatan;
use App\Models\MasterJabatanTtf;
use App\Models\MasterKaryawan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Field Divisi di menu master data: dropdown dari data yang ada + opsi "Ketik manual".
 */
class DivisiDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $admin = User::create([
            'name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password',
            'jabatan_level' => 'manager', 'role' => 'admin', 'is_active' => true,
        ]);
        $this->actingAs($admin);

        MasterDivisi::create(['nama_divisi' => 'CRM & CX']);
        MasterDivisi::create(['nama_divisi' => 'Finance']);
        MasterDivisi::create(['nama_divisi' => 'Divisi Nonaktif', 'is_active' => false]);
        MasterKaryawan::create(['nama' => 'A', 'divisi' => 'crm & cx']);   // sama dengan CRM & CX, beda huruf
        MasterKaryawan::create(['nama' => 'B', 'divisi' => 'Host Live']);   // hanya ada di data karyawan
        MasterJabatanTtf::create(['nama_jabatan' => 'X', 'divisi' => 'Technology', 'target_hari_kerja' => 30]);
    }

    public function test_daftar_pilihan_gabungan_semua_sumber_tanpa_duplikat_dan_tanpa_divisi_nonaktif(): void
    {
        $daftar = MasterDivisi::daftarPilihan();

        $this->assertSame(['CRM & CX', 'Finance', 'Host Live', 'Technology'], array_values($daftar));
        $this->assertNotContains('Divisi Nonaktif', $daftar);
        $this->assertSame(array_keys($daftar), array_values($daftar)); // nilai = label
    }

    public function test_form_karyawan_menampilkan_dropdown_dan_menyimpan_pilihan_maupun_ketik_manual(): void
    {
        Livewire::test(KaryawanManager::class)
            ->call('create')
            ->assertSee('Host Live')
            ->assertSee('Ketik manual')
            ->set('nama', 'Pilih Dropdown')->set('divisi', 'Finance')->call('save')
            ->call('create')
            ->set('nama', 'Ketik Sendiri')->set('divisi', 'Divisi Ketikan Baru')->call('save');

        $this->assertSame('Finance', MasterKaryawan::where('nama', 'Pilih Dropdown')->value('divisi'));
        $this->assertSame('Divisi Ketikan Baru', MasterKaryawan::where('nama', 'Ketik Sendiri')->value('divisi'));

        // divisi yang diketik manual otomatis muncul di dropdown berikutnya
        $this->assertContains('Divisi Ketikan Baru', MasterDivisi::daftarPilihan());
    }

    public function test_edit_memilih_otomatis_divisi_record_di_dropdown(): void
    {
        $k = MasterKaryawan::create(['nama' => 'Unik', 'divisi' => 'Divisi Langka']);

        // divisi yang sudah dipakai record mana pun otomatis masuk daftar, jadi tampil terpilih (bukan mode ketik manual)
        Livewire::test(KaryawanManager::class)
            ->call('edit', $k->id)
            ->assertSeeHtml('manual: false')
            ->assertSeeHtml('<option value="Divisi Langka" selected>');
    }

    public function test_form_jabatan_membuka_mode_ketik_manual_kalau_divisi_baru_terisi(): void
    {
        Livewire::test(JabatanManager::class)
            ->call('create')
            ->assertSeeHtml('manual: false')
            ->set('divisi_baru', 'Riset Pasar')
            ->assertSeeHtml('manual: true');
    }

    public function test_form_jabatan_ttf_dan_user_memakai_dropdown_dan_menerima_ketik_manual(): void
    {
        Livewire::test(JabatanTtfManager::class)
            ->call('create')->assertSee('Ketik manual')->assertSee('Finance')
            ->set('nama_jabatan', 'Jabatan Baru')->set('divisi', 'Divisi Manual TTF')->set('target_hari_kerja', 20)
            ->call('save');
        $this->assertSame('Divisi Manual TTF', MasterJabatanTtf::where('nama_jabatan', 'Jabatan Baru')->value('divisi'));

        Livewire::test(UserManager::class)
            ->call('create')->assertSee('Ketik manual')->assertSee('Host Live')
            ->set('name', 'User Baru')->set('email', 'user.baru@example.test')->set('password', 'password123')
            ->set('divisi', 'Finance')
            ->call('save');
        $this->assertSame('Finance', User::where('email', 'user.baru@example.test')->value('divisi'));
    }

    public function test_form_jabatan_memilih_divisi_dari_dropdown_atau_mengetik_divisi_baru(): void
    {
        $finance = MasterDivisi::where('nama_divisi', 'Finance')->first();

        Livewire::test(JabatanManager::class)
            ->call('create')->assertSee('Ketik manual')
            ->set('nama_jabatan', 'Akuntan')->set('master_divisi_id', $finance->id)->call('save')
            ->call('create')
            ->set('nama_jabatan', 'Peneliti')->set('divisi_baru', 'Riset Pasar')->call('save')
            ->call('create')
            ->set('nama_jabatan', 'Analis')->set('divisi_baru', 'finance')->call('save'); // beda huruf = divisi yang sama

        $this->assertSame($finance->id, MasterJabatan::where('nama_jabatan', 'Akuntan')->value('master_divisi_id'));

        $baru = MasterDivisi::where('nama_divisi', 'Riset Pasar')->first();
        $this->assertNotNull($baru);
        $this->assertSame($baru->id, MasterJabatan::where('nama_jabatan', 'Peneliti')->value('master_divisi_id'));

        $this->assertSame($finance->id, MasterJabatan::where('nama_jabatan', 'Analis')->value('master_divisi_id'));
        $this->assertSame(1, MasterDivisi::whereRaw('LOWER(nama_divisi) = ?', ['finance'])->count());
    }
}
