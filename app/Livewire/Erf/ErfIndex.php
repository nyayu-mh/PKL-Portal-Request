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
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ErfIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    // ── Form Buat/Ajukan Ulang ERF (pop-up) ─────────────────────────

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $jenis_erf = '';

    public ?int $jumlah_karyawan = 1;

    public ?int $jabatan_dibutuhkan_id = null;

    public string $uraian_tugas = '';

    public string $kualifikasi_kandidat = '';

    public string $catatan = '';

    public ?int $karyawan_diganti_id = null;

    public string $alasan_penggantian = '';

    /** Info tampilan saja (catatan revisi dari atasan), diisi saat edit(). */
    public ?string $revisiCatatan = null;

    public ?string $revisiAtasanName = null;

    public function mount(): void
    {
        // Datang dari tombol "Edit & Ajukan Ulang" di halaman detail ERF.
        if ($id = request()->integer('edit')) {
            $this->edit($id);
        }
    }

    public function updating($property): void
    {
        if (in_array($property, ['search', 'status'])) {
            $this->resetPage();
        }
    }

    public function updatedJenisErf(): void
    {
        $this->karyawan_diganti_id = null;
        $this->alasan_penggantian = '';
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

    protected function guardCanOpenForm(): void
    {
        $user = Auth::user();
        abort_unless($user->canCreateErf(), 403, 'ERF hanya dapat diajukan oleh Leader atau Manager.');

        if ($user->needsAtasanApproval() && ! $user->atasan_id) {
            abort(403, 'Atasan langsung Anda belum diatur oleh Admin. Hubungi Admin/IT sebelum mengajukan ERF.');
        }
    }

    public function create(): void
    {
        $this->guardCanOpenForm();
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $this->guardCanOpenForm();

        $erfRequest = ErfRequest::findOrFail($id);
        $user = Auth::user();
        abort_unless($erfRequest->user_id === $user->id, 403);
        abort_unless($erfRequest->approval_status === 'revisi', 403, 'ERF ini tidak sedang dalam status perlu revisi.');

        $this->editingId = $erfRequest->id;
        $this->jenis_erf = $erfRequest->jenis_erf;
        $this->jumlah_karyawan = $erfRequest->jumlah_karyawan;
        $this->jabatan_dibutuhkan_id = $erfRequest->jabatan_dibutuhkan_id;
        $this->uraian_tugas = $erfRequest->uraian_tugas;
        $this->kualifikasi_kandidat = $erfRequest->kualifikasi_kandidat;
        $this->catatan = (string) $erfRequest->catatan;
        $this->karyawan_diganti_id = $erfRequest->karyawan_diganti_id;
        $this->alasan_penggantian = (string) $erfRequest->alasan_penggantian;
        $this->revisiCatatan = $erfRequest->catatan_approval_atasan;
        $this->revisiAtasanName = $erfRequest->atasan->name ?? null;
        $this->showForm = true;
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'jenis_erf', 'jabatan_dibutuhkan_id', 'uraian_tugas', 'kualifikasi_kandidat',
            'catatan', 'karyawan_diganti_id', 'alasan_penggantian', 'showForm',
            'revisiCatatan', 'revisiAtasanName',
        ]);
        $this->jumlah_karyawan = 1;
        $this->resetErrorBag();
    }

    public function save()
    {
        $data = $this->validate();
        $user = Auth::user();

        $jabatan = MasterJabatanTtf::findOrFail($this->jabatan_dibutuhkan_id);
        $editing = $this->editingId ? ErfRequest::findOrFail($this->editingId) : null;

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

        $erf = DB::transaction(function () use ($payload, $user, $tanggalRequest, $editing) {
            if ($editing) {
                abort_unless($editing->user_id === $user->id, 403);
                $editing->update($payload);
                $erf = $editing->fresh();
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
        $user = Auth::user();

        // Aturan akses (pembuat, atasan langsung, atau Tim HR) ada di scope visibleTo() model.
        $query = ErfRequest::query()->visibleTo($user)->with(['pemohon', 'jabatanDibutuhkan'])->latest();

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('erf_id', 'like', "%{$this->search}%")
                    ->orWhereHas('pemohon', fn ($q2) => $q2->where('name', 'like', "%{$this->search}%"));
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        return view('livewire.erf.erf-index', [
            'erfs' => $query->paginate(10),
            'canCreate' => $user->canCreateErf(),
            'statusOptions' => ErfRequest::statusLabels(),
            'jabatanOptions' => MasterJabatanTtf::where('is_active', true)->orderBy('nama_jabatan')->get(),
            'karyawanOptions' => MasterKaryawan::where('status_aktif', true)->orderBy('nama')->get(),
        ]);
    }
}
