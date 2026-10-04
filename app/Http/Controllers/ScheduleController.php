<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['tanggal' => ['nullable', 'date']]);

        $tanggal = Carbon::parse($request->input('tanggal', today()))->startOfDay();

        $rooms = Room::orderBy('gedung')->orderBy('kode')->get();

        $byRoom = Booking::with('user')
            ->where('status', 'disetujui')
            ->whereDate('tanggal', $tanggal->toDateString())
            ->orderBy('jam_mulai')
            ->get()
            ->groupBy('room_id');

        return view('schedule.index', compact('tanggal', 'rooms', 'byRoom'));
    }
}
