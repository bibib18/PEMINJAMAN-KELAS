@extends('layouts.app')

@section('title', 'Ruang Kelas')

@section('content')
    @php $isAdmin = auth()->user()->isAdmin(); @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">Ruang kelas</h1>
            <p class="page-sub">Kapasitas dan fasilitas tiap ruang. Ruang berstatus perawatan tidak bisa dipinjam.</p>
        </div>
        @if ($isAdmin)
            <a href="{{ route('rooms.create') }}" class="btn btn-primary"><x-icon name="plus" :size="16" /> Tambah ruang</a>
        @endif
    </div>

    <form method="GET" action="{{ route('rooms.index') }}" class="mt-6 flex flex-wrap gap-3">
        <div class="relative min-w-52 flex-1">
            <x-icon name="search" :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted" />
            <input name="q" value="{{ request('q') }}" placeholder="Cari kode, nama, atau gedung" class="input !pl-9" aria-label="Cari ruang">
        </div>
        <select name="gedung" class="input !w-auto" aria-label="Filter gedung" onchange="this.form.submit()">
            <option value="">Semua gedung</option>
            @foreach ($gedungList as $g)
                <option value="{{ $g }}" @selected(request('gedung') === $g)>{{ $g }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-secondary">Terapkan</button>
        @if (request()->hasAny(['q', 'gedung']))
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>

    <div class="panel table-wrap mt-4">
        <table class="table table-stack">
            <thead>
                <tr><th>Kode</th><th>Ruang</th><th class="num">Kapasitas</th><th>Fasilitas</th><th>Status</th><th><span class="sr-only">Aksi</span></th></tr>
            </thead>
            <tbody>
                @forelse ($rooms as $room)
                    <tr>
                        <td data-label="Kode"><span class="mono font-semibold">{{ $room->kode }}</span></td>
                        <td data-label="Ruang">
                            <div class="font-semibold">{{ $room->nama }}</div>
                            <div class="text-xs text-muted">{{ $room->gedung }}, lantai {{ $room->lantai }}</div>
                        </td>
                        <td data-label="Kapasitas" class="num mono">{{ $room->kapasitas }}</td>
                        <td data-label="Fasilitas" class="max-w-56 text-muted">{{ implode(', ', $room->fasilitas_list) ?: '—' }}</td>
                        <td data-label="Status"><x-status-badge :status="$room->status" /></td>
                        <td class="text-right">
                            <div class="flex flex-wrap justify-end gap-2">
                                @if ($room->isTersedia())
                                    <a href="{{ route('bookings.create', ['room_id' => $room->id]) }}" class="btn btn-primary btn-sm">Pinjam</a>
                                @endif
                                @if ($isAdmin)
                                    <a href="{{ route('rooms.edit', $room) }}" class="btn btn-secondary btn-sm" aria-label="Ubah {{ $room->kode }}"><x-icon name="pencil" :size="14" /> Ubah</a>
                                    <form method="POST" action="{{ route('rooms.destroy', $room) }}"
                                          data-confirm="Hapus ruang {{ $room->kode }} ({{ $room->nama }})? Semua riwayat peminjaman ruang ini ikut terhapus dan tidak bisa dikembalikan."
                                          data-confirm-label="Hapus ruang" data-confirm-tone="danger">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-sm" aria-label="Hapus {{ $room->kode }}"><x-icon name="trash" :size="14" /></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-10 text-center text-muted">Tidak ada ruang yang cocok dengan pencarian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $rooms->links() }}</div>
@endsection
