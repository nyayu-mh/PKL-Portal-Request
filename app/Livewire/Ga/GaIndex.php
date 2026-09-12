<?php

namespace App\Livewire\Ga;

use App\Models\GaRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class GaIndex extends Component
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
