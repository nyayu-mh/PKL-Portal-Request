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

#[Layout('components.layouts.app')]
class GaCreate extends Component
{
    use WithFileUploads;

    public ?GaRequest $gaRequest = null;

    public string $jenis_request = '';

    public string $judul = '';

    public string $deskripsi = '';

    public string $lokasi_kerja = '';

    public $lampiran_bukti_kondisi;

    public $lampiran_rekomendasi_vendor;

    public string $catatan = '';

    public function mount(?GaRequest $gaRequest = null): void
    {
        $user = Auth::user();
        abort_unless($user->canCreateGa(), 403, 'Request GA hanya dapat diajukan oleh Junior Leader, Leader, atau Manager.');

        if ($user->needsAtasanApproval() && ! $user->atasan_id) {
            abort(403, 'Atasan langsung Anda belum diatur oleh Admin. Hubungi Admin/IT sebelum mengajukan request GA.');
        }

        if ($gaRequest && $gaRequest->exists) {
            abort_unless($gaRequest->user_id === $user->id, 403);
            abort_unless($gaRequest->approval_status === 'ditolak', 403, 'Request GA ini tidak sedang dalam status perlu revisi.');

            $this->gaRequest = $gaRequest;
            $this->jenis_request = $gaRequest->jenis_request;
            $this->judul = $gaRequest->judul;
            $this->deskripsi = $gaRequest->deskripsi;
            $this->lokasi_kerja = $gaRequest->lokasi_kerja;
            $this->catatan = (string) $gaRequest->catatan;
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

        $sudahAdaBukti = $this->gaRequest?->lampiran_bukti_kondisi;

        $rules['lampiran_bukti_kondisi'] = ($this->jenis_request === 'maintenance' && ! $sudahAdaBukti)
            ? ['required', 'file', 'max:5120']
            : ['nullable', 'file', 'max:5120'];

        return $rules;
    }

    public function save()
    {
        $data = $this->validate();
        $user = Auth::user();

        $picGa = User::where('role', 'ga')->where('is_active', true)->orderBy('id')->first();

        $paths = [
            'lampiran_bukti_kondisi' => $this->lampiran_bukti_kondisi?->store('ga/bukti-kondisi', 'public') ?? $this->gaRequest?->lampiran_bukti_kondisi,
            'lampiran_rekomendasi_vendor' => $this->lampiran_rekomendasi_vendor?->store('ga/rekomendasi-vendor', 'public') ?? $this->gaRequest?->lampiran_rekomendasi_vendor,
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

        $ga = DB::transaction(function () use ($payload, $user, $picGa) {
            if ($this->gaRequest) {
                $this->gaRequest->update($payload);
                $ga = $this->gaRequest->fresh();
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
        return view('livewire.ga.ga-create');
    }
}
