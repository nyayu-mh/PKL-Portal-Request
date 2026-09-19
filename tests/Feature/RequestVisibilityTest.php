<?php

namespace Tests\Feature;

use App\Models\ErfRequest;
use App\Models\GaRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aturan akses ERF/GA (data sensitif):
 *  - user biasa: cuma yang ia buat sendiri + yang atasan langsungnya dia
 *  - Tim HR: ERF yang approval atasannya beres; Tim GA: request GA yang approval atasannya beres
 *  - Super Admin: semuanya
 */
class RequestVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private User $atasan;

    private User $pemohon;

    private User $orangLain;

    private User $hr;

    private User $ga;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->atasan = $this->makeUser('Atasan', 'manager');
        $this->pemohon = $this->makeUser('Pemohon', 'leader', ['atasan_id' => $this->atasan->id]);
        $this->orangLain = $this->makeUser('Orang Lain', 'leader');
        $this->hr = $this->makeUser('HR', 'staff', ['role' => 'hr']);
        $this->ga = $this->makeUser('GA', 'staff', ['role' => 'ga']);
        $this->admin = $this->makeUser('Admin', 'manager', ['role' => 'admin']);
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

    private function makeErf(string $approval, ?User $owner = null): ErfRequest
    {
        $owner ??= $this->pemohon;

        return ErfRequest::create([
            'erf_id' => 'ERF-T-'.uniqid(),
            'user_id' => $owner->id,
            'tanggal_request' => now(),
            'jenis_erf' => 'karyawan_baru',
            'jumlah_karyawan' => 1,
            'uraian_tugas' => 'x',
            'kualifikasi_kandidat' => 'x',
            'approval_status' => $approval,
            'atasan_user_id' => $owner->atasan_id,
            'status' => 'pending',
        ]);
    }

    private function makeGa(string $approval, ?User $owner = null): GaRequest
    {
        $owner ??= $this->pemohon;

        return GaRequest::create([
            'ga_id' => 'GA-T-'.uniqid(),
            'user_id' => $owner->id,
            'tanggal_request' => now(),
            'jenis_request' => 'maintenance',
            'judul' => 'x',
            'deskripsi' => 'x',
            'lokasi_kerja' => 'head_office',
            'approval_status' => $approval,
            'atasan_user_id' => $owner->atasan_id,
            'status' => 'pending',
        ]);
    }

    private function visibleErfIds(User $user): array
    {
        return ErfRequest::query()->visibleTo($user)->pluck('id')->all();
    }

    private function visibleGaIds(User $user): array
    {
        return GaRequest::query()->visibleTo($user)->pluck('id')->all();
    }

    public function test_pemohon_hanya_melihat_erf_miliknya_sendiri(): void
    {
        $milikPemohon = $this->makeErf('menunggu');
        $milikOrangLain = $this->makeErf('menunggu', $this->orangLain);

        $this->assertSame([$milikPemohon->id], $this->visibleErfIds($this->pemohon));
        $this->assertSame([$milikOrangLain->id], $this->visibleErfIds($this->orangLain));
    }

    public function test_atasan_langsung_bisa_melihat_erf_bawahannya_tapi_bukan_orang_lain(): void
    {
        $bawahan = $this->makeErf('menunggu');
        $this->makeErf('menunggu', $this->orangLain);

        $this->assertSame([$bawahan->id], $this->visibleErfIds($this->atasan));
    }

    public function test_tim_hr_hanya_melihat_erf_yang_sudah_disetujui_atau_tidak_perlu_approval(): void
    {
        $menunggu = $this->makeErf('menunggu');
        $revisi = $this->makeErf('revisi');
        $ditolak = $this->makeErf('ditolak');
        $disetujui = $this->makeErf('disetujui');
        $tidakPerlu = $this->makeErf('tidak_perlu', $this->orangLain);

        $ids = $this->visibleErfIds($this->hr);

        $this->assertEqualsCanonicalizing([$disetujui->id, $tidakPerlu->id], $ids);
        $this->assertNotContains($menunggu->id, $ids);
        $this->assertNotContains($revisi->id, $ids);
        $this->assertNotContains($ditolak->id, $ids);
    }

    public function test_tim_hr_tetap_melihat_erf_miliknya_sendiri_walau_belum_disetujui(): void
    {
        $milikHr = $this->makeErf('menunggu', $this->hr);

        $this->assertContains($milikHr->id, $this->visibleErfIds($this->hr));
    }

    public function test_tim_ga_dan_karyawan_biasa_tidak_melihat_erf_orang_lain(): void
    {
        $erf = $this->makeErf('disetujui');

        $this->assertNotContains($erf->id, $this->visibleErfIds($this->ga));
        $this->assertNotContains($erf->id, $this->visibleErfIds($this->orangLain));
    }

    public function test_tim_ga_hanya_melihat_request_ga_yang_sudah_disetujui(): void
    {
        $menunggu = $this->makeGa('menunggu');
        $disetujui = $this->makeGa('disetujui');

        $ids = $this->visibleGaIds($this->ga);

        $this->assertSame([$disetujui->id], $ids);
        $this->assertNotContains($menunggu->id, $ids);
    }

    public function test_tim_hr_tidak_melihat_request_ga_orang_lain(): void
    {
        $ga = $this->makeGa('disetujui');

        $this->assertNotContains($ga->id, $this->visibleGaIds($this->hr));
    }

    public function test_super_admin_bisa_melihat_semuanya(): void
    {
        $this->makeErf('menunggu');
        $this->makeErf('ditolak', $this->orangLain);
        $this->makeGa('menunggu');

        $this->assertCount(2, $this->visibleErfIds($this->admin));
        $this->assertCount(1, $this->visibleGaIds($this->admin));
    }

    public function test_akses_langsung_lewat_url_ke_erf_orang_lain_ditolak(): void
    {
        $erf = $this->makeErf('menunggu');

        $this->actingAs($this->orangLain)->get(route('erf.show', $erf))->assertForbidden();
        $this->actingAs($this->ga)->get(route('erf.show', $erf))->assertForbidden();
        // Tim HR pun belum boleh membuka ERF yang approval atasannya belum beres.
        $this->actingAs($this->hr)->get(route('erf.show', $erf))->assertForbidden();
    }

    public function test_akses_langsung_lewat_url_yang_diizinkan_berhasil(): void
    {
        $erf = $this->makeErf('disetujui');

        $this->actingAs($this->pemohon)->get(route('erf.show', $erf))->assertOk();
        $this->actingAs($this->atasan)->get(route('erf.show', $erf))->assertOk();
        $this->actingAs($this->hr)->get(route('erf.show', $erf))->assertOk();
        $this->actingAs($this->admin)->get(route('erf.show', $erf))->assertOk();
    }

    public function test_akses_langsung_lewat_url_ke_request_ga_orang_lain_ditolak(): void
    {
        $ga = $this->makeGa('disetujui');

        $this->actingAs($this->orangLain)->get(route('ga.show', $ga))->assertForbidden();
        $this->actingAs($this->hr)->get(route('ga.show', $ga))->assertForbidden();
        $this->actingAs($this->ga)->get(route('ga.show', $ga))->assertOk();
    }

    public function test_tim_hr_tidak_bisa_masuk_modul_ga_dan_tim_ga_tidak_bisa_masuk_modul_erf(): void
    {
        // Request milik Tim HR/GA sendiri pun tidak dibuka lintas modul.
        $gaMilikHr = $this->makeGa('menunggu', $this->hr);
        $erfMilikGa = $this->makeErf('menunggu', $this->ga);

        $this->assertSame([], $this->visibleGaIds($this->hr));
        $this->assertSame([], $this->visibleErfIds($this->ga));

        $this->actingAs($this->hr)->get(route('ga.index'))->assertForbidden();
        $this->actingAs($this->hr)->get(route('ga.show', $gaMilikHr))->assertForbidden();
        $this->actingAs($this->ga)->get(route('erf.index'))->assertForbidden();
        $this->actingAs($this->ga)->get(route('erf.show', $erfMilikGa))->assertForbidden();

        // Modul milik timnya sendiri tetap bisa dibuka.
        $this->actingAs($this->hr)->get(route('erf.index'))->assertOk();
        $this->actingAs($this->ga)->get(route('ga.index'))->assertOk();
    }

    public function test_menu_dan_dashboard_tim_hr_tanpa_ga_dan_tim_ga_tanpa_erf(): void
    {
        $this->actingAs($this->hr)->get(route('dashboard'))
            ->assertOk()->assertSee('Request ERF')->assertDontSee('Request General Affair')->assertDontSee(route('ga.index'));

        $this->actingAs($this->ga)->get(route('dashboard'))
            ->assertOk()->assertSee('Request General Affair')->assertDontSee('Request ERF')->assertDontSee(route('erf.index'));

        $this->actingAs($this->admin)->get(route('dashboard'))
            ->assertOk()->assertSee(route('erf.index'))->assertSee(route('ga.index'));
    }

    public function test_user_dengan_centang_lihat_semua_request_melihat_erf_dan_ga_semuanya(): void
    {
        // Mis. Manager HRBP: role hr, tapi berhak memantau semua request.
        $hrbp = $this->makeUser('Manager HRBP', 'manager', ['role' => 'hr', 'lihat_semua_request' => true]);
        $erf = $this->makeErf('menunggu');
        $ga = $this->makeGa('menunggu');

        $this->assertContains($erf->id, $this->visibleErfIds($hrbp));
        $this->assertContains($ga->id, $this->visibleGaIds($hrbp));

        $this->actingAs($hrbp)->get(route('erf.show', $erf))->assertOk();
        $this->actingAs($hrbp)->get(route('ga.show', $ga))->assertOk();
        $this->actingAs($hrbp)->get(route('ga.index'))->assertOk();
        $this->actingAs($hrbp)->get(route('dashboard'))->assertOk()->assertSee(route('ga.index'));

        // Tim HR biasa (tanpa centang) tetap tidak bisa masuk modul GA.
        $this->actingAs($this->hr)->get(route('ga.show', $ga))->assertForbidden();
    }
}
