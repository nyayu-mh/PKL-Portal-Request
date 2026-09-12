<?php

namespace App\Livewire\Erf;

use App\Models\ErfRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class ErfIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function updating($property): void
    {
        if (in_array($property, ['search', 'status'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $user = Auth::user();

        $query = ErfRequest::query()->with(['pemohon', 'jabatanDibutuhkan'])->latest();

        if (! $user->isAdmin() && ! $user->isHr()) {
            // Selain request milik sendiri, atasan juga tetap bisa memantau request
            // yang pernah/sedang perlu approval-nya (baik masih menunggu, sudah disetujui, atau ditolak).
            $query->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('atasan_user_id', $user->id);
            });
        }

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
        ]);
    }
}
