<?php

namespace Tests\Feature;

use App\Models\ErfRequest;
use App\Models\GaRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Brand pemohon (Wookey Wight / So Honey Jr / Semua Brand) tampil di semua halaman:
 * dashboard, daftar & detail ERF/GA, Approval Saya, sidebar, dan Manajemen User (admin).
 */
class BrandDisplayTest extends TestCase
{
    use RefreshDatabase;

    private User $atasan;

    private User $pemohon;

    private User $hr;

    private User $ga;

    private User $admin;

    private ErfRequest $erf;

    private GaRequest $gaRequest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->atasan = $this->makeUser('Atasan', 'manager');
        $this->pemohon = $this->makeUser('Pemohon', 'leader', ['atasan_id' => $this->atasan->id, 'brand' => 'so_honey_jr']);
        $this->hr = $this->makeUser('HR', 'staff', ['role' => 'hr']);
        $this->ga = $this->makeUser('GA', 'staff', ['role' => 'ga']);
        $this->admin = $this->makeUser('Admin', 'manager', ['role' => 'admin', 'brand' => 'semua']);

        $this->erf = ErfRequest::create([
            'erf_id' => 'ERF-T-1', 'user_id' => $this->pemohon->id, 'tanggal_request' => now(),
            'jenis_erf' => 'karyawan_baru', 'jumlah_karyawan' => 1, 'uraian_tugas' => 'x', 'kualifikasi_kandidat' => 'x',
            'approval_status' => 'menunggu', 'atasan_user_id' => $this->atasan->id, 'status' => 'pending',
        ]);
        $this->gaRequest = GaRequest::create([
            'ga_id' => 'GA-T-1', 'user_id' => $this->pemohon->id, 'tanggal_request' => now(),
            'jenis_request' => 'maintenance', 'judul' => 'x', 'deskripsi' => 'x', 'lokasi_kerja' => 'head_office',
            'approval_status' => 'menunggu', 'atasan_user_id' => $this->atasan->id, 'status' => 'pending',
        ]);
    }

    private function makeUser(string $name, string $level, array $extra = []): User
    {
        return User::create(array_merge([
            'name' => $name,
            'email' => strtolower(str_replace(' ', '.', $name)).'@example.test',
            'password' => 'password',
            'jabatan_level' => $level,
            'role' => 'karyawan',
            'is_active' => true,
        ], $extra));
    }

    public function test_brand_tampil_di_dashboard_daftar_dan_detail_untuk_pemohon(): void
    {
        $this->actingAs($this->pemohon);

        foreach (['dashboard', 'erf.index', 'ga.index'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('So Honey Jr');
        }
        $this->get(route('erf.show', $this->erf))->assertOk()->assertSee('So Honey Jr');
        $this->get(route('ga.show', $this->gaRequest))->assertOk()->assertSee('So Honey Jr');
    }

    public function test_brand_pemohon_tampil_di_halaman_approval_atasan(): void
    {
        $this->actingAs($this->atasan)->get(route('approval.index'))
            ->assertOk()->assertSee('ERF-T-1')->assertSee('GA-T-1')->assertSee('So Honey Jr');
    }

    public function test_brand_pemohon_tampil_untuk_hr_dan_ga_di_daftar_dan_detail(): void
    {
        $this->erf->update(['approval_status' => 'disetujui']);
        $this->gaRequest->update(['approval_status' => 'disetujui']);

        $this->actingAs($this->hr);
        $this->get(route('erf.index'))->assertOk()->assertSee('So Honey Jr');
        $this->get(route('erf.show', $this->erf))->assertOk()->assertSee('So Honey Jr');

        $this->actingAs($this->ga);
        $this->get(route('ga.index'))->assertOk()->assertSee('So Honey Jr');
        $this->get(route('ga.show', $this->gaRequest))->assertOk()->assertSee('So Honey Jr');
    }

    public function test_brand_user_login_tampil_di_sidebar_dan_admin_melihat_brand_di_manajemen_user(): void
    {
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk()->assertSee('Semua Brand');
        $this->actingAs($this->admin)->get(route('setting.users.index'))
            ->assertOk()->assertSee('So Honey Jr')->assertSee('Semua Brand');
    }

    public function test_pemohon_tanpa_brand_menampilkan_belum_diatur_di_detail_dan_tanpa_lencana_di_daftar(): void
    {
        $this->pemohon->update(['brand' => null]);

        $this->actingAs($this->pemohon)->get(route('erf.show', $this->erf))->assertOk()->assertSee('Belum diatur');
        $this->actingAs($this->pemohon)->get(route('erf.index'))->assertOk()
            ->assertDontSee('So Honey Jr')->assertDontSee('Wookey Wight')->assertDontSee('Belum diatur');
    }
}
