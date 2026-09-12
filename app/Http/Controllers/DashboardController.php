<?php

namespace App\Http\Controllers;

use App\Models\ErfRequest;
use App\Models\GaRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        $erfQuery = ErfRequest::query();
        $gaQuery = GaRequest::query();

        // Selain request milik sendiri, atasan tetap bisa memantau progress request
        // yang pernah/sedang perlu approval-nya.
        if (! $user->isAdmin() && ! $user->isHr()) {
            $erfQuery->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('atasan_user_id', $user->id));
        }
        if (! $user->isAdmin() && ! $user->isGa()) {
            $gaQuery->where(fn ($q) => $q->where('user_id', $user->id)->orWhere('atasan_user_id', $user->id));
        }

        $erfStats = [
            'total' => (clone $erfQuery)->count(),
            'pending' => (clone $erfQuery)->whereIn('status', ['pending', 'in_review'])->count(),
            'on_progress' => (clone $erfQuery)->whereIn('status', ['approved', 'on_progress', 'interview_hr', 'interview_user', 'offering', 'kandidat_fix'])->count(),
            'completed' => (clone $erfQuery)->where('status', 'completed')->count(),
        ];

        $gaStats = [
            'total' => (clone $gaQuery)->count(),
            'pending' => (clone $gaQuery)->where('status', 'pending')->count(),
            'on_progress' => (clone $gaQuery)->whereIn('status', ['diproses_ga', 'vendor_diorder', 'proses_vendor', 'barang_diterima'])->count(),
            'completed' => (clone $gaQuery)->where('status', 'selesai')->count(),
        ];

        $recentErf = (clone $erfQuery)->with('pemohon')->latest()->limit(5)->get();
        $recentGa = (clone $gaQuery)->with('pemohon')->latest()->limit(5)->get();

        return view('dashboard', compact('erfStats', 'gaStats', 'recentErf', 'recentGa'));
    }
}
