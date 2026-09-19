<?php

namespace Tests\Feature;

use App\Models\GaRequest;
use App\Models\GaVendorQuotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Lampiran GA bersifat privat: hanya yang boleh melihat request GA-nya yang bisa membuka file.
 */
class GaAttachmentAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $pemohon;

    private User $atasan;

    private User $orangLain;

    private User $hr;

    private User $ga;

    private User $admin;

    private GaRequest $request;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->atasan = $this->makeUser('Atasan', 'manager');
        $this->pemohon = $this->makeUser('Pemohon', 'leader', ['atasan_id' => $this->atasan->id]);
        $this->orangLain = $this->makeUser('Orang Lain', 'leader');
        $this->hr = $this->makeUser('HR', 'staff', ['role' => 'hr']);
        $this->ga = $this->makeUser('GA', 'staff', ['role' => 'ga']);
        $this->admin = $this->makeUser('Admin', 'manager', ['role' => 'admin']);

        Storage::disk('local')->put('ga/bukti-kondisi/bukti.png', 'isi-bukti');
        Storage::disk('local')->put('ga/quotations/quo.pdf', 'isi-quo');

        $this->request = $this->makeGa('disetujui', ['lampiran_bukti_kondisi' => 'ga/bukti-kondisi/bukti.png']);
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

    private function makeGa(string $approval, array $extra = []): GaRequest
    {
        return GaRequest::create(array_merge([
            'ga_id' => 'GA-T-'.uniqid(),
            'user_id' => $this->pemohon->id,
            'tanggal_request' => now(),
            'jenis_request' => 'maintenance',
            'judul' => 'x',
            'deskripsi' => 'x',
            'lokasi_kerja' => 'head_office',
            'approval_status' => $approval,
            'atasan_user_id' => $this->atasan->id,
            'status' => 'pending',
        ], $extra));
    }

    private function makeQuotation(GaRequest $request): GaVendorQuotation
    {
        return GaVendorQuotation::create([
            'ga_request_id' => $request->id,
            'nama_vendor' => 'Vendor A',
            'file_path' => 'ga/quotations/quo.pdf',
        ]);
    }

    public function test_tamu_yang_belum_login_diarahkan_ke_login(): void
    {
        $this->get(route('ga.lampiran', [$this->request, 'bukti']))->assertRedirect(route('login'));
    }

    public function test_yang_berhak_melihat_request_bisa_membuka_lampiran(): void
    {
        foreach ([$this->pemohon, $this->atasan, $this->ga, $this->admin] as $user) {
            $response = $this->actingAs($user)->get(route('ga.lampiran', [$this->request, 'bukti']));
            $response->assertOk();
            $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition'));
            $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        }
    }

    public function test_yang_tidak_berhak_ditolak(): void
    {
        foreach ([$this->orangLain, $this->hr] as $user) {
            $this->actingAs($user)->get(route('ga.lampiran', [$this->request, 'bukti']))->assertForbidden();
        }
    }

    public function test_tim_ga_belum_boleh_membuka_lampiran_request_yang_belum_disetujui(): void
    {
        $belum = $this->makeGa('menunggu', ['lampiran_bukti_kondisi' => 'ga/bukti-kondisi/bukti.png']);

        $this->actingAs($this->ga)->get(route('ga.lampiran', [$belum, 'bukti']))->assertForbidden();
        $this->actingAs($this->atasan)->get(route('ga.lampiran', [$belum, 'bukti']))->assertOk();
    }

    public function test_jenis_lampiran_tidak_dikenal_atau_file_tidak_ada_menghasilkan_404(): void
    {
        $this->actingAs($this->pemohon)->get(route('ga.lampiran', [$this->request, 'lainnya']))->assertNotFound();
        // request ini tidak punya lampiran rekomendasi vendor
        $this->actingAs($this->pemohon)->get(route('ga.lampiran', [$this->request, 'rekomendasi']))->assertNotFound();

        $hilang = $this->makeGa('disetujui', ['lampiran_bukti_kondisi' => 'ga/bukti-kondisi/tidak-ada.png']);
        $this->actingAs($this->pemohon)->get(route('ga.lampiran', [$hilang, 'bukti']))->assertNotFound();
    }

    public function test_file_quotation_dicek_hak_akses_dan_kepemilikan(): void
    {
        $quo = $this->makeQuotation($this->request);

        $response = $this->actingAs($this->ga)->get(route('ga.quotation', [$this->request, $quo]));
        $response->assertOk();
        $this->assertStringContainsString('inline', $response->headers->get('Content-Disposition')); // pdf

        $this->actingAs($this->orangLain)->get(route('ga.quotation', [$this->request, $quo]))->assertForbidden();

        // quotation milik request lain tidak bisa "dipinjam" lewat request yang boleh dilihat
        $lain = $this->makeGa('disetujui');
        $this->actingAs($this->admin)->get(route('ga.quotation', [$lain, $quo]))->assertNotFound();
    }

    public function test_file_bertipe_berisiko_dipaksa_jadi_unduhan_bukan_dibuka_di_browser(): void
    {
        Storage::disk('local')->put('ga/bukti-kondisi/evil.html', '<script>alert(1)</script>');
        $req = $this->makeGa('disetujui', ['lampiran_bukti_kondisi' => 'ga/bukti-kondisi/evil.html']);

        $response = $this->actingAs($this->pemohon)->get(route('ga.lampiran', [$req, 'bukti']));
        $response->assertOk();
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_halaman_detail_memakai_link_privat_bukan_url_storage_publik(): void
    {
        $this->withoutVite();
        $quo = $this->makeQuotation($this->request);

        $this->actingAs($this->ga)->get(route('ga.show', $this->request))
            ->assertOk()
            ->assertSee(route('ga.lampiran', [$this->request, 'bukti']), false)
            ->assertSee(route('ga.quotation', [$this->request, $quo]), false)
            ->assertDontSee('/storage/ga/', false);
    }
}
