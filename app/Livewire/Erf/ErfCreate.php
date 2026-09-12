<?php

namespace App\Livewire\Erf;

use App\Models\ErfRequest;
use App\Models\ErfStatusHistory;
use App\Models\MasterJabatanTtf;
use App\Models\MasterKaryawan;
use App\Services\RequestIdGenerator;
use App\Services\WorkingDayCalculator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ErfCreate extends Component
{
    public ?ErfRequest $erfRequest = null;

    public string $jenis_erf = '';

    public ?int $jumlah_karyawan = 1;

    public ?int $jabatan_dibutuhkan_id = null;

    public string $uraian_tugas = '';

    public string $kualifikasi_kandidat = '';

    public string $catatan = '';

    public ?int $karyawan_diganti_id = null;

    public string $alasan_penggantian = '';

    public function mount(?ErfRequest $erfRequest = null): void
    {
        $user = Auth::user();
        abort_unless($user->canCreateErf(), 403, 'ERF hanya dapat diajukan oleh Leader atau Manager.');

        if ($user->needsAtasanApproval() && ! $user->atasan_id) {
            abort(403, 'Atasan langsung Anda belum diatur oleh Admin. Hubungi Admin/IT sebelum mengajukan ERF.');
        }

        if ($erfRequest && $erfRequest->exists) {
            abort_unless($erfRequest->user_id === $user->id, 403);
            abort_unless($erfRequest->approval_status === 'ditolak', 403, 'ERF ini tidak sedang dalam status perlu revisi.');

            $this->erfRequest = $erfRequest;
            $this->jenis_erf = $erfRequest->jenis_erf;
            $this->jumlah_karyawan = $erfRequest->jumlah_karyawan;
            $this->jabatan_dibutuhkan_id = $erfRequest->jabatan_dibutuhkan_id;
            $this->uraian_tugas = $erfRequest->uraian_tugas;
            $this->kualifikasi_kandidat = $erfRequest->kualifikasi_kandidat;
            $this->catatan = (string) $erfRequest->catatan;
            $this->karyawan_diganti_id = $erfRequest->karyawan_diganti_id;
            $this->alasan_penggantian = (string) $erfRequest->alasan_penggantian;
        }
    }

    protected function rules(): array
    {
        $rules = [
            'jenis_erf' => ['required', 'in:karyawan_baru,pengganti_karyawan_lama'],
            'jumlah_karyawan' => ['required', 'integer', 'min:1'],
            'jabatan_dibutuhkan_id' => ['required', 'exists:master_jabatan_ttf,id'],
            'uraian_tugas' => ['required', 'string'],
            'kualifikasi_kandidat' => ['required', 'string'],
            'catatan' => ['nullable', 'string'],
        ];

        if ($this->jenis_erf === 'pengganti_karyawan_lama') {
            $rules['karyawan_diganti_id'] = ['required', 'exists:master_karyawan,id'];
            $rules['alasan_penggantian'] = ['required', 'string'];
        }

        return $rules;
    }

    public function updatedJenisErf(): void
    {
        $this->karyawan_diganti_id = null;
        $this->alasan_penggantian = '';
    }

    public function save()
    {
        $data = $this->validate();
        $user = Auth::user();

        $jabatan = MasterJabatanTtf::findOrFail($this->jabatan_dibutuhkan_id);

        $calculator = new WorkingDayCalculator;
        $tanggalRequest = now();
        $tanggalEfektif = $calculator->effectiveBaseDate($tanggalRequest);
        $tanggalMencariKandidat = $calculator->nextWorkingDay($tanggalEfektif);
        $estimasiFulfillment = $calculator->addWorkingDays($tanggalMencariKandidat, $jabatan->target_hari_kerja);

        $needsApproval = $user->needsAtasanApproval();

        $payload = [
            'jenis_erf' => $data['jenis_erf'],
            'jumlah_karyawan' => $data['jumlah_karyawan'],
            'jabatan_dibutuhkan_id' => $jabatan->id,
            'uraian_tugas' => $data['uraian_tugas'],
            'kualifikasi_kandidat' => $data['kualifikasi_kandidat'],
            'catatan' => $data['catatan'] ?: null,
            'karyawan_diganti_id' => $data['karyawan_diganti_id'] ?? null,
            'alasan_penggantian' => $data['alasan_penggantian'] ?? null,
            'pic_hr_user_id' => $jabatan->pic_hr_user_id,
            'ttf_hari' => $jabatan->target_hari_kerja,
            'tanggal_mencari_kandidat' => $tanggalMencariKandidat,
            'estimasi_tanggal_fulfillment' => $estimasiFulfillment,
            'approval_status' => $needsApproval ? 'menunggu' : 'tidak_perlu',
            'atasan_user_id' => $needsApproval ? $user->atasan_id : null,
            'catatan_approval_atasan' => null,
            'approved_at' => null,
        ];

        $erf = DB::transaction(function () use ($payload, $user, $tanggalRequest) {
            if ($this->erfRequest) {
                $this->erfRequest->update($payload);
                $erf = $this->erfRequest->fresh();
                $note = $payload['approval_status'] === 'menunggu'
                    ? 'ERF diajukan ulang setelah revisi, menunggu approval atasan.'
                    : 'ERF diajukan ulang setelah revisi.';
            } else {
                $erf = ErfRequest::create(array_merge($payload, [
                    'erf_id' => RequestIdGenerator::generate('erf_requests', 'erf_id', 'ERF'),
                    'user_id' => $user->id,
                    'tanggal_request' => $tanggalRequest,
                    'status' => 'pending',
                ]));
                $note = $payload['approval_status'] === 'menunggu'
                    ? 'ERF diajukan, menunggu approval atasan.'
                    : 'ERF diajukan.';
            }

            ErfStatusHistory::create([
                'erf_request_id' => $erf->id,
                'status' => $erf->status,
                'changed_by_user_id' => Auth::id(),
                'catatan' => $note,
            ]);

            return $erf;
        });

        session()->flash('success', "ERF berhasil diajukan dengan nomor {$erf->erf_id}.");

        return $this->redirect(route('erf.show', $erf), navigate: false);
    }

    public function render()
    {
        return view('livewire.erf.erf-create', [
            'jabatanOptions' => MasterJabatanTtf::where('is_active', true)->orderBy('nama_jabatan')->get(),
            'karyawanOptions' => MasterKaryawan::where('status_aktif', true)->orderBy('nama')->get(),
        ]);
    }
}
