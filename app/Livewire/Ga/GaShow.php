<?php

namespace App\Livewire\Ga;

use App\Models\GaRequest;
use App\Models\GaStatusHistory;
use App\Models\GaVendorQuotation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.app')]
class GaShow extends Component
{
    use WithFileUploads;

    public GaRequest $gaRequest;

    // Form proses GA
    public $butuh_vendor = null;

    public string $nama_vendor = '';

    public string $catatan_rab = '';

    public ?string $tanggal_mulai_proses = null;

    public string $status = '';

    public ?string $tanggal_selesai = null;

    public ?string $realisasi_biaya = null;

    public string $catatan_internal = '';

    public string $catatan_update = '';

    public string $catatan_approval = '';

    // Tambah quotation vendor
    public string $new_vendor_nama = '';

    public $new_vendor_file;

    public ?string $new_vendor_harga = null;

    public function mount(GaRequest $gaRequest): void
    {
        $user = Auth::user();
        abort_unless(
            $user->isAdmin() || $user->isGa() || $gaRequest->user_id === $user->id || $gaRequest->atasan_user_id === $user->id,
            403
        );

        $this->gaRequest = $gaRequest;
        $this->butuh_vendor = $gaRequest->butuh_vendor;
        $this->nama_vendor = (string) $gaRequest->nama_vendor;
        $this->catatan_rab = (string) $gaRequest->catatan_rab;
        $this->tanggal_mulai_proses = optional($gaRequest->tanggal_mulai_proses)->toDateString();
        $this->status = $gaRequest->status;
        $this->tanggal_selesai = optional($gaRequest->tanggal_selesai)->toDateString();
        $this->realisasi_biaya = $gaRequest->realisasi_biaya;
        $this->catatan_internal = (string) $gaRequest->catatan_internal;
    }

    public function canManage(): bool
    {
        return Auth::user()->isAdmin() || Auth::user()->isGa();
    }

    public function isApprover(): bool
    {
        return $this->gaRequest->approval_status === 'menunggu' && $this->gaRequest->atasan_user_id === Auth::id();
    }

    public function isOwner(): bool
    {
        return $this->gaRequest->user_id === Auth::id();
    }

    public function approve(): void
    {
        abort_unless($this->isApprover(), 403);

        $this->gaRequest->update([
            'approval_status' => 'disetujui',
            'catatan_approval_atasan' => $this->catatan_approval ?: null,
            'approved_at' => now(),
        ]);

        GaStatusHistory::create([
            'ga_request_id' => $this->gaRequest->id,
            'status' => $this->gaRequest->status,
            'changed_by_user_id' => Auth::id(),
            'catatan' => 'Disetujui atasan ('.Auth::user()->name.').'.($this->catatan_approval ? ' Catatan: '.$this->catatan_approval : ''),
        ]);

        $this->catatan_approval = '';
        $this->gaRequest->refresh();

        session()->flash('success', 'Request GA telah disetujui dan diteruskan ke tim GA.');
    }

    public function requestRevision(): void
    {
        abort_unless($this->isApprover(), 403);

        $this->validate([
            'catatan_approval' => ['required', 'string'],
        ], [
            'catatan_approval.required' => 'Mohon isi alasan/masukan revisi sebelum meminta revisi.',
        ]);

        $this->gaRequest->update([
            'approval_status' => 'revisi',
            'catatan_approval_atasan' => $this->catatan_approval,
            'approved_at' => null,
        ]);

        GaStatusHistory::create([
            'ga_request_id' => $this->gaRequest->id,
            'status' => $this->gaRequest->status,
            'changed_by_user_id' => Auth::id(),
            'catatan' => 'Dikembalikan untuk revisi oleh atasan ('.Auth::user()->name.'). Alasan: '.$this->catatan_approval,
        ]);

        $this->catatan_approval = '';
        $this->gaRequest->refresh();

        session()->flash('success', 'Request GA dikembalikan ke pemohon untuk direvisi.');
    }

    public function rejectFinal(): void
    {
        abort_unless($this->isApprover(), 403);

        $this->validate([
            'catatan_approval' => ['required', 'string'],
        ], [
            'catatan_approval.required' => 'Mohon isi alasan penolakan sebelum menolak request GA ini.',
        ]);

        $this->gaRequest->update([
            'approval_status' => 'ditolak',
            'catatan_approval_atasan' => $this->catatan_approval,
            'approved_at' => null,
            'status' => 'ditolak',
        ]);

        GaStatusHistory::create([
            'ga_request_id' => $this->gaRequest->id,
            'status' => 'ditolak',
            'changed_by_user_id' => Auth::id(),
            'catatan' => 'Ditolak final oleh atasan ('.Auth::user()->name.'). Alasan: '.$this->catatan_approval,
        ]);

        $this->catatan_approval = '';
        $this->status = 'ditolak';
        $this->gaRequest->refresh();

        session()->flash('success', 'Request GA ditolak. Request ini sudah ditutup (case closed).');
    }

    public function cancel(): void
    {
        abort_unless($this->isOwner(), 403);
        abort_unless(in_array($this->gaRequest->approval_status, ['menunggu', 'revisi']), 403, 'Request GA yang sudah disetujui/diproses/ditolak tidak bisa dihapus sendiri. Hubungi GA/Admin.');

        $gaId = $this->gaRequest->ga_id;
        $this->gaRequest->delete();

        session()->flash('success', "Request GA {$gaId} berhasil dihapus.");

        $this->redirect(route('ga.index'), navigate: false);
    }

    public function addQuotation(): void
    {
        abort_unless($this->canManage(), 403);

        $this->validate([
            'new_vendor_nama' => ['required', 'string', 'max:150'],
            'new_vendor_file' => ['required', 'file', 'max:5120'],
            'new_vendor_harga' => ['nullable', 'numeric', 'min:0'],
        ]);

        $path = $this->new_vendor_file->store('ga/quotations', 'public');

        GaVendorQuotation::create([
            'ga_request_id' => $this->gaRequest->id,
            'nama_vendor' => $this->new_vendor_nama,
            'file_path' => $path,
            'harga_penawaran' => $this->new_vendor_harga ?: null,
        ]);

        $this->reset(['new_vendor_nama', 'new_vendor_file', 'new_vendor_harga']);
        session()->flash('success', 'Quotation vendor berhasil ditambahkan.');
    }

    public function selectQuotation(int $quotationId): void
    {
        abort_unless($this->canManage(), 403);

        $this->gaRequest->quotations()->update(['is_terpilih' => false]);
        $quotation = GaVendorQuotation::findOrFail($quotationId);
        $quotation->update(['is_terpilih' => true]);

        $this->gaRequest->update(['nama_vendor' => $quotation->nama_vendor, 'butuh_vendor' => true]);
        $this->butuh_vendor = true;
        $this->nama_vendor = $quotation->nama_vendor;

        session()->flash('success', "Vendor {$quotation->nama_vendor} ditetapkan sebagai vendor terpilih.");
    }

    public function removeQuotation(int $quotationId): void
    {
        abort_unless($this->canManage(), 403);

        $quotation = GaVendorQuotation::findOrFail($quotationId);
        Storage::disk('public')->delete($quotation->file_path);
        $quotation->delete();
    }

    public function updateStatus()
    {
        abort_unless($this->canManage(), 403);
        abort_unless($this->gaRequest->isApprovedForProcessing(), 403, 'Request GA ini belum disetujui atasan.');

        $this->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(GaRequest::statusLabels()))],
            'tanggal_mulai_proses' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
            'realisasi_biaya' => ['nullable', 'numeric', 'min:0'],
            'nama_vendor' => ['nullable', 'string', 'max:150'],
            'catatan_rab' => ['nullable', 'string'],
            'catatan_internal' => ['nullable', 'string'],
            'catatan_update' => ['nullable', 'string'],
        ]);

        $this->gaRequest->update([
            'butuh_vendor' => $this->butuh_vendor,
            'nama_vendor' => $this->nama_vendor ?: null,
            'catatan_rab' => $this->catatan_rab ?: null,
            'tanggal_mulai_proses' => $this->tanggal_mulai_proses,
            'status' => $this->status,
            'tanggal_selesai' => $this->tanggal_selesai,
            'realisasi_biaya' => $this->realisasi_biaya ?: null,
            'catatan_internal' => $this->catatan_internal ?: null,
        ]);

        GaStatusHistory::create([
            'ga_request_id' => $this->gaRequest->id,
            'status' => $this->status,
            'changed_by_user_id' => Auth::id(),
            'catatan' => $this->catatan_update ?: null,
        ]);

        session()->flash('success', 'Data request GA berhasil diperbarui.');

        return $this->redirect(route('ga.index'), navigate: false);
    }

    public function render()
    {
        return view('livewire.ga.ga-show', [
            'canManage' => $this->canManage(),
            'isApprover' => $this->isApprover(),
            'isOwner' => $this->isOwner(),
            'statusOptions' => GaRequest::statusLabels(),
            'histories' => $this->gaRequest->histories()->with('changedBy')->get(),
            'quotations' => $this->gaRequest->quotations()->latest()->get(),
        ]);
    }
}
