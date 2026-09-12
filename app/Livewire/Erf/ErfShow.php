<?php

namespace App\Livewire\Erf;

use App\Models\ErfRequest;
use App\Models\ErfStatusHistory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ErfShow extends Component
{
    public ErfRequest $erfRequest;

    public string $status = '';

    public ?string $tanggal_selesai = null;

    public ?string $tanggal_karyawan_masuk = null;

    public string $catatan_update = '';

    public string $catatan_approval = '';

    public function mount(ErfRequest $erfRequest): void
    {
        $user = Auth::user();
        abort_unless(
            $user->isAdmin() || $user->isHr() || $erfRequest->user_id === $user->id || $erfRequest->atasan_user_id === $user->id,
            403
        );

        $this->erfRequest = $erfRequest;
        $this->status = $erfRequest->status;
        $this->tanggal_selesai = optional($erfRequest->tanggal_selesai)->toDateString();
        $this->tanggal_karyawan_masuk = optional($erfRequest->tanggal_karyawan_masuk)->toDateString();
    }

    public function canManage(): bool
    {
        return Auth::user()->isAdmin() || Auth::user()->isHr();
    }

    public function isApprover(): bool
    {
        return $this->erfRequest->approval_status === 'menunggu' && $this->erfRequest->atasan_user_id === Auth::id();
    }

    public function isOwner(): bool
    {
        return $this->erfRequest->user_id === Auth::id();
    }

    public function approve(): void
    {
        abort_unless($this->isApprover(), 403);

        $this->erfRequest->update([
            'approval_status' => 'disetujui',
            'catatan_approval_atasan' => $this->catatan_approval ?: null,
            'approved_at' => now(),
        ]);

        ErfStatusHistory::create([
            'erf_request_id' => $this->erfRequest->id,
            'status' => $this->erfRequest->status,
            'changed_by_user_id' => Auth::id(),
            'catatan' => 'Disetujui atasan ('.Auth::user()->name.').'.($this->catatan_approval ? ' Catatan: '.$this->catatan_approval : ''),
        ]);

        $this->catatan_approval = '';
        $this->erfRequest->refresh();

        session()->flash('success', 'ERF telah disetujui dan diteruskan ke HR.');
    }

    public function reject(): void
    {
        abort_unless($this->isApprover(), 403);

        $this->validate([
            'catatan_approval' => ['required', 'string'],
        ], [
            'catatan_approval.required' => 'Mohon isi alasan/masukan revisi sebelum menolak.',
        ]);

        $this->erfRequest->update([
            'approval_status' => 'ditolak',
            'catatan_approval_atasan' => $this->catatan_approval,
            'approved_at' => null,
        ]);

        ErfStatusHistory::create([
            'erf_request_id' => $this->erfRequest->id,
            'status' => $this->erfRequest->status,
            'changed_by_user_id' => Auth::id(),
            'catatan' => 'Dikembalikan untuk revisi oleh atasan ('.Auth::user()->name.'). Alasan: '.$this->catatan_approval,
        ]);

        $this->catatan_approval = '';
        $this->erfRequest->refresh();

        session()->flash('success', 'ERF dikembalikan ke pemohon untuk direvisi.');
    }

    public function cancel(): void
    {
        abort_unless($this->isOwner(), 403);
        abort_unless(in_array($this->erfRequest->approval_status, ['menunggu', 'ditolak']), 403, 'ERF yang sudah disetujui/diproses tidak bisa dihapus sendiri. Hubungi HR/Admin.');

        $erfId = $this->erfRequest->erf_id;
        $this->erfRequest->delete();

        session()->flash('success', "ERF {$erfId} berhasil dihapus.");

        $this->redirect(route('erf.index'), navigate: false);
    }

    public function updateStatus(): void
    {
        abort_unless($this->canManage(), 403);
        abort_unless($this->erfRequest->isApprovedForProcessing(), 403, 'ERF ini belum disetujui atasan.');

        $this->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(ErfRequest::statusLabels()))],
            'tanggal_selesai' => ['nullable', 'date'],
            'tanggal_karyawan_masuk' => ['nullable', 'date'],
            'catatan_update' => ['nullable', 'string'],
        ]);

        $this->erfRequest->update([
            'status' => $this->status,
            'tanggal_selesai' => $this->tanggal_selesai,
            'tanggal_karyawan_masuk' => $this->tanggal_karyawan_masuk,
        ]);

        ErfStatusHistory::create([
            'erf_request_id' => $this->erfRequest->id,
            'status' => $this->status,
            'changed_by_user_id' => Auth::id(),
            'catatan' => $this->catatan_update ?: null,
        ]);

        $this->catatan_update = '';
        $this->erfRequest->refresh();

        session()->flash('success', 'Status ERF berhasil diperbarui.');
    }

    public function render()
    {
        return view('livewire.erf.erf-show', [
            'canManage' => $this->canManage(),
            'isApprover' => $this->isApprover(),
            'isOwner' => $this->isOwner(),
            'statusOptions' => ErfRequest::statusLabels(),
            'histories' => $this->erfRequest->histories()->with('changedBy')->get(),
        ]);
    }
}
