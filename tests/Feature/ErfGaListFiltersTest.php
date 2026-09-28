<?php

namespace Tests\Feature;

use App\Models\ErfRequest;
use App\Models\GaRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Filter tanggal (semua akun) & filter brand (Manager, Tim HR/GA, Super Admin) di daftar ERF/GA.
 */
class ErfGaListFiltersTest extends TestCase
{
    use RefreshDatabase;

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

    private function makeErf(User $owner, string $tanggal): ErfRequest
    {
        return ErfRequest::create([
            'erf_id' => 'ERF-T-'.uniqid(), 'user_id' => $owner->id, 'tanggal_request' => $tanggal,
            'jenis_erf' => 'karyawan_baru', 'jumlah_karyawan' => 1, 'uraian_tugas' => 'x', 'kualifikasi_kandidat' => 'x',
            'approval_status' => 'tidak_perlu', 'atasan_user_id' => null, 'status' => 'pending',
        ]);
    }

    private function makeGa(User $owner, string $tanggal): GaRequest
    {
        return GaRequest::create([
            'ga_id' => 'GA-T-'.uniqid(), 'user_id' => $owner->id, 'tanggal_request' => $tanggal,
            'jenis_request' => 'maintenance', 'judul' => 'x', 'deskripsi' => 'x', 'lokasi_kerja' => 'head_office',
            'approval_status' => 'tidak_perlu', 'atasan_user_id' => null, 'status' => 'pending',
        ]);
    }

    public function test_filter_tanggal_erf_menyaring_hasil_untuk_semua_akun(): void
    {
        $manager = $this->makeUser('Manager', 'manager', ['lihat_semua_request' => true]);
        $lama = $this->makeErf($manager, '2026-01-05');
        $baru = $this->makeErf($manager, '2026-03-20');

        $component = Livewire::actingAs($manager)->test(\App\Livewire\Erf\ErfIndex::class)
            ->assertSee('Tanggal Request')
            ->set('tanggal_dari', '2026-03-01')
            ->assertSee($baru->erf_id)
            ->assertDontSee($lama->erf_id);

        $component->set('tanggal_dari', '')->set('tanggal_sampai', '2026-01-31')
            ->assertSee($lama->erf_id)->assertDontSee($baru->erf_id);
    }

    public function test_filter_tanggal_ga_menyaring_hasil(): void
    {
        $ga = $this->makeUser('Tim GA', 'staff', ['role' => 'ga']);
        $lama = $this->makeGa($ga, '2026-01-05');
        $baru = $this->makeGa($ga, '2026-03-20');

        Livewire::actingAs($ga)->test(\App\Livewire\Ga\GaIndex::class)
            ->assertSee('Tanggal Request')
            ->set('tanggal_dari', '2026-03-01')
            ->assertSee($baru->ga_id)
            ->assertDontSee($lama->ga_id);
    }

    public function test_filter_brand_muncul_dan_berfungsi_untuk_manager_hr_admin_di_erf(): void
    {
        $manager = $this->makeUser('Manager', 'manager', ['lihat_semua_request' => true]);
        $hr = $this->makeUser('HR', 'staff', ['role' => 'hr']);
        $karyawanBiasa = $this->makeUser('Karyawan Biasa', 'leader');

        $pemohonA = $this->makeUser('Pemohon A', 'leader', ['brand' => 'wookey_wight']);
        $pemohonB = $this->makeUser('Pemohon B', 'leader', ['brand' => 'so_honey_jr']);
        $erfA = $this->makeErf($pemohonA, '2026-02-01');
        $erfB = $this->makeErf($pemohonB, '2026-02-01');

        Livewire::actingAs($manager)->test(\App\Livewire\Erf\ErfIndex::class)
            ->assertSee('Semua Brand')
            ->set('brand', 'wookey_wight')
            ->assertSee($erfA->erf_id)->assertDontSee($erfB->erf_id);

        Livewire::actingAs($hr)->test(\App\Livewire\Erf\ErfIndex::class)
            ->assertSee('Semua Brand');

        // Karyawan biasa (bukan manager/hr/admin) tidak diberi filter brand.
        Livewire::actingAs($karyawanBiasa)->test(\App\Livewire\Erf\ErfIndex::class)
            ->assertDontSee('Semua Brand');
    }

    public function test_filter_brand_muncul_dan_berfungsi_untuk_manager_ga_admin_di_ga(): void
    {
        $manager = $this->makeUser('Manager', 'manager', ['lihat_semua_request' => true]);
        $ga = $this->makeUser('Tim GA', 'staff', ['role' => 'ga']);
        $karyawanBiasa = $this->makeUser('Karyawan Biasa', 'leader');

        $pemohonA = $this->makeUser('Pemohon A', 'leader', ['brand' => 'wookey_wight']);
        $pemohonB = $this->makeUser('Pemohon B', 'leader', ['brand' => 'so_honey_jr']);
        $gaA = $this->makeGa($pemohonA, '2026-02-01');
        $gaB = $this->makeGa($pemohonB, '2026-02-01');

        Livewire::actingAs($ga)->test(\App\Livewire\Ga\GaIndex::class)
            ->set('brand', 'so_honey_jr')
            ->assertSee($gaB->ga_id)->assertDontSee($gaA->ga_id);

        Livewire::actingAs($karyawanBiasa)->test(\App\Livewire\Ga\GaIndex::class)
            ->assertDontSee('Semua Brand');
    }
}
