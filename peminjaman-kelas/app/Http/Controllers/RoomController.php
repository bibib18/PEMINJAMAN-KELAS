<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        $rooms = Room::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->q.'%';
                $q->where(fn ($qq) => $qq->where('nama', 'like', $term)
                    ->orWhere('kode', 'like', $term)
                    ->orWhere('gedung', 'like', $term));
            })
            ->when($request->filled('gedung'), fn ($q) => $q->where('gedung', $request->gedung))
            ->orderBy('gedung')->orderBy('kode')
            ->paginate(12)->withQueryString();

        $gedungList = Room::query()->distinct()->orderBy('gedung')->pluck('gedung');

        return view('rooms.index', compact('rooms', 'gedungList'));
    }

    public function create()
    {
        return view('rooms.create', ['room' => new Room(['status' => 'tersedia', 'lantai' => 1])]);
    }

    public function store(Request $request): RedirectResponse
    {
        Room::create($this->validated($request));

        return redirect()->route('rooms.index')->with('success', 'Ruang kelas ditambahkan.');
    }

    public function edit(Room $room)
    {
        return view('rooms.edit', compact('room'));
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $room->update($this->validated($request, $room));

        return redirect()->route('rooms.index')->with('success', 'Ruang kelas diperbarui.');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Ruang kelas dihapus.');
    }

    private function validated(Request $request, ?Room $room = null): array
    {
        return $request->validate([
            'kode' => ['required', 'string', 'max:20', Rule::unique('rooms', 'kode')->ignore($room?->id)],
            'nama' => ['required', 'string', 'max:100'],
            'gedung' => ['required', 'string', 'max:100'],
            'lantai' => ['required', 'integer', 'min:1', 'max:20'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:1000'],
            'fasilitas' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(['tersedia', 'perawatan'])],
        ]);
    }
}
