<?php

namespace App\Livewire\Approval;

use App\Models\ErfRequest;
use App\Models\GaRequest;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ApprovalIndex extends Component
{
    public function render()
    {
        $userId = Auth::id();
        $user = Auth::user();

        $erfs = ErfRequest::query()
            ->visibleTo($user)
            ->with('pemohon', 'jabatanDibutuhkan')
            ->where('atasan_user_id', $userId)
            ->where('approval_status', 'menunggu')
            ->latest()
            ->get();

        $gas = GaRequest::query()
            ->visibleTo($user)
            ->with('pemohon')
            ->where('atasan_user_id', $userId)
            ->where('approval_status', 'menunggu')
            ->latest()
            ->get();

        return view('livewire.approval.approval-index', [
            'erfs' => $erfs,
            'gas' => $gas,
        ]);
    }
}
