<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApprovalController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['user', 'room'])
            ->where('status', 'pending')
            ->orderBy('tanggal')->orderBy('jam_mulai')
            ->paginate(10);

        // Tandai pengajuan yang sudah bentrok dengan peminjaman disetujui.
        $bookings->getCollection()->transform(function (Booking $b) {
            $b->bentrok = Booking::bentrok($b->room_id, $b->tanggal->toDateString(), $b->jam_mulai, $b->jam_selesai, $b->id)->exists();

            return $b;
        });

        return view('approvals.index', compact('bookings'));
    }

    public function approve(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $berhasil = DB::transaction(function () use ($request, $booking) {
            $bentrok = Booking::bentrok(
                $booking->room_id,
                $booking->tanggal->toDateString(),
                $booking->jam_mulai,
                $booking->jam_selesai,
                $booking->id
            )->lockForUpdate()->exists();

            if ($bentrok) {
                return false;
            }

            $booking->update([
                'status' => 'disetujui',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
                'catatan_admin' => $request->input('catatan_admin'),
            ]);

            return true;
        });

        return $berhasil
            ? back()->with('success', 'Peminjaman disetujui.')
            : back()->with('error', 'Tidak bisa disetujui: bentrok dengan peminjaman lain yang sudah disetujui.');
    }

    public function reject(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'catatan_admin' => ['required', 'string', 'max:500'],
        ], ['catatan_admin.required' => 'Alasan penolakan wajib diisi.']);

        if ($booking->status !== 'pending') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $booking->update([
            'status' => 'ditolak',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'catatan_admin' => $data['catatan_admin'],
        ]);

        return back()->with('success', 'Pengajuan ditolak.');
    }
}
