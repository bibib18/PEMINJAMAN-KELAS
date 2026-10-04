<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->isAdmin();

        $query = Booking::with(['room', 'user'])
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id));

        switch ($request->input('status')) {
            case 'pending':
            case 'ditolak':
            case 'dibatalkan':
                $query->where('status', $request->status);
                break;
            case 'disetujui':
                $query->akan();
                break;
            case 'selesai':
                $query->sudahLewat();
                break;
        }

        if ($request->filled('q')) {
            $term = '%'.$request->q.'%';
            $query->where(function ($w) use ($term, $isAdmin) {
                $w->where('keperluan', 'like', $term)
                    ->orWhereHas('room', fn ($r) => $r->where('nama', 'like', $term)->orWhere('kode', 'like', $term))
                    ->when($isAdmin, fn ($ww) => $ww->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('nim_nip', 'like', $term)));
            });
        }

        switch ($request->input('urut', 'terbaru')) {
            case 'terlama':
                $query->orderBy('tanggal')->orderBy('jam_mulai');
                break;
            case 'ruang':
                $query->orderBy(Room::select('kode')->whereColumn('rooms.id', 'bookings.room_id'))
                    ->orderByDesc('tanggal')->orderByDesc('jam_mulai');
                break;
            default:
                $query->orderByDesc('tanggal')->orderByDesc('jam_mulai');
        }

        $bookings = $query->paginate(10)->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $rooms = Room::where('status', 'tersedia')->orderBy('gedung')->orderBy('kode')->get();

        return view('bookings.create', [
            'rooms' => $rooms,
            'selectedRoom' => $request->integer('room_id') ?: null,
            'tanggal' => $request->input('tanggal', today()->toDateString()),
        ]);
    }

    /** Daftar peminjaman pada satu ruang & tanggal, untuk panel ketersediaan di form. */
    public function availability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'room_id' => ['required', Rule::exists('rooms', 'id')],
            'tanggal' => ['required', 'date'],
        ]);

        $items = Booking::where('room_id', $data['room_id'])
            ->whereDate('tanggal', $data['tanggal'])
            ->whereIn('status', ['disetujui', 'pending'])
            ->orderBy('jam_mulai')
            ->get(['jam_mulai', 'jam_selesai', 'status'])
            ->map(fn ($b) => [
                'mulai' => substr($b->jam_mulai, 0, 5),
                'selesai' => substr($b->jam_selesai, 0, 5),
                'status' => $b->status,
            ]);

        return response()->json($items);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'room_id' => ['required', Rule::exists('rooms', 'id')],
            'tanggal' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i', 'after_or_equal:07:00'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai', 'before_or_equal:21:00'],
            'jumlah_peserta' => ['required', 'integer', 'min:1'],
            'keperluan' => ['required', 'string', 'max:500'],
        ], [
            'jam_mulai.after_or_equal' => 'Jam operasional mulai pukul 07:00.',
            'jam_selesai.before_or_equal' => 'Jam operasional berakhir pukul 21:00.',
            'jam_selesai.after' => 'Jam selesai harus setelah jam mulai.',
            'tanggal.after_or_equal' => 'Tanggal tidak boleh di masa lalu.',
        ]);

        $room = Room::findOrFail($data['room_id']);

        if (! $room->isTersedia()) {
            return back()->withInput()->withErrors(['room_id' => 'Ruang ini sedang dalam perawatan.']);
        }

        if ($data['jumlah_peserta'] > $room->kapasitas) {
            return back()->withInput()->withErrors([
                'jumlah_peserta' => "Kapasitas ruang {$room->kode} hanya {$room->kapasitas} orang.",
            ]);
        }

        $mulai = $data['jam_mulai'].':00';
        $selesai = $data['jam_selesai'].':00';

        if (Booking::bentrok($room->id, $data['tanggal'], $mulai, $selesai)->exists()) {
            return back()->withInput()->withErrors([
                'jam_mulai' => 'Jadwal bentrok dengan peminjaman lain yang sudah disetujui. Pilih jam atau ruang lain.',
            ]);
        }

        $booking = $request->user()->bookings()->create([
            'room_id' => $room->id,
            'tanggal' => $data['tanggal'],
            'jam_mulai' => $mulai,
            'jam_selesai' => $selesai,
            'jumlah_peserta' => $data['jumlah_peserta'],
            'keperluan' => $data['keperluan'],
            'status' => 'pending',
        ]);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Pengajuan terkirim. Admin akan memprosesnya; statusnya bisa dipantau di halaman ini.');
    }

    public function show(Request $request, Booking $booking)
    {
        $this->authorizeAccess($request, $booking);
        $booking->load(['room', 'user', 'approver']);

        return view('bookings.show', compact('booking'));
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $this->authorizeAccess($request, $booking);

        if (! $booking->bisaDibatalkan()) {
            return back()->with('error', 'Peminjaman ini tidak bisa dibatalkan.');
        }

        $booking->update(['status' => 'dibatalkan']);

        return redirect()->route('bookings.index')->with('success', 'Peminjaman dibatalkan.');
    }

    private function authorizeAccess(Request $request, Booking $booking): void
    {
        abort_unless(
            $request->user()->isAdmin() || $booking->user_id === $request->user()->id,
            403
        );
    }
}
