@extends('layouts.app')

@section('title', 'Peminjaman')

@section('content')
    @php
        $isAdmin = auth()->user()->isAdmin();
        $statuses = [
            '' => 'Semua',
            'pending' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
        ];
        $current = (string) request('status', '');
        $keep = array_filter(['q' => request('q'), 'urut' => request('urut')]);
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="page-title">{{ $isAdmin ? 'Semua peminjaman' : 'Peminjaman saya' }}</h1>
            <p class="page-sub">{{ $isAdmin ? 'Seluruh pengajuan dari mahasiswa dan dosen.' : 'Riwayat dan status pengajuanmu.' }}</p>
        </div>
        <a href="{{ route('bookings.create') }}" class="btn btn-primary"><x-icon name="plus" :size="16" /> Ajukan peminjaman</a>
    </div>

    <nav class="tabs mt-6" aria-label="Filter status">
        @foreach ($statuses as $value => $label)
            <a href="{{ route('bookings.index', $keep + ($value !== '' ? ['status' => $value] : [])) }}"
               class="tab" @if ($current === (string) $value) aria-current="true" @endif>{{ $label }}</a>
        @endforeach
    </nav>

    <form method="GET" action="{{ route('bookings.index') }}" class="mt-4 flex flex-wrap gap-3">
        @if ($current !== '') <input type="hidden" name="status" value="{{ $current }}"> @endif
        <div class="relative min-w-52 flex-1">
            <x-icon name="search" :size="16" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted" />
            <input name="q" value="{{ request('q') }}" class="input !pl-9" aria-label="Cari peminjaman"
                   placeholder="{{ $isAdmin ? 'Cari ruang, peminjam, atau keperluan' : 'Cari ruang atau keperluan' }}">
        </div>
        <select name="urut" class="input !w-auto" aria-label="Urutkan" onchange="this.form.submit()">
            <option value="terbaru" @selected(request('urut', 'terbaru') === 'terbaru')>Tanggal terbaru</option>
            <option value="terlama" @selected(request('urut') === 'terlama')>Tanggal terlama</option>
            <option value="ruang" @selected(request('urut') === 'ruang')>Kode ruang (A–Z)</option>
        </select>
        <button type="submit" class="btn btn-secondary">Terapkan</button>
        @if (request()->hasAny(['q', 'urut', 'status']))
            <a href="{{ route('bookings.index') }}" class="btn btn-secondary">Reset</a>
        @endif
    </form>

    <div class="panel table-wrap mt-4">
        <table class="table table-stack">
            <thead>
                <tr>
                    <th>Tanggal &amp; jam</th>
                    <th>Ruang</th>
                    @if ($isAdmin)<th>Peminjam</th>@endif
                    <th>Keperluan</th>
                    <th>Status</th>
                    <th><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bookings as $b)
                    <tr>
                        <td data-label="Waktu" class="whitespace-nowrap">
                            <div class="font-semibold">{{ $b->tanggal->translatedFormat('d M Y') }}</div>
                            <div class="mono text-muted">{{ $b->jam }}</div>
                        </td>
                        <td data-label="Ruang">
                            <div><span class="mono font-semibold">{{ $b->room->kode }}</span></div>
                            <div class="text-xs text-muted">{{ $b->room->nama }}</div>
                        </td>
                        @if ($isAdmin)
                            <td data-label="Peminjam">
                                <div>{{ $b->user->name }}</div>
                                <div class="mono text-xs text-muted">{{ $b->user->nim_nip }}</div>
                            </td>
                        @endif
                        <td data-label="Keperluan" class="max-w-xs"><span class="line-clamp-2">{{ $b->keperluan }}</span></td>
                        <td data-label="Status"><x-status-badge :status="$b->status_tampil" /></td>
                        <td class="text-right"><a href="{{ route('bookings.show', $b) }}" class="link text-sm">Detail</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isAdmin ? 6 : 5 }}" class="py-12 text-center text-muted">
                            @if (request()->hasAny(['q', 'status']))
                                Tidak ada peminjaman yang cocok dengan filter ini.
                            @else
                                Belum ada peminjaman. <a href="{{ route('bookings.create') }}" class="link">Ajukan yang pertama</a>.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $bookings->links() }}</div>
@endsection
