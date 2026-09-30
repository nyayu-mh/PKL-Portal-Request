<?php

namespace Tests\Feature;

use App\Models\ErfRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Data ERF yang dimunculkan di akun HR mengikuti PIC-nya: ERF PIC Prima hanya muncul di akun Prima,
 * tidak di akun Lidya (dan sebaliknya). ERF tanpa PIC tetap terlihat semua HR (belum ada yang ditugaskan).
 */
class ErfPicHrVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private User $pemohon;

    private User $prima;

    private User $lidya;

    private User $hrbp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->pemohon = $this->makeUser('Pemohon', 'leader');
        $this->prima = $this->makeUser('Prima', 'staff', ['role' => 'hr']);
        $this->lidya = $this->makeUser('Lidya', 'staff', ['role' => 'hr']);
        $this->hrbp = $this->makeUser('Kemas HRBP', 'manager', ['role' => 'hr', 'lihat_semua_request' => true]);
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

    private function makeErf(?int $picHrId, string $approval = 'disetujui'): ErfRequest
    {
        return ErfRequest::create([
            'erf_id' => 'ERF-T-'.uniqid(), 'user_id' => $this->pemohon->id, 'tanggal_request' => now(),
            'jenis_erf' => 'karyawan_baru', 'jumlah_karyawan' => 1, 'uraian_tugas' => 'x', 'kualifikasi_kandidat' => 'x',
            'approval_status' => $approval, 'atasan_user_id' => null, 'status' => 'pending',
            'pic_hr_user_id' => $picHrId,
        ]);
    }

    public function test_hr_hanya_melihat_erf_dengan_pic_dirinya_sendiri(): void
    {
        $erfPrima = $this->makeErf($this->prima->id);
        $erfLidya = $this->makeErf($this->lidya->id);

        $visiblePrima = ErfRequest::visibleTo($this->prima)->pluck('id')->all();
        $visibleLidya = ErfRequest::visibleTo($this->lidya)->pluck('id')->all();

        $this->assertSame([$erfPrima->id], $visiblePrima);
        $this->assertSame([$erfLidya->id], $visibleLidya);

        $this->actingAs($this->lidya)->get(route('erf.show', $erfPrima))->assertForbidden();
        $this->actingAs($this->prima)->get(route('erf.show', $erfPrima))->assertOk();
    }

    public function test_erf_tanpa_pic_tetap_terlihat_semua_hr(): void
    {
        $erf = $this->makeErf(null);

        $this->assertContains($erf->id, ErfRequest::visibleTo($this->prima)->pluck('id')->all());
        $this->assertContains($erf->id, ErfRequest::visibleTo($this->lidya)->pluck('id')->all());
    }

    public function test_erf_yang_belum_disetujui_tetap_tidak_terlihat_pic_hr_nya_sekalipun(): void
    {
        $erf = $this->makeErf($this->prima->id, 'menunggu');

        $this->assertSame([], ErfRequest::visibleTo($this->prima)->pluck('id')->all());
    }

    public function test_manager_hrbp_lihat_semua_request_tidak_dibatasi_pic(): void
    {
        $erfPrima = $this->makeErf($this->prima->id);
        $erfLidya = $this->makeErf($this->lidya->id);

        $visible = ErfRequest::visibleTo($this->hrbp)->pluck('id')->all();

        $this->assertContains($erfPrima->id, $visible);
        $this->assertContains($erfLidya->id, $visible);
    }
}
