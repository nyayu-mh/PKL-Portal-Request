<?php

namespace App\Livewire\Ga;

use App\Models\GaRequest;
use App\Models\GaStatusHistory;
use App\Models\User;
use App\Services\RequestIdGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class GaIndex extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $status = '';

    // ── Form Buat/Ajukan Ulang Request GA (pop-up) ──────────────────

    public bool $showForm = false;

    public ?int $editingId = null;

    public string $jenis_request = '';

    public string $judul = '';

    public string $deskripsi = '';

    public string $lokasi_kerja = '';

    public $lampiran_bukti_kondisi;

    public $lampiran_rekomendasi_vendor;

    public string $catatan = '';

    /** Path lampiran yang sudah ada sebelumnya (saat ajukan ulang), supaya tetap tampil sudah terupload. */
    public ?string $existing_lampiran_bukti_kondisi = null;

    public ?string $existing_lampiran_rekomendasi_vendor = null;

    /** Info tampilan saja (catatan revisi dari atasan), diisi saat edit(). */
    public ?string $revisiCatatan = null;

    public ?string $revisiAtasanName = null;

    public function mount(): void
    {
        // Datang dari tombol "Edit & Ajukan Ulang" di halaman detail request GA.
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

    protected function rules(): array
    {
        $rules = [
            'jenis_request' => ['required', 'in:maintenance,pengadaan_barang,office_acquisition,renovasi_kantor'],
            'judul' => ['required', 'string', 'max:150'],
            'deskripsi' => ['required', 'string'],
            'lokasi_kerja' => ['required', 'in:head_office,crm_office,pdn_office,gudang_palembang,gudang_jakarta,gudang_pekalongan'],
            'catatan' => ['nullable', 'string'],
            'lampiran_rekomendasi_vendor' => ['nullable', 'file', 'max:5120'],
        ];

        $sudahAdaBukti = (bool) $this->existing_lampiran_bukti_kondisi;

        $rules['lampiran_bukti_kondisi'] = ($this->jenis_request === 'maintenance' && ! $sudahAdaBukti)
            ? ['required', 'file', 'max:5120']
            : ['nullable', 'file', 'max:5120'];

        return $rules;
    }

    protected function guardCanOpenForm(): void
    {
        $user = Auth::user();
        abort_unless($user->canCreateGa(), 403, 'Request GA hanya dapat diajukan oleh Junior Leader, Leader, atau Manager.');

        if ($user->needsAtasanApproval() && ! $user->atasan_id) {
            abort(403, 'Atasan langsung Anda belum diatur oleh Admin. Hubungi Admin/IT sebelum mengajukan request GA.');
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

        $gaRequest = GaRequest::findOrFail($id);
        $user = Auth::user();
        abort_unless($gaRequest->user_id === $user->id, 403);
        abort_unless($gaRequest->approval_status === 'revisi', 403, 'Request GA ini tidak sedang dalam status perlu revisi.');

        $this->editingId = $gaRequest->id;
        $this->jenis_request = $gaRequest->jenis_request;
        $this->judul = $gaRequest->judul;
        $this->deskripsi = $gaRequest->deskripsi;
        $this->lokasi_kerja = $gaRequest->lokasi_kerja;
        $this->catatan = (string) $gaRequest->catatan;
        $this->existing_lampiran_bukti_kondisi = $gaRequest->lampiran_bukti_kondisi;
        $this->existing_lampiran_rekomendasi_vendor = $gaRequest->lampiran_rekomendasi_vendor;
        $this->revisiCatatan = $gaRequest->catatan_approval_atasan;
        $this->revisiAtasanName = $gaRequest->atasan->name ?? null;
        $this->showForm = true;
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId', 'jenis_request', 'judul', 'deskripsi', 'lokasi_kerja',
            'lampiran_bukti_kondisi', 'lampiran_rekomendasi_vendor', 'catatan',
            'existing_lampiran_bukti_kondisi', 'existing_lampiran_rekomendasi_vendor', 'showForm',
            'revisiCatatan', 'revisiAtasanName',
        ]);
        $this->resetErrorBag();
    }

    public function save()
    {
        $data = $this->validate();
        $user = Auth::user();
        $editing = $this->editingId ? GaRequest::findOrFail($this->editingId) : null;

        $picGa = User::where('role', 'ga')->where('is_active', true)->orderBy('id')->first();

        $paths = [
            'lampiran_bukti_kondisi' => $this->lampiran_bukti_kondisi?->store('ga/bukti-kondisi', 'public') ?? $this->existing_lampiran_bukti_kondisi,
            'lampiran_rekomendasi_vendor' => $this->lampiran_rekomendasi_vendor?->store('ga/rekomendasi-vendor', 'public') ?? $this->existing_lampiran_rekomendasi_vendor,
        ];

        $needsApproval = $user->needsAtasanApproval();

        $payload = [
            'jenis_request' => $data['jenis_request'],
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'],
            'lokasi_kerja' => $data['lokasi_kerja'],
            'lampiran_bukti_kondisi' => $paths['lampiran_bukti_kondisi'],
            'lampiran_rekomendasi_vendor' => $paths['lampiran_rekomendasi_vendor'],
            'catatan' => $data['catatan'] ?: null,
            'approval_status' => $needsApproval ? 'menunggu' : 'tidak_perlu',
            'atasan_user_id' => $needsApproval ? $user->atasan_id : null,
            'catatan_approval_atasan' => null,
            'approved_at' => null,
        ];

        $ga = DB::transaction(function () use ($payload, $user, $picGa, $editing) {
            if ($editing) {
                abort_unless($editing->user_id === $user->id, 403);
                $editing->update($payload);
                $ga = $editing->fresh();
                $note = $payload['approval_status'] === 'menunggu'
                    ? 'Request GA diajukan ulang setelah revisi, menunggu approval atasan.'
                    : 'Request GA diajukan ulang setelah revisi.';
            } else {
                $ga = GaRequest::create(array_merge($payload, [
                    'ga_id' => RequestIdGenerator::generate('ga_requests', 'ga_id', 'GA'),
                    'user_id' => $user->id,
                    'tanggal_request' => now(),
                    'pic_ga_user_id' => $picGa?->id,
                    'status' => 'pending',
                ]));
                $note = $payload['approval_status'] === 'menunggu'
                    ? 'Request GA diajukan, menunggu approval atasan.'
                    : 'Request GA diajukan.';
            }

            GaStatusHistory::create([
                'ga_request_id' => $ga->id,
                'status' => $ga->status,
                'changed_by_user_id' => Auth::id(),
                'catatan' => $note,
            ]);

            return $ga;
        });

        session()->flash('success', "Request GA berhasil diajukan dengan nomor {$ga->ga_id}.");

        return $this->redirect(route('ga.show', $ga), navigate: false);
    }

    public function render()
    {
        $user = Auth::user();

        $query = GaRequest::query()->with('pemohon')->latest();

        if (! $user->isAdmin() && ! $user->isGa()) {
            // Selain request milik sendiri, atasan juga tetap bisa memantau request
            // yang pernah/sedang perlu approval-nya (baik masih menunggu, sudah disetujui, atau ditolak).
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('atasan_user_id', $user->id);
            });
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('ga_id', 'like', "%{$this->search}%")
                    ->orWhere('judul', 'like', "%{$this->search}%");
            });
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        return view('livewire.ga.ga-index', [
            'gas' => $query->paginate(10),
            'canCreate' => $user->canCreateGa(),
            'statusOptions' => GaRequest::statusLabels(),
        ]);
    }
}
