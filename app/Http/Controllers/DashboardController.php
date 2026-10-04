<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $stats = [
                'pending' => Booking::where('status', 'pending')->count(),
                'hari_ini' => Booking::where('status', 'disetujui')->whereDate('tanggal', today())->count(),
                'bulan_ini' => Booking::whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)->count(),
                'ruang' => Room::count(),
            ];
            $antrean = Booking::with(['user', 'room'])
                ->where('status', 'pending')
                ->orderBy('tanggal')->orderBy('jam_mulai')
                ->limit(5)->get();
            $terbaru = collect();
        } else {
            $mine = fn () => $user->bookings();
            $stats = [
                'pending' => $mine()->where('status', 'pending')->count(),
                'disetujui' => $mine()->akan()->count(),
                'ditolak' => $mine()->where('status', 'ditolak')->count(),
                'total' => $mine()->count(),
            ];
            $antrean = collect();
            $terbaru = $user->bookings()->with('room')
                ->orderByDesc('created_at')->limit(5)->get();
        }

        $mendatang = Booking::with(['room', 'user'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->akan()
            ->orderBy('tanggal')->orderBy('jam_mulai')
            ->limit(5)->get();

        return view('dashboard', compact('stats', 'antrean', 'mendatang', 'terbaru'));
    }
}
