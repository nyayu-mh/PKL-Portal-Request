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

        // Aturan akses (pembuat, atasan langsung, atau Tim HR/GA) ada di scope visibleTo() model.
        $erfQuery = ErfRequest::query()->visibleTo($user);
        $gaQuery = GaRequest::query()->visibleTo($user);

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

        // Tim HR tidak melihat modul GA, Tim GA tidak melihat modul ERF (Super Admin melihat keduanya).
        $showErf = $user->canAccessErf();
        $showGa = $user->canAccessGa();

        return view('dashboard', compact('erfStats', 'gaStats', 'recentErf', 'recentGa', 'showErf', 'showGa'));
    }
}
